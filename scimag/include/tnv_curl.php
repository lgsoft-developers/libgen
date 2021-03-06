<?php

function post_content ($url,$postdata) {
  $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

  $ch = curl_init( $url );
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_ENCODING, "");
  curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
  curl_setopt($ch, CURLOPT_TIMEOUT, 120);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
  curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE_FILE);
  curl_setopt($ch, CURLOPT_COOKIEFILE,COOKIE_FILE);

  $content = curl_exec( $ch );
  $err     = curl_errno( $ch );
  $errmsg  = curl_error( $ch );
  $header  = curl_getinfo( $ch );
  curl_close( $ch );

  $header['errno']   = $err;
  $header['errmsg']  = $errmsg; echo $errmsg;
  $header['content'] = $content;
  return $header;
}

function get_content( $url )
{
  $uagent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8";

  $ch = curl_init( $url );

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   // ���������� ���-��������
  curl_setopt($ch, CURLOPT_HEADER, 0);           // �� ���������� ���������
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);   // ��������� �� ����������
  curl_setopt($ch, CURLOPT_ENCODING, "");        // ������������ ��� ���������
  curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); // ������� ����������
  curl_setopt($ch, CURLOPT_TIMEOUT, 120);        // ������� ������
  curl_setopt($ch, CURLOPT_MAXREDIRS, 10);       // ��������������� ����� 10-��� ���������
  curl_setopt($ch, CURLOPT_COOKIEJAR, 'COOKIE_FILE');
  curl_setopt($ch, CURLOPT_COOKIEFILE,'COOKIE_FILE');
  
  $content = curl_exec( $ch );
  $err     = curl_errno( $ch );
  $errmsg  = curl_error( $ch );
  $header  = curl_getinfo( $ch );
  curl_close( $ch );

  $header['errno']   = $err;
  $header['errmsg']  = $errmsg;
  $header['content'] = $content;
  return $header;
}

?>