<?php

/**
 * @author 
 * @copyright 2009
 */

function torrent_login() {
	global $torrent_username;
	global $torrent_userpass;
	$url = 'http://login.torrents.ru/forum/login.php';
	$postdata = "login_username=$torrent_username&login_password=$torrent_userpass&login=Вход";
	//print $postdata;
  	$result = post_content( $url, $postdata );
  	return $result;
}


function torrent_post_content($url,$postdata) {
	$result=post_content($url,$postdata);
	$content=$result['content'];
	if (strpos($content,'http://login.torrents.ru/forum/login.php')==0){
	return 	$result;
	}
	else {
		torrent_login();
		$result=post_content($url,$postdata);
		return 	$result;	
	}
	
}

function torrent_get_content($url) {
	$result=get_content($url);
	$content=$result['content'];
	if (strpos($content,'http://login.torrents.ru/forum/login.php')==0){
	return 	$result;
	}
	else {
		torrent_login();
		$result=get_content($url);
		return 	$result;	
	}
	
}

//Получить список все топиков указанного форума.
function get_forum_topics($forumid) {
	$all_topics=array ();
	global $opt;
	$result=torrent_get_content("http://torrents.ru/forum/viewforum.php?f=$forumid"); $resp=$result['content'];
	if ($opt->get_variable(9)==1)  echo $resp;
	preg_match_all('/<a class="pg" href="viewforum.php\?f=\d+&amp;start=\d+">(\d+)<\/a>/s',$resp,$results);
	//echo $resp; 
	$pages=$results[1][count($results[1])-1]; echo "found $pages pages<br>\n";
	//tolog('fill_database','get_forum_topics',"Получение первой страницы форума $forum \n\n".$resp);
	for($i=0;$i<$pages;$i++) {
		$result=torrent_get_content("http://torrents.ru/forum/viewforum.php?f=$forumid&start=".($i*50)); $resp=$result['content'];
		if ($opt->get_variable(9)==1)  echo $resp;
		preg_match('/<td colspan="6" class="row3 topicSep">Топики<\/td>(.*)/s',$resp,$r);
		print "look $i page<br>\n";
		if ($r) $resp=$r[1];
		preg_match_all('/viewtopic.php\?t=(\d+)/s',$resp,$topics);
		$all_topics=array_merge_recursive($all_topics,$topics[1]); 
		}
//tolog('fill_database','get_forum_topics',"Конец получения топиков $forum \n\n".$all_topics);
return $all_topics;
}



//Получение топиков через трекер  get_new_topics(array('new'=>0,'days'=>3))
function get_new_topics($pars) {
	$all_topics=array ();
	global $mysql;
	global $opt;
	if (!isset($pars['days'])) $pars['days']=$opt->get_variable(2);
	if (!isset($pars['new'])) $pars['new']=$opt->get_variable(8);
	$ss='';
	$sql="select * from tnv_torrents_forums where status=1";
	$res=$mysql->query($sql);
	while ($row = $mysql->FETCH_ASSOC($res)) {
		$ss.='f[]='.$row['forumid'].'&';
		}
	$ss=trim($ss,'&');
	if ($pars['new']==1) $ss.='&new=1';
	$ss.='&tm='.$pars['days'];
	$ss.='&o=1&s=2&df=1';

	$result = torrent_post_content( "http://torrents.ru/forum/tracker.php", $ss ); $resp=$result['content'];
	if ($opt->get_variable(9)==1) print($resp); //exit;
	ob_flush();
    flush();
	//preg_match_all('/<a class="pg" href="tracker.php\?search_id=(.*?)\&amp;start=\d*">(\d*)<\/a>/s',$resp,$results);
	preg_match_all('/viewtopic.php\?t=(\d+)/s',$resp,$topics);
	$all_topics=array_merge_recursive($all_topics,$topics[1]);
	if (preg_match_all('/<a class="pg" href="tracker.php\?(search_id=.*?\&amp;start=\d*)">\d*<\/a>/s',$resp,$results))  {
		$pages=$results[1]; 
		//print_r($pages); //exit;
		for($i=0;$i<count($pages);$i++){
			print('+++++++++++++++'.$pages[$i]);
			$ss=str_replace('&amp;','&',$pages[$i]);
			
			//$resp = sendToHost('torrents.ru','POST','/forum/tracker.php',$ss);
			$result = torrent_get_content( "http://torrents.ru/forum/tracker.php?$ss" ); $resp=$result['content'];
			//print("************$ss<br>\n");
			$resp_=preg_match('/tor-tbl(.*)/s',$resp,$results); $resp=$results[0];
			if ($opt->get_variable(9)==1)  print $resp;
			ob_flush();
    		flush();
			preg_match_all('/viewtopic.php\?t=(\d+)/s',$resp,$topics);
			$all_topics=array_merge_recursive($all_topics,$topics[1]);
		//exit;			
		}
	//print_r($all_topics); //exit;
	}
	//exit;
	return $all_topics;
}




//Парсинг топика книжки и сохранение в базе.
function parse_book_topic2($topic) {
	global $mysql; //"viewforum.php?f=1683"
	global $opt;
	//$resp = sendToHost('torrents.ru','GET',"/forum/viewtopic.php?t=$topic",'');
	$result=torrent_get_content("http://torrents.ru/forum/viewtopic.php?t=$topic"); $resp=$result['content'];
	if ($opt->get_variable(9)==1) echo $resp;
	
	if (preg_match_all('/viewforum.php\?f=(\d+)/',$resp,$r)) $forum=$r[1]; $forumid=$r[1][count($r[1])-1]; // echo $forumid;
	//exit; //$author=chop($r[1]); else $author='';
	if (preg_match('/<span style="font-size: 24px; line-height: normal;">(.*?)<\/span>/',$resp,$r))	$name=strip_tags($r[1]); else $name='';
	//if (preg_match('/Год выпуска.*(\d{4})<br>/',$resp,$r))	$book_year=$r[1]; else $book_year=0; 
	
	if (preg_match('/Год выпуска.*(\d{4})<br \/>/',$resp,$r))	$book_year=$r[1]; else $book_year=0; 

	print_r($r);
	if (preg_match('/<span class="post-b">Автор<\/span>:(.*)<br \/>/',$resp,$r))	$author=chop($r[1]); else $author='';
	if (preg_match('/<span class="post-b">Количество страниц<\/span>:(.*)<br \/>/',$resp,$r))	$pages=chop($r[1]);  else $pages='';
	if (preg_match('/<span class="post-b">Издательство<\/span>:(.*)<br \/>/',$resp,$r))	$izdat=chop($r[1]);  else $izdat='';     
	if (preg_match('/<span class="post-b">ISBN<\/span>:(.*)<br \/>/',$resp,$r))	$isbn=chop($r[1]);  else $isbn='';
	if (preg_match('/<span class="post-b">Формат<\/span>:(.*)<br \/>/',$resp,$r))	$format=chop($r[1]);  else $format='';
	if (preg_match('/<span class="post-b">Описание<\/span>:(.*?)<div class="clear">/s',$resp,$r))	$desc=$r[1];  else $desc='';
	if (preg_match('/<span class="post-b">Жанр<\/span>:(.*)<br \/>/',$resp,$r))	$zhanr=chop($r[1]);  else $zhanr='';
	if (preg_match('/<span class="post-b">Серия<\/span>:(.*)<br \/>/',$resp,$r))	$serie=chop($r[1]);  else $serie='';
	if (preg_match('/<span class="post-b">Качество<\/span>:(.*)<br \/>/',$resp,$r))	$kachestvo=chop($r[1]);  else $kachestvo='';
	
	//<var class="postImg postImgAligned img-right" title="http://pic.ipicture.ru/uploads/091116/3oMAOKvlT4.jpg">
	if (preg_match('/<var class="postImg .*?" title="(.*?)">/',$resp,$r))	//$kachestvo=chop($r[1]);  else $kachestvo='';
	{
		$cover_url=$r[1];
	}
	
	if (preg_match('/"Зарегистрирован">\[ (.*) \]/',$resp,$r))	$registred=chop($r[1]);  else $registred='';
	$mons=array ('Янв'=>'01','Фев'=>'02','Мар'=>'03','Апр'=>'04','Май'=>'05','Июн'=>'06','Июл'=>'07','Авг'=>'08','Сен'=>'09','Окт'=>'10','Ноя'=>'11','Дек'=>'12');
	list($date,$time)=split(' ',$registred);
	list($day,$mon,$year) = split('-',$date);
	list($h,$m) = split(':',$time);
	$registred = "$year-".$mons[$mon]."-$day $h:$m:00";


	
	if (preg_match('/http:\/\/dl.torrents.ru\/forum\/dl.php\?t=(\d+)/',$resp,$r))	$torrent_file_id=$r[1];  else $torrent_file_id=0;
	//echo "$torrent_file_id";
	$sql="select * from tnv_torrents_topics where topicid=$topic";
	$res=$mysql->query($sql);
	if ($mysql->num_rows($res)==0) {
		$sql="insert into tnv_torrents_topics (topicid,forumid,name,year,author,pages,izdat,isbn,format,descr,insert_date,zhanr,serie,kachestvo,registred,status,torrent_file_id) 
		values ($topic,$forumid,'".addslashes($name)."','".addslashes($book_year)."','".addslashes($author)."','".addslashes($pages)."'
		,'".addslashes($izdat)."','".addslashes($isbn)."','".addslashes($format)."','".addslashes($desc)."',NOW(),'".addslashes($zhanr)."','".addslashes($serie)."','".addslashes($kachestvo)."','$registred',0,$torrent_file_id)";
		//echo $sql; //exit();
		$res=$mysql->query($sql); 
		print "Сохраняем: $name <br>\n";
	}

}

//Получение торрент-файла.
 function download_torrent($topic_id) {
 	global $mysql;
 	global $opt;
 	$path=$opt->get_variable(4);
 	$sql="select * from tnv_torrents_topics where topicid=$topic_id";
 	$res=$mysql->query($sql);
 	if ($mysql->num_rows($res)==1) {
 		$row=$mysql->FETCH_ASSOC($res); //print_r($row); echo "http://dl.torrents.ru/forum/dl.php?t=".$row['topic_file_id'];
 		$result=torrent_get_content("http://dl.torrents.ru/forum/dl.php?t=".$row['torrent_file_id']);
 		$resp=$result['content'];
 		if (file_write("$path/$topic_id.torrent",$resp)) {
 			//$sql="update tnv_torrents_topics set status=1 where topicid=$topic_id";
 			//$res=$mysql->query($sql);
 			return true;
 		} else return false;
 	}
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


function get_new_torrents($date_start='',$date_end=''){
	global $mysql;
	global $opt;
	
	$cmd='';
	$sql="select f.forumname,t.* from tnv_torrents_forums f,tnv_torrents_topics t where t.status=0 and t.forumid=f.forumid ";
	if ($date_start!='') {
		$sql.=" and t.registred >= '".$opt->get_variable(10)."' ";
	}
	$sql.=" limit ".$opt->get_variable(11);
	$res = $mysql->query($sql);
	while ($row = $mysql->fetch_assoc($res)) {
sleep(5);
		if (download_torrent($row['topicid'])) {
			echo "loaded  : ".$row['forumname']." ".$row['name']."<br>\n";
			//echo " >> ".$row['forumname']."  : ".$row['forumid']."-".iconv('Windows-1251','866IGNORE',$row['forumname'])."<br>\n";
			//echo " >> ".$row['name']."  :".$row['topicid']."-".iconv('Windows-1251','866IGNORE',$row['name'])."<br>\n";

			$forumname=safe_text($row['forumid']."-".iconv('Windows-1251','866IGNORE',$row['forumname']));
			$topicname=safe_text($row['topicid']."-".iconv('Windows-1251','866IGNORE',$row['name']));			
		
		$cmd.="\"c:\\Program files\\utorrent\\utorrent.exe\" /directory \"".$opt->get_variable(5)."\\".$forumname."\\".$topicname."\" \"".$opt->get_variable(4)."\\".$row['topicid'].".torrent\"\n";
		$mysql->query("update tnv_torrents_topics set status=1 where topicid=".$row['topicid']);
		}
		else
		$mysql->query("update tnv_torrents_topics set status=2 where topicid=".$row['topicid']);	
	}

	file_write(str_replace('\\','\\\\',$opt->get_variable(4))."\\torrent.cmd",$cmd);
	
}




function fill_database (){
	global $mysql;
	global $opt;	
 	$sql="select * from tnv_torrents_forums where status=1 order by forumid";
 	$res=$mysql->query($sql);
 	while ($row = $mysql->fetch_assoc($res)) {
 		$topics = get_forum_topics($row['forumid']);
 		echo "Found ".count($topics)." topics<br>\n";
 		foreach ($topics as $topic) {
 			$sql="select * from tnv_torrents_topics where topicid=$topic";
 			$res=$mysql->query($sql);
 			if ($mysql->num_rows($res)==0) {
 				parse_book_topic2($topic);
 				sleep(2);
 				}
 			}
 		}
	}
	
	
	
?>