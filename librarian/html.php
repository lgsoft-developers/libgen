<?php
$htmlheadbegin = "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html><head><title>Library Genesis</title>
<META HTTP-EQUIV='CACHE-CONTROL' CONTENT='NO-CACHE'>
<meta http-equiv='content-type' content='text/html; charset=utf-8'>
";
$htmlheadend = "</head><body>";
$script = "<script type='text/javascript'>
function f() {document.getElementById('1').focus();}
window.onload = f;
</script>";

$htmlhead = $htmlheadbegin.$htmlheadend;
$htmlheadfocus = $htmlheadbegin.$script.$htmlheadend;
$htmlfoot = "</body></html>";
?>
