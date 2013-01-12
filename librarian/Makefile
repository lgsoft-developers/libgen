DB=libgen.sqlite3

.%.sql: %.sql
	time sqlite3 $(DB) <$^
	touch $@

all: $(DB) .service.sql .description.sql .updated.sql

$(DB): schema.sql
	rm -f $(DB) .*.sql
	sqlite3 $@ <$^

index:
	echo "CREATE UNIQUE INDEX update_md5 ON updated(md5);" | sqlite3 $(DB)
	echo "CREATE UNIQUE INDEX description_md5 ON description(md5);" | sqlite3 $(DB)

libgen.csv: $(DB)
	echo "SELECT Filename, Filesize, MD5 FROM updated WHERE Filename != '';" | sqlite3 -csv $(DB) >$@
