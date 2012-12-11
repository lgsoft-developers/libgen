<?php

/**
 * @author tigranav
 * @copyright 2009
 */
 
 
// http://tools.ietf.org/html/rfc2616#page-143
// http://ru2.php.net/fsockopen
// http://ru.php.net/manual/en/function.rtrim.php
 
 
 
function torrent_login($login,$password,$path){
global $cookies;
$resp = sendToHost('torrents.ru','POST',$path,'login_username=$login&login_password=$password&login=%C2%F5%EE%E4');	
}

function parse_book_topic($topic,$forum) {
	global $cashing;
	global $_SERVER;
	global $mysql;
	//global $cookies;
//if ($cashing) {
//	if (!file_exists($_SERVER['DOCUMENT_ROOT']."/topic_$topic.cashe")) {
//		$resp = sendToHost('torrents.ru','GET',"/forum/viewtopic.php?t=$topic",'');
//		file_write($_SERVER['DOCUMENT_ROOT']."/topic_$topic.cashe",$resp);
//		}
//		else 
//		$resp=file_read($_SERVER['DOCUMENT_ROOT']."/topic_$topic.cashe");
//	} else 
$resp = sendToHost('torrents.ru','GET',"/forum/viewtopic.php?t=$topic",'');
	//echo $resp;
	if (preg_match('/<span style="font-size: 24px; line-height: normal;">(.*?)<\/span>/',$resp,$r))	$name=$r[1];
	if (preg_match('/Год выпуска.*(\d{4})/',$resp,$r))	$year=$r[1]; else $year=0;
	if (preg_match('/<span class="post-b">Автор<\/span>:(.*)<br \/>/',$resp,$r))	$author=chop($r[1]); else $author='';
	if (preg_match('/<span class="post-b">Количество страниц<\/span>:(.*)<br \/>/',$resp,$r))	$pages=chop($r[1]);  else $pages='';
	if (preg_match('/<span class="post-b">Издательство<\/span>:(.*)<br \/>/',$resp,$r))	$izdat=chop($r[1]);  else $izdat='';     
	if (preg_match('/<span class="post-b">ISBN<\/span>:(.*)<br \/>/',$resp,$r))	$isbn=chop($r[1]);  else $isbn='';
	if (preg_match('/<span class="post-b">Формат<\/span>:(.*)<br \/>/',$resp,$r))	$format=chop($r[1]);  else $format='';
	if (preg_match('/<span class="post-b">Описание<\/span>:(.*?)<div class="clear">/s',$resp,$r))	$desc=$r[1];  else $desc='';
	if (preg_match('/<span class="post-b">Жанр<\/span>:(.*)<br \/>/',$resp,$r))	$zhanr=chop($r[1]);  else $zhanr='';
	if (preg_match('/<span class="post-b">Серия<\/span>:(.*)<br \/>/',$resp,$r))	$serie=chop($r[1]);  else $serie='';
	if (preg_match('/<span class="post-b">Качество<\/span>:(.*)<br \/>/',$resp,$r))	$kachestvo=chop($r[1]);  else $kachestvo='';
	if (preg_match('/"Зарегистрирован">\[ (.*) \]/',$resp,$r))	$registred=chop($r[1]);  else $registred='';
	//print_r($r);
	//<span title="Зарегистрирован">[ 2009-07-19 23:21 ]</span>
	//preg_match('/<span class="post-b">ISBN<\/span>: (.*)/',$resp,$r);
	//print_r($r);
	$sql="insert into tnv_torrents_topics (topicid,forumid,name,year,author,pages,izdat,isbn,format,descr,insert_date,zhanr,serie,kachestvo,registred,topic) 
	values ($topic,$forum,'".addslashes($name)."','".addslashes($year)."','".addslashes($author)."','".addslashes($pages)."'
	,'".addslashes($izdat)."','".addslashes($isbn)."','".addslashes($format)."','".addslashes($desc)."',NOW(),'".addslashes($zhanr)."','".addslashes($serie)."','".addslashes($kachestvo)."','$registred','$resp')";
	echo $sql; exit();
	$res=$mysql->query($sql); 
	//echo $mysql->getError($res);
//	echo "Name : $name <br>\n";
//	echo "Год выпуска : $year <br>\n";
//	echo "Автор : $author <br>\n";
//	echo "Количество страниц : $pages <br>\n";
//	echo "Издательство : $izdat <br>\n";
//	echo "ISBN : $isbn <br>\n";
//	echo "Формат : $format <br>\n";
//	echo "Описание : $desc <br>\n";
//<div class="post_body" id="p-25785504-1">
//				<span style="font-size: 24px; line-height: normal;">Vidal 2009. Справочник Видаль. Лекарственные препараты в России</span><span class="post-br"><br></span><var class="postImg postImgAligned img-right" title="http://s47.radikal.ru/i116/0903/9f/e2c997372886.jpg"><img src="http://s47.radikal.ru/i116/0903/9f/e2c997372886.jpg" class="postImg postImgAligned img-right" alt="pic"></var><span class="post-b">Год выпуска</span>: 2009<br>
//<span class="post-b">Автор</span>: АстраФармСервис<br>
//<span class="post-b">Жанр</span>: Медицина<br>
//<span class="post-b">Издательство</span>: АстраФармСервис<br>
//<span class="post-b">ISBN</span>: 978-5-89892-118-7<br>
//
//<span class="post-b">Формат</span>: HTML<br>
//<span class="post-b">Качество</span>: eBook (изначально компьютерное)<br>
//<span class="post-b">Количество страниц</span>: 1760<br>
//<span class="post-b">Описание</span>: Справочник Видаль содержит информацию о 2425 лекарственных препаратах и активных веществах, представленных на российском фармацевтическом рынке 405 предприятиями и фирмами 43 стран. Полные описания включают торговое название препарата, рекомендованное международное наименование, состав и формы выпуска (с фотографиями упаковок), фармакологическое действие, фармакокинетические параметры, показания к применению и режимы дозирования для различных групп пациентов, побочные эффекты, противопоказания, симптомы передозировки, лекарственное взаимодействие, особые указания по применению, условия хранения и отпуска из аптек. Удобный поиск обеспечивается указателями: клинико-фармакологических групп, международных наименований, нозологическим и кодов АТХ системы классификации.<br>
//Информационные страницы ведущих фармфирм содержат полный перечень производимых препаратов с регистрационными номерами, а также адреса и телефоны представительств.<span class="post-br"><br></span>Справочник Видаль предназначен для широкого круга специалистов: врачей всех специальностей, фармацевтов, провизоров, преподавателей и студентов медицинских и фармацевтических ВУЗов, а также специалистов, связанных с лекарственным обеспечением.<span class="post-br"><br></span><span style="color: red;"><span class="post-b">Образ оригинального диска справочника</span></span>									
//					
//
//<div class="clear"></div>
		
}


function get_forum_topics($forum,$cashing=true) {
	$all_topics=array ();
	global $_SERVER;
	global $cashing;
//if ($cashing) {
//	if (!file_exists($_SERVER['DOCUMENT_ROOT']."/$forum.cashe")) {
//		$resp = sendToHost('torrents.ru','GET',"/forum/viewforum.php?f=$forum",'');
//		file_write($_SERVER['DOCUMENT_ROOT']."/$forum.cashe",$resp);
//		}
//		else 
//		$resp=file_read($_SERVER['DOCUMENT_ROOT']."/$forum.cashe");
//	}
//	else 
	$resp = sendToHost('torrents.ru','GET',"/forum/viewforum.php?f=$forum",'');
preg_match_all('/<a class="pg" href="viewforum.php\?f=\d+&amp;start=\d+">(\d+)<\/a>/s',$resp,$results);
echo $resp; 
$pages=$results[1][count($results[1])-1];
tolog('fill_database','get_forum_topics',"Получение первой страницы форума $forum \n\n".$resp);
for($i=0;$i<$pages;$i++) {
//	if (!file_exists($_SERVER['DOCUMENT_ROOT']."/$forum-$i.cashe") ) {
//	$resp = sendToHost('torrents.ru','GET',"/forum/viewforum.php?f=$forum&start=".($i*50),'');
//	file_write($_SERVER['DOCUMENT_ROOT']."/$forum-$i.cashe",$resp);
//	}
//	else 
//	$resp=file_read($_SERVER['DOCUMENT_ROOT']."/$forum-$i.cashe");
	$resp = sendToHost('torrents.ru','GET',"/forum/viewforum.php?f=$forum&start=".($i*50),'');
	tolog('fill_database','get_forum_topics',"Получение $i страницы форума $forum \n\n".$resp);
	preg_match('/<td colspan="6" class="row3 topicSep">Топики<\/td>(.*)/s',$resp,$r);
	if ($r) $resp=$r[1];
	preg_match_all('/viewtopic.php\?t=(\d+)/s',$resp,$topics);
	$all_topics=array_merge_recursive($all_topics,$topics[1]); 

}
tolog('fill_database','get_forum_topics',"Конец получения топиков $forum \n\n".$all_topics);
return $all_topics;
}


function sendToHost($host,$method,$path,$data,$useragent=0){
    $buf = '';
    global $cookies;
    $cooks = $cookies;

    if (!$fp = fsockopen($host, 80, $errno, $errstr, 30) )
        return false;

    if (empty($method)) {
        $method = 'GET';
    }
    $method = strtoupper($method);
    if ($method == 'GET') {
        $path .= '?' . $data;
    }
    $out = "$method $path HTTP/1.1\r\n";

    $out .= "Host: $host\r\n";
    $out .= "Referer: http://$host\r\n";
    $out .= "Content-type: application/x-www-form-urlencoded\r\n";

		while ($cook = array_pop($cooks)) {$out.="Cookie: $cook\r\n"; }

    if ($useragent) {
        $out .= "User-Agent: MSIE\r\n";
    }
    $out .= "Content-length: " . strlen($data) . "\r\n";

    $out .= "Connection: Close\r\n\r\n";
    
    if ($method == 'POST') {
        $out .= $data;
    }
	//print $out; 
    if (! fwrite($fp, $out) )
        return false;

    while (!feof($fp)) {
        $buffer= fgets($fp, 4000);
        $buf .=$buffer;
        // Добавляем новые куки
		if (preg_match('/Set-Cookie:(.*)/',$buffer,$result)){
		if (!isset($cookie[$result[1]])) array_push($cookies,chop($result[1]));

		}        
    }
    fclose($fp);
    return $buf;
}  


function get_new_topics($pars) {
	if (!isset($pars['days'])) $pars['days']=1;
	if (!isset($pars['new'])) $pars['new']=0;
	$all_topics=array ();
	global $_SERVER;
	global $cashing;
	global $mysql;
	$ss='';
	$sql="select * from tnv_torrents_forums where parentid is not null";
	$res=$mysql->query($sql);
	while ($row = $mysql->FETCH_ASSOC($res)) {
		$ss.='f[]='.$row['forumid'].'&';
		}
	$ss=trim($ss,'&');
	if ($pars['new']==1) $ss.='&new=1';
	$ss.='&tm='.$pars['days'];
	$ss.='&o=1&s=2&df=1';
	$resp = sendToHost('torrents.ru','POST','/forum/tracker.php',$ss);
	if ($resp_=preg_match('/tor-tbl(.*)/s',$resp,$results)) $resp=$results[0];


	//print($resp); //exit;
	//preg_match_all('/<a class="pg" href="tracker.php\?search_id=(.*?)\&amp;start=\d*">(\d*)<\/a>/s',$resp,$results);
	preg_match_all('/<a class="pg" href="tracker.php\?(search_id=.*?\&amp;start=\d*)">\d*<\/a>/s',$resp,$results);
	$pages=$results[1]; print_r($pages); //exit;
	preg_match_all('/viewtopic.php\?t=(\d+)/s',$resp,$topics);
	$all_topics=array_merge_recursive($all_topics,$topics[1]);
	//echo ($resp);
	//print_r($all_topics);
	//exit;	
	for($i=0;$i<count($pages);$i++){
		///print('+++++++++++++++'.$pages[$i]);
		$ss=str_replace('&amp;','&',$pages[$i]);
		$resp = sendToHost('torrents.ru','POST','/forum/tracker.php',$ss);
		$resp_=preg_match('/tor-tbl(.*)/s',$resp,$results); $resp=$results[0];
		//print $resp;
		preg_match_all('/viewtopic.php\?t=(\d+)/s',$resp,$topics);
		$all_topics=array_merge_recursive($all_topics,$topics[1]);
		//exit;			
	}
	//print_r($all_topics); //exit;

	return $all_topics;
}
//------------------------------------------------------------------------------------------------
function parse_book_topic2($topic) {
	global $cashing;
	global $_SERVER;
	global $mysql; //"viewforum.php?f=1683"
	$resp = sendToHost('torrents.ru','GET',"/forum/viewtopic.php?t=$topic",'');
	//echo $resp;
	if (preg_match_all('/viewforum.php\?f=(\d+)/',$resp,$r)) $forum=$r[1]; $forumid=$r[1][count($r[1])-1]; // echo $forumid;
	//exit; //$author=chop($r[1]); else $author='';
	if (preg_match('/<span style="font-size: 24px; line-height: normal;">(.*?)<\/span>/',$resp,$r))	$name=$r[1]; else $name='???';
	if (preg_match('/Год выпуска.*(\d{4})/',$resp,$r))	$year=$r[1]; else $year=0;
	if (preg_match('/<span class="post-b">Автор<\/span>:(.*)<br \/>/',$resp,$r))	$author=chop($r[1]); else $author='';
	if (preg_match('/<span class="post-b">Количество страниц<\/span>:(.*)<br \/>/',$resp,$r))	$pages=chop($r[1]);  else $pages='';
	if (preg_match('/<span class="post-b">Издательство<\/span>:(.*)<br \/>/',$resp,$r))	$izdat=chop($r[1]);  else $izdat='';     
	if (preg_match('/<span class="post-b">ISBN<\/span>:(.*)<br \/>/',$resp,$r))	$isbn=chop($r[1]);  else $isbn='';
	if (preg_match('/<span class="post-b">Формат<\/span>:(.*)<br \/>/',$resp,$r))	$format=chop($r[1]);  else $format='';
	if (preg_match('/<span class="post-b">Описание<\/span>:(.*?)<div class="clear">/s',$resp,$r))	$desc=$r[1];  else $desc='';
	if (preg_match('/<span class="post-b">Жанр<\/span>:(.*)<br \/>/',$resp,$r))	$zhanr=chop($r[1]);  else $zhanr='';
	if (preg_match('/<span class="post-b">Серия<\/span>:(.*)<br \/>/',$resp,$r))	$serie=chop($r[1]);  else $serie='';
	if (preg_match('/<span class="post-b">Качество<\/span>:(.*)<br \/>/',$resp,$r))	$kachestvo=chop($r[1]);  else $kachestvo='';
	if (preg_match('/"Зарегистрирован">\[ (.*) \]/',$resp,$r))	$registred=chop($r[1]);  else $registred='';
	//echo date('y-m-d H:i:s',strtotime($registred));
	//26-Май-09 00:13
	$mons=array ('Янв'=>'01','Фев'=>'02','Мар'=>'03','Апр'=>'04','Май'=>'05','Июн'=>'06','Июл'=>'07','Авг'=>'08','Сен'=>'09','Окт'=>'10','Ноя'=>'11','Дек'=>'12');
	list($date,$time)=split(' ',$registred);
	list($day,$mon,$year) = split('-',$date);
	list($h,$m) = split(':',$time);
	$registred = "$year-".$mons[$mon]."-$day $h:$m:00";
	//print_r($r);
	//<span title="Зарегистрирован">[ 2009-07-19 23:21 ]</span>
	//preg_match('/<span class="post-b">ISBN<\/span>: (.*)/',$resp,$r);
	//print_r($r);
	$sql="insert into tnv_torrents_topics (topicid,forumid,name,year,author,pages,izdat,isbn,format,descr,insert_date,zhanr,serie,kachestvo,registred) 
	values ($topic,$forumid,'".addslashes($name)."','".addslashes($year)."','".addslashes($author)."','".addslashes($pages)."'
	,'".addslashes($izdat)."','".addslashes($isbn)."','".addslashes($format)."','".addslashes($desc)."',NOW(),'".addslashes($zhanr)."','".addslashes($serie)."','".addslashes($kachestvo)."','$registred')";
	//echo $sql; //exit();
	$res=$mysql->query($sql); 
	print "Сохраняем: $name <br>\n";
}

function win2utf($str)
{
  $utf = "";
  for($i = 0; $i < strlen($str); $i++)
  {
    $donotrecode = false;
    $c = ord(substr($str, $i, 1));
    if ($c == 0xA8) $res = 0xD081;
    elseif ($c == 0xB8) $res = 0xD191;
    elseif ($c < 0xC0) $donotrecode = true;
    elseif ($c < 0xF0) $res = $c + 0xCFD0;
    else $res = $c + 0xD090;
    $utf .= ($donotrecode) ? chr($c) : (chr($res >> 8) . chr($res & 0xff));
  }
  return $utf;
}


function safe_text($text) {
	$text=strip_tags($text);
	$text=str_replace('"','',$text);
	$text=str_replace("'",'',$text);
	$text=str_replace("/",'',$text);
	$text=str_replace("|",'',$text);
	$text=str_replace('\\','',$text);
	$text=str_replace(':','',$text);	
	if (strlen($text)>70) $text=substr($text,0,70); 
	$text=chop($text,' ');
	return 	$text;
}

function get_new_torrents(){
	global $mysql;
	global $_SERVER;
	global $path_to_save;
	global $dest_path;
	$cmd='';
	$sql="select f.forumname,t.* from tnv_torrents_forums f,tnv_torrents_topics t where t.status=0 and t.forumid=f.forumid limit 50";
	$res = $mysql->query($sql);
	while ($row = $mysql->fetch_assoc($res)) {
sleep(5);
		if (download_torrent3($row['topicid'],SITE_ROOT."/$path_to_save\\".$row['topicid'].'.torrent')) {
			echo "loaded  : ".$row['forumname']." ".$row['name']."<br>\n";

			$forumname=safe_text($row['forumid']."-".iconv('Windows-1251','866//IGNORE',$row['forumname']));
			$topicname=safe_text($row['topicid']."-".iconv('Windows-1251','866//IGNORE',$row['name']));			
		
		$cmd.="\"c:\\Program files\\utorrent\\utorrent.exe\" /directory \"$desc_path\\".$forumname."\\".$topicname."\" \"".$_SERVER['DOCUMENT_ROOT']."\\torrents\\".$row['topicid'].".torrent\"\n";
		$mysql->query("update tnv_torrents_topics set status=1 where topicid=".$row['topicid']);
		}
		else
		$mysql->query("update tnv_torrents_topics set status=2 where topicid=".$row['topicid']);	
	}

	file_write(SITE_ROOT."/$path_to_save\\torrent.cmd",$cmd);
	
}



function download_torrent($topic,$filename) {
	$resp = sendToHost('torrents.ru','GET','/forum/dl.php?t='.$topic,'');
	list ($header,$content) = split("\r\n\r\n",$resp);
	//echo $content; exit;
	$i = strpos($content,"\r\n");
	$content=substr($content,$i+2,strlen($content)-$i-2);
	file_write($filename,$content);
}

function download_torrent2($topic,$filename) {
	$resp = sendToHost2('torrents.ru','GET','/forum/dl.php?t='.$topic,'');
	list ($header,$content) = split("\r\n\r\n",$resp);
	echo $resp; 
	if (!preg_match('/Content-Type: text\/html/',$header,$r)) {
		$i = strpos($content,"\r\n");
		$content=substr($content,$i+2,strlen($content)-$i-2);
		file_write($filename,$content);
		return true;
	}
	else {
		if (preg_match('/статус: <b>(.*?)<\/b>/s',$content,$r)) { $status=$r[1];} else {$status='unknown';}
		file_write($filename.".$status",'');
		return false;	
	}
}


function download_torrent3($topic,$filename) {
	$resp = sendToHost2('torrents.ru','GET','/forum/dl.php?t='.$topic,'');
	list ($header,$content) = split("\r\n\r\n",$resp);
	//echo $resp; 
	if (!preg_match('/Content-Type: text\/html/',$header,$r)) {
		//$i = strpos($content,"\r\n");
		$content=parseHttpResponse($resp);
		//Почему то в ответе первой строкой идет длинна, а последней ответ - почему то не обрабтывается это во внешней функции. 
		//Убираем ненужное.
		//$cont=split("\r\n",$content); //print_r($cont);
		//array_shift($cont); //array_pop($cont);
		//$content=join("",$cont);
		//$content=rstrtrim($content,"\r\n0");
		file_write($filename,$content);
		return true;
	}
	else {
		if (preg_match('/статус: <b>(.*?)<\/b>/s',$content,$r)) { $status=$r[1];} else {$status='unknown';}
		file_write($filename.".$status",$resp);
		return false;	
	}
}

function sendToHost2($host,$method,$path,$data,$useragent=0){
    $buf = '';
    global $cookies;
    $cooks = $cookies;
    
    tolog('forents_functions','sendToHost2',"start  $host $method $path $data");

    if (!$fp = fsockopen($host, 80, $errno, $errstr, 30) )
        return false;

    $out = "GET $path HTTP/1.1\r\n";
    $out .= "Host: $host\r\n";
    $out .= "Referer: http://$host\r\n";
	while ($cook = array_pop($cooks)) {$out.="Cookie: $cook\r\n"; }
    $out .= "User-Agent: MSIE\r\n";
    $out .= "Connection: Close\r\n\r\n";
    
	if (! fwrite($fp, $out) )
        return false;

    while (!feof($fp)) {
        //$buffer= fgets($fp,128);
        //if (!$buffer) echo "** ERROR **";
        //print "[?]\n";
        $buffer= fgetc($fp);
        $buf .=$buffer;
    }
//	while (!feof($fp)) {
//	  $buf .= fread($fp, 8192);
//	}
    fclose($fp);
    tolog('forents_functions','sendToHost2',"end  $host $method $path $data");
    return $buf;
}  


function parseHttpResponse($content=null) {
    if (empty($content)) { return false; }
    // split into array, headers and content.
    $hunks = explode("\r\n\r\n",trim($content));
    if (!is_array($hunks) or count($hunks) < 2) {
        return false;
        }
    $header  = $hunks[count($hunks) - 2];
    $body    = $hunks[count($hunks) - 1];
    $headers = explode("\n",$header);
    unset($hunks);
    //unset($header);
    //print_r($headers);
    if (!validateHttpResponse($headers)) { return false; }
    if (preg_match('/Transfer-Encoding: chunked/s',$header)) {
    	
    	//echo "include"; exit;
    	//echo trim(unchunkHttpResponse($body)); exit;
        return trim(unchunkHttpResponse($body));
        } else {
        return trim($body);
        }
    }

//
// Validate http responses by checking header.  Expects array of
// headers as argument.  Returns boolean.
//
function validateHttpResponse($headers=null) {
    if (!is_array($headers) or count($headers) < 1) { return false; }
    switch(trim(strtolower($headers[0]))) {
        case 'http/1.0 100 ok':
        case 'http/1.0 200 ok':
        case 'http/1.1 100 ok':
        case 'http/1.1 200 ok':
            return true;
        break;
        }
    return false;
    }

//
// Unchunk http content.  Returns unchunked content on success,
// false on any errors...  Borrows from code posted above by
// jbr at ya-right dot com.
//
function unchunkHttpResponse($str=null) {
    if (!is_string($str) or strlen($str) < 1) { return false; }
    $eol = "\r\n";
    $add = strlen($eol);
    $tmp = $str;
    $str = '';
    do {
        $tmp = ltrim($tmp);
        $pos = strpos($tmp, $eol);
        if ($pos === false) { return false; }
        $len = hexdec(substr($tmp,0,$pos));
        if (!is_numeric($len) or $len < 0) { return false; }
        $str .= substr($tmp, ($pos + $add), $len);
        $tmp  = substr($tmp, ($len + $pos + $add));
        $check = trim($tmp);
        } while(!empty($check));
    unset($tmp);
    return $str;
    }

function rstrtrim($str, $remove=null)
{
    $str    = (string)$str;
    $remove = (string)$remove;   
   
    if(empty($remove))
    {
        return rtrim($str);
    }
   
    $len = strlen($remove);
    $offset = strlen($str)-$len;
    while($offset > 0 && $offset == strpos($str, $remove, $offset))
    {
        $str = substr($str, 0, $offset);
        $offset = strlen($str)-$len;
    }
   
    return rtrim($str);   
   
} //End of function rstrtrim($str, $remove=null) 

function tolog($module,$func,$message) {
	global $mysql;
	$sql="insert into tnv_log (event_date,module,func,message) values (NOW(),'".addslashes($module)."','".addslashes($func)."','".addslashes($message)."')";
	if(!$mysql->query($sql)) echo mysql_error();

}
?>