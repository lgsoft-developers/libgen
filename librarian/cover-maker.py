# coding: utf8
import sys
import os
import urllib2
import logging
import glob
import shutil
import optparse
import re
import time
import subprocess

import MySQLdb
import MySQLdb.cursors


USE_OS_SYSTEM = True

class LoggingReadCursor(MySQLdb.cursors.Cursor):

    def execute(self, sql, values=None):
        log.debug("Executing SQL: %s; args: %s", sql, values)
        MySQLdb.cursors.Cursor.execute(self, sql, values)

class LoggingWriteCursor(MySQLdb.cursors.Cursor):

    def execute(self, sql, values=None):
        global options
        if options.dry_run:
            log.debug("Would execute SQL: %s; args: %s", sql, values)
        else:
            log.debug("Executing SQL: %s; args: %s", sql, values)
            MySQLdb.cursors.Cursor.execute(self, sql, values)

def system(args):
    if USE_OS_SYSTEM:
        cmd = " ".join(args)
        status = os.system(cmd)
        if status != 0:
            raise subprocess.CalledProcessError(status, args)
    else:
        subprocess.check_call(args)

def cover_name_path(main_file_name, suffix, ext):
    global dest_root, options
    dest_cover_name = main_file_name + suffix + ext
    dest_cover_path = os.path.join(dest_root, dest_cover_name)
    exists = False
    if not options.force and os.path.exists(dest_cover_path):
        log.info("Cover %s already exists, using as is", dest_cover_path)
        exists = True

    return dest_cover_name, dest_cover_path, exists

def move_to_dest(from_name, to_name):
    if not os.path.isdir(os.path.dirname(to_name)):
        os.makedirs(os.path.dirname(to_name))
    shutil.move(from_name, to_name)

def download_cover(main_file_name, cover_url):
    global options
    cover_ext = os.path.splitext(cover_url)[1]
    if not cover_ext:
        log.warning("%s: cover url has no file extension, using 'img' placeholder", cover_url)
        cover_ext = '.img'

    # -d suffix means "downloaded"
    dest_cover_name, dest_cover_path, exists = cover_name_path(main_file_name, "-d", cover_ext)
    if exists:
        return dest_cover_name

    attempt = 0
    while True:
        try:
            try:
                net_fp = urllib2.urlopen(cover_url)
            except urllib2.HTTPError, e:
                log.error("Could not fetch %s, server response: %s", cover_url, e)
                return None

            cover_fp = open("cover.tmp", "wb")
            shutil.copyfileobj(net_fp, cover_fp)
            cover_fp.close()
            net_fp.close()
            break
        except IOError, e:
            if attempt >= options.retry:
                log.error("Could not download cover %s, skipping: %s", cover_url, e)
                return None
            attempt += 1
            log.warning("Could not download cover, will retry: %s", e)
            time.sleep(2)

    move_to_dest("cover.tmp", dest_cover_path)
    log.info("Downloaded cover to %s", dest_cover_path)
    return dest_cover_name

def render_cover(main_file_name, type):
    global lib_root, options
    src_name = os.path.join(lib_root, main_file_name)
    if not os.path.exists(src_name):
        log.info("Book file %s does not exist, skipping", src_name)
        return None

    # -g suffix means "generated"
    dest_cover_name, dest_cover_path, exists = cover_name_path(main_file_name, "-g", ".jpg")
    if exists:
        return dest_cover_name

    size = "%dx%d" % (options.cover_size, options.cover_size)
    try:
        if type == "pdf":
            # Different version of pdftoppm may produce different number of digits
            # for different files, so have to use glob.
            for f in glob.glob("tmpcover-*.ppm"): 
                os.remove(f)
            system(["pdftoppm", "-q", "-f", "1", "-l", "1", src_name, "tmpcover"])
            files = glob.glob("tmpcover-*.ppm")
            assert len(files) == 1
            ppm_name = files[0]
        elif type == "djvu":
            system(["ddjvu", "-format=ppm", "-page=1", "-size=" + size, src_name, "tmpcover.ppm"])
            ppm_name = "tmpcover.ppm"
        system(["convert", "-scale", size, ppm_name, "tmpcover.jpg"])
    except subprocess.CalledProcessError, e:
        log.error('Error executing page extraction command "%s", skipping %s', e.cmd, src_name)
        return None

    move_to_dest("tmpcover.jpg", dest_cover_path)
    log.info("Rendered %s cover to %s", type, dest_cover_path)
    return dest_cover_name


# Global vars
log = None
options = None
lib_root = None
dest_root = None

def main():
    global options, log, lib_root, dest_root
    oparser = optparse.OptionParser(usage="%prog <options> <lib path> <dest path>", description="""\
Make coverpage thumbnails for LibGen library, either by downloading them or
rendering from first page of PDF/DJVU files. Path to library is given by first
argument, covers are put under separate root specified by second argument
(may be equal to library path).""")

    oparser.add_option("", "--retry", type="int", default=3, help="Number of retries on network errors")
    oparser.add_option("", "--force", action="store_true", help="Ignore local files, force redownloading/reconversion")
    oparser.add_option("-n", "--dry-run", action="store_true", help="Don't write anything to DB")
    oparser.add_option("-d", "--debug", action="store_true", default=False, help="Show debug logging (e.g. SQL)")
    oparser.add_option("", "--cover-size", metavar="SIZE", type="int", default=500, help="Max coverpage dimension (only for rendered)")

    optgroup = optparse.OptionGroup(oparser, "Record selection options")
    optgroup.add_option("", "--all", action="store_true", help="Process all records")
    optgroup.add_option("", "--id", metavar="ID[-IDLAST]", help="Process record(s) with given id(s)")
    optgroup.add_option("", "--hash", help="Process only record with given hash")
    optgroup.add_option("", "--only-dl", action="store_true", help="Process only records requiring download")
    optgroup.add_option("", "--only-render", action="store_true", help="Process only records requiring rendering")
    optgroup.add_option("-l", "--limit", type="int", default=-1, help="Make at most LIMIT covers")
    oparser.add_option_group(optgroup)

    optgroup = optparse.OptionGroup(oparser, "DB connection options")
    optgroup.add_option("", "--db-host", default="localhost", help="DB host (%default)")
    optgroup.add_option("", "--db-name", default="bookwarrior", help="DB name (%default)")
    optgroup.add_option("", "--db-user", default="root", help="DB user")
    optgroup.add_option("", "--db-passwd", metavar="PASSWD", default="", help="DB password (empty)")
    oparser.add_option_group(optgroup)

    (options, args) = oparser.parse_args()
    if len(args) != 2:
        oparser.error("Wrong number of arguments")
    if len(filter(None, [options.all, options.id, options.hash])) != 1:
        oparser.error("One (and only one) of --all, --id= or --hash= must be specified")
    if not options.db_user:
        oparser.error("--db-user is required")

    logging.basicConfig(level=[logging.INFO, logging.DEBUG][options.debug])
    log = logging.getLogger()

    lib_root = args[0]
    dest_root = args[1]

    # Prepare range condition
    if options.all:
        range_where = ""
    elif options.id:
        m = re.match(r"(\d+)-(\d+)", options.id)
        if m:
            range_where = " AND ID BETWEEN %s AND %s" % (m.group(1), m.group(2))
        else:
            range_where = " AND ID=%d" % int(options.id)
    elif options.hash:
        range_where = " AND MD5='%s'" % options.hash

    # Prepare kind condition
    only_kind_where = ("Coverurl LIKE 'http:%'", "(Coverurl='' AND Extension IN ('pdf', 'djvu'))")
    if options.only_dl:
        only_kind_where = only_kind_where[0]
    elif options.only_render:
        only_kind_where = only_kind_where[1]
    else:
        only_kind_where = "(%s OR %s)" % only_kind_where

    conn = MySQLdb.connect(host=options.db_host, user=options.db_user, passwd=options.db_passwd, db=options.db_name, use_unicode=True)
    cursor = conn.cursor(LoggingReadCursor)
    cursor.execute("SELECT ID, Filename, Coverurl, Extension FROM updated WHERE " + only_kind_where + " AND Filename != '' " + range_where)
    cursor_write = conn.cursor(LoggingWriteCursor)

    total = 0
    processed = 0
    while True:
        if options.limit >= 0 and processed >= options.limit:
            break
        row = cursor.fetchone()
        if not row:
            break
        total += 1
        if row[2] == "":
            dest_cover_name = render_cover(row[1], row[3])
        else:
            dest_cover_name = download_cover(row[1], row[2])

        if dest_cover_name:
            cursor_write.execute("UPDATE updated SET Coverurl=%s WHERE ID=%s", (dest_cover_name, row[0]))
            processed += 1

    print "Total records processed: %d, new covers made: %d" % (total, processed)
    conn.commit()
    cursor.close()
    conn.close()

if __name__ == "__main__":
    main()

