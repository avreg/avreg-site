<?php

/**
*
* @file head.inc.php
* @brief В файле реализовано формирование базовой функциональности и структуры страниц сайта
*
*<ul>
*<li> Формируется структура страницы
*<li> Добавляется базовое содержимое тега \<head\>
*<li> Инициализируются js-переменные
*<li> Определяются  js-функции
*<li> Добавляется тег \<body\> со стилями
*</ul>
*
* 
*/

ob_start();

require_once('lib/config.inc.php');

/**
 * Send http headers
 */
// Don't use cache (required for Opera)
$now = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: ' . $now);
header('Last-Modified: ' . $now);
header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0
// Define the charset to be used
header('Content-Type: text/html; charset=' . $chset);
if ( isset($ie6_quirks_mode) && $ie6_quirks_mode && preg_match('/MSIE\s*6/',$_SERVER['HTTP_USER_AGENT']) )
   print '<?xml version="1.0" encoding="'.$chset.'"?>'."\n";
if (strstr($_SERVER['SCRIPT_NAME'], 'gallery.php'))
    print '<!DOCTYPE HTML>';
else
    print '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'."\n";
print '<html><head>'."\n";
print '<link rel="SHORTCUT ICON" href="'.$conf['prefix'].'/favicon.ico">'."\n";
print '<title>';
if ( isset($GLOBALS['pageTitle']) )
   print($conf['server-name'] . '['.$named.']::'. $GLOBALS[$GLOBALS['pageTitle']]);
else
   print($conf['server-name'] . '['.$named.']');
print '</title>'."\n";
print '<meta http-equiv="Content-Type" content="text/html; charset='.$chset.'">'."\n";
print '<meta name="author" content="Andrey Nikitin &lt;nik-a at mail dot ru&gt;">'."\n";
if ( isset($BaseTarget) )
   print '<base target="'.$BaseTarget.'">'."\n";
print '<style type="text/css">'."\n";
// print '<!--'."\n";
readfile ($wwwdir.'/main.css');
// print '// -->'."\n";
print '</style>'."\n";
if ( isset($css_links) && is_array($css_links))
   foreach ($css_links as &$__css_link)
      print '<link href="'.$conf['prefix'].'/'.$__css_link.'" rel="stylesheet" type="text/css" />'."\n";
if ( isset($USE_JQUERY) ) {
   if ( $conf['debug'] )
      print '<script type="text/javascript" src="'.$conf['prefix'].'/lib/js/jquery-1.7.1.js"></script>'."\n";
   else{
      print '<script type="text/javascript" src="'.$conf['prefix'].'/lib/js/jquery-1.7.1.min.js"></script>'."\n";
   }
}
if ( isset($link_javascripts) && is_array($link_javascripts))
   foreach ($link_javascripts as &$__js_link)
      print '<script type="text/javascript" src="'.$conf['prefix'].'/'.$__js_link.'"></script>'."\n";
?>

<script type="text/javascript" >

<!--
<?php 
printf("var StorageDir = '%s';\n", addcslashes($conf['storage-dir'], '\'"/\\'));
printf("var WwwPrefix  = '%s';\n", addcslashes($conf['prefix'], '\'"/\\'));
printf("var MediaAlias = '%s';\n", addcslashes($conf['media-alias'], '\'"/\\'));
?>

var MSIE=false; // FIXME double calc with php
var GECKO=false;
var WEBKIT = false;

var UA = navigator.userAgent.toLowerCase();
if (UA.indexOf('msie') >=0 )
   MSIE=true;
if (UA.indexOf('webkit') >=0 ){
	WEBKIT=true;
}
else if (UA.indexOf('gecko') >=0 ){
   GECKO=true;
}

var tipobj=null;
var offsetxpoint=-60; //Customize x offset of tooltip
var offsetypoint=20; //Customize y offset of tooltip
var ie=document.all;
var ns6=document.getElementById && !document.all;
var enabletip=false;

var hint=null;

///очистка html-контента элемента 
function clear_innerHTML(obj) {
   var child;
   while (child = obj.firstChild)
      obj.removeChild(child);
   obj.innerHTML='';
}

function ietruebody(){
   return (document.compatMode &&
      document.compatMode!='BackCompat')? document.documentElement : document.body;
}

function ddrivetip(thetext, thecolor, thewidth) {
   if (ns6||ie) {
      if (thetext && typeof thetext!='undefined') {
         clear_innerHTML(tipobj);
         tipobj.innerHTML=thetext;
      } else if (hint != null) {
         clear_innerHTML(tipobj);
         tipobj.innerHTML=hint;
      } else {
         return false;
      }
      if (thewidth || typeof thewidth!='undefined') 
         tipobj.style.width=thewidth+'px';
      if (thecolor && typeof thecolor!='undefined' && thecolor!='')
         tipobj.style.backgroundColor=thecolor;
      enabletip=true;
      return false;
   }
}

function positiontip(e) {
   if (enabletip){
      var curX=(ns6)?e.pageX : event.clientX+ietruebody().scrollLeft;
      var curY=(ns6)?e.pageY : event.clientY+ietruebody().scrollTop;
      //Find out how close the mouse is to the corner of the window 
      var rightedge=ie&&!window.opera?
         ietruebody().clientWidth-event.clientX-offsetxpoint :
         window.innerWidth-e.clientX-offsetxpoint-20;
      var bottomedge=ie&&!window.opera?
         ietruebody().clientHeight-event.clientY-offsetypoint :
         window.innerHeight-e.clientY-offsetypoint-20;
      var leftedge=(offsetxpoint<0)? offsetxpoint*(-1) : -1000;

      //if the horizontal distance isn't enough to accomodate the width of the context menu
      if (rightedge<tipobj.offsetWidth)
         //move the horizontal position of the menu to the left by it's width
         tipobj.style.left=ie?
         ietruebody().scrollLeft+event.clientX-tipobj.offsetWidth+"px" :
         window.pageXOffset+e.clientX-tipobj.offsetWidth+"px";
      else if (curX<leftedge)
         tipobj.style.left="5px";
      else
         //position the horizontal position of the menu where the mouse is positioned
         tipobj.style.left=curX+offsetxpoint+"px"
         //same concept with the vertical position
         if (bottomedge<tipobj.offsetHeight)
            tipobj.style.top=ie?
            ietruebody().scrollTop+event.clientY-tipobj.offsetHeight-offsetypoint+"px" :
            window.pageYOffset+e.clientY-tipobj.offsetHeight-offsetypoint+"px";
         else
            tipobj.style.top=curY+offsetypoint+"px";
      tipobj.style.visibility="visible";
   }
}

function hideddrivetip() {
   if (ns6||ie){
      enabletip=false;
      tipobj.style.visibility="hidden";
      tipobj.style.left="-1000px";
      tipobj.style.backgroundColor='';
      tipobj.style.width='';
   }
}

function MakeArray(n) {
   this.length = n;
   return this;
}
monthNames = new MakeArray(12);
monthNames[1] = "Янв";
monthNames[2] = "Фев";
monthNames[3] = "Мар";
monthNames[4] = "Апр";
monthNames[5] = "Мая";
monthNames[6] = "Июн";
monthNames[7] = "Июл";
monthNames[8] = "Авг";
monthNames[9] = "Сен";
monthNames[10] = "Окт";
monthNames[11] = "Ноя";
monthNames[12] = "Дек";
dayNames = new MakeArray(7);
dayNames[0] = "Вск";
dayNames[1] = "Пнд";
dayNames[2] = "Втр";
dayNames[3] = "Срд";
dayNames[4] = "Чтв";
dayNames[5] = "Птн";
dayNames[6] = "Сбт";


function SetCookie(cookieName,cookieValue,nDays, Path)
{
   var today = new Date();
   var expire = new Date();
   if (nDays==null || nDays==0) nDays=1;
   expire.setTime(today.getTime() + 3600000*24*nDays);
	var dpath = (Path != null && Path != '') ? ';path='+Path  : '';
   document.cookie = cookieName + '=' + escape(cookieValue) + ";expires="+expire.toGMTString()+dpath;

	if(MSIE){
	   	document.cookie=cookieName + '=' + escape(cookieValue) + ";expires="+expire.toGMTString();
	}
	
}

function ReadCookie(cookieName)
{
   if ( ! (cookieName && typeof cookieName!='undefined') )
      return "";
   var theCookie=""+document.cookie;
   var ind=theCookie.indexOf(cookieName);
   if (ind==-1) ; 
   var ind1=theCookie.indexOf(';',ind);
   if (ind1==-1) ind1=theCookie.length; 
   return unescape(theCookie.substring(ind+cookieName.length+1,ind1));
}

<?php
if ( isset($include_javascripts) && is_array($include_javascripts)) {
   foreach ($include_javascripts as &$__js){
      if (eregi('.php$', $__js))
         require ($wwwdir.$__js);
      else
         readfile($wwwdir.$__js);
   }
}
?>
// -->
</script>
</head>

<?php


if (!isset($NOBODY))
{
   if ( isset($MENU) )
   {
      $pageBgColor = $left_bgcolor;
   } else {
      if (!isset($pageBgColor)) $pageBgColor = $ContentColor;
      // print_syslog(LOG_NOTICE, 'access: '.basename($_SERVER['SCRIPT_FILENAME']));
   }
   if (!isset($body_style))
      $body_style = '';
   if ( isset($pageBgColor) && !empty($pageBgColor) )
      $body_style .= 'background-color: '.$pageBgColor.';';
   else
      $body_style .= 'visibility:visible;';

   if ( isset($body_onload) && !empty($body_onload) )
      $_onload = 'onload="JavaScript:'.$body_onload.'"';
   else  if ( isset($load) && !empty($load) )
      $_onload = 'onload="JavaScript:window.open(\''.$load.'\', target=\'content\')"';
   else
      $_onload = '';

   if ( !isset($body_addons))
      $body_addons = '';

/*
    if ( isset($body_onunload) && !empty($body_onunload) )
      $_onload = 'onunload="JavaScript:'.$body_onunload.'"';
 */ 
   print sprintf('<body style="%s" %s %s>',$body_style, $_onload, $body_addons)."\n";
   $custom_header = preg_replace('%^'.$conf['prefix'].'(/.+)\.php$%', '\1_header.inc.php', $_SERVER['SCRIPT_NAME']);
   if ( 0 != strcmp($_SERVER['SCRIPT_NAME'], $custom_header ) ) {
      if ($conf['debug'])
         print '<div class="legend header"><span class="legend">@include "'. $conf['customize-dir'] . $custom_header . "\"</span>\n";
      #tohtml($_SERVER['SCRIPT_NAME']);
      @include($conf['customize-dir'] . $custom_header);
      if ($conf['debug'])
         print "</div>\n";
   }
}

?>
<noscript>
<center><div class="help" style="width:50%;text-align:left;">
Ваш браузер не использует JavaScript.<br>
Включите поддержку JavaScript и перегрузите/обновите эту страницу.</div></center>
</noscript>
<div id="tooltip"></div>

<?php
  // чтобы из включений customize/PAGE_header.inc.php можно было другой http-ответ послать, например redirect
if ( empty($NO_OB_END_FLUSH) )
   while (@ob_end_flush());
?>
