<?php
/**
 * 
 * @file offline/view-image.php
 * @brief Подробный просмотр события
 */

$include_javascripts = array('offline/view.js');
$USE_JQUERY = true;
$ie6_quirks_mode = true;
$body_style='margin: 0px 0px 0px 0px; padding: 0px 0px 0px 0px; ';
$lang_file = '_offline.php';
require ('../head.inc.php');
DENY($arch_status);
?>

<script type="text/javascript" language="JavaScript1.2">
<!--
if (ie||ns6)
  tipobj=document.all? 
     document.all['tooltip'] :
     document.getElementById? document.getElementById('tooltip') : '';

document.onmousemove=positiontip;

<?php
/* вычисляем протокол доступа к медиа-файлам 
   на основании адреса клиента и правил в конфиге */
require_once($wwwdir . 'lib/utils-inet.php');
$remote_ip = ip2long($_SERVER['REMOTE_ADDR']);
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);

if ( $MSIE /* only win */ || false !== strpos($ua, 'windows') )
   $platform = 'win';
else if ( false !== strpos($ua, 'linux') ||
          false !== strpos($ua, 'solaris') ||
          false !== strpos($ua, 'bsd') ||
          false !== strpos($ua, 'mac os x') )
  $platform = 'nix';
else
  $platform = 'other';

$_found = false;
$_ipacl_list = array_keys($conf["murl-pre-$platform"]);
foreach ( $_ipacl_list as &$_ipacl_str ) {
  $_ipacl = avreg_inet_network($_ipacl_str);
  if ( $_ipacl === false ) {
      /* неправильно задан IP ACL */
     printf("alert(\"invalid IP ACL [%s] for murl-pre-$platform param (see avreg.conf)\");\n", addcslashes($_ipacl_str, '\'"/\\'));
     break;
  }
  if ( true === avreg_ipv4_cmp( $remote_ip, -1, $_ipacl['addr'],  $_ipacl['mask']) ) {
     printf('var MediaUrlPref = \'%s/\';'."\n",
          addcslashes($conf["murl-pre-$platform"][$_ipacl_str], '\'"/\\'));
     $_found = true;
     break;
   }
}

if ( !$_found /* не нашли совпадения по ACL */ )
  print 'var MediaUrlPref = location.protocol + \'\/\/\' + location.host + WwwPrefix + MediaAlias + \'\/\';'."\n";
?>
// -->
</script>

<div id="content" style="position:absolute;width:100%;height:100%;"
 onmouseover="ddrivetip();" onmouseout="hideddrivetip();">

<?php
if (!isset ($src) || empty($src)) {
  print "<div style=\"padding: 50px 30px 5px 30px; font-size: 110%;\">$strViewFrame1</div><div style=\"padding: 5px 30px 30px 30px;  font-size: 110%;\">$strViewFrame2</div>\n";
} 
print  '</div>'."\n";

require ('../foot.inc.php');
?>
