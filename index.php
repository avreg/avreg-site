<?php

/**
 *
 * @file index.php
 * @brief генерация стартовой страницы сайта Avreg
 * 
 * @mainpage AvregSite 
 * 
 * Веб-интерфейс
 * 
 * Состоит из следующих частей:
 * - @ref lib 
 * - @ref admin 
 * - @ref lang 
 * - @ref offline 
 * - @ref online 
 * - @ref pda 
 * 
 */
session_start();
if (isset($_SESSION['is_admin_mode']))
    unset($_SESSION['is_admin_mode']);
$NO_OB_END_FLUSH = true;

$USE_JQUERY = true;

$link_javascripts=array(
 		'index.js',
		'lib/js/user_layouts.js',
		'lib/js/json2.js'
);


require ('./head.inc.php');
while (@ob_end_flush());


print '<table id="main_tab" width="860" height="500"  cellspacing="20" border="0" cellpadding="0" align="center">'."\n";
print '<tbody>'."\n";
print '<tr>'."\n";
//pda
print '<td align="center" valign="top" nowrap class="main_td_ref" >'."\n";
if ($allow_pda) {
	print  '<div style="position:relative;" ><div class="mode_item" style="position:absolute; " >'."\n";
	print '<a class="main_links" href="./pda/"><img   src="./img/pda.gif" border="0px" /></a>'."\n";
	print '<p><a class="main_links" href="./pda/">PDA-версия</a></p>'."\n";
	print  '</div ></div >'."\n";
}
print '</td>'."\n";

//online
print '<td align="center" valign="top" class="main_td_ref" >'."\n";
print  '<div style="position:relative;" ><div class="mode_item" style="position:absolute; " >'."\n";
print '<a class="main_links" onclick="online(\'./online/index.php\');" href="#" title="'.$a_webcam.'"><img
src="./img/online.jpg" width="251" height="165" border="0"></a>'."\n";
print '<p><a class="main_links" onclick="online(\''.'./online/index.php\');" href="#">'.$a_webcam.'</a></p>'."\n";
print  '</div ></div >'."\n";
print '</td>'."\n";
//админка
print '<td align="center" valign="top" class="main_td_ref" >'."\n";
if ( $admin_user /* config.inc.php */ ) {
   $href3=sprintf($conf['prefix'].'/admin/index.php?sip=%s&amp;named=%s',$sip,$named);
   print  '<div style="position:relative;" ><div class="mode_item" style="position:absolute; " >'."\n";
   print '<a class="main_links" href="'.$href3.'" title="'.$a_adminv.'"><img   src="./img/admin.jpg" width="251" height="165" border="0"></a>'."\n";
   print '<p align="center"><a class="main_links" href="'.$href3.'">'.$a_adminv.'</a></p>'."\n";
   print  '</div ></div >'."\n";
} else {
   print "&nbsp;\n";
}
print '</td>'."\n";


print '</tr>'."\n";

if ( $arch_user ) {
   print '<tr>'."\n";
   //Архив :: поиск
   print '<td align="center" valign="top" class="main_td_ref" >'."\n";
   print  '<div style="position:relative;" ><div class="mode_item" style="position:absolute; " >'."\n";
   print '<a class="main_links" href="./offline/index.php" title="'.$a_archive.'"><img   src="./img/offline.jpg" width="251" height="165" border="0"></a>'."\n";
   print '<p><a class="main_links" href="./offline/index.php">'.$a_archive.'</a></p>'."\n";
   print  '</div ></div >'."\n";
   print '</td>'."\n";
   
   //Архив :: плейлист
   print '<td align="center" valign="top" class="main_td_ref" >'."\n";
   print  '<div style="position:relative;" ><div class="mode_item" style="position:absolute; " >'."\n";
   print '<a class="main_links" href="./offline/playlist.php"><img   src="./img/offline_playlist.jpg" width="251" height="165" border="0"></a>'."\n";
   print '<p align="center"><a class="main_links" href="./offline/playlist.php">'.$a_archive_playlist.'</a></p>'."\n";
   print  '</div ></div >'."\n";
   print '</td>'."\n";
   
   //Gallery
   print '<td align="center" valign="top" class="main_td_ref" >'."\n";
   print  '<div style="position:relative;" ><div class="mode_item" style="position:absolute; " >'."\n";
   print '<a class="main_links" href="./offline/gallery.php"><img src="./img/offline_gallery.png" width="251" height="165" border="0"></a>'."\n";
   print '<p align="center"><a class="main_links" href="./offline/gallery.php">'.$a_archive_gallery.'</a></p>'."\n";
   print  '</div ></div >'."\n";
    
   print '</td>'."\n";
    
   
   print '</tr>'."\n";
}
print '</tbody>'."\n";
print '</table>'."\n";

if ( $conf['debug'] ) {
   tohtml($conf);
   // phpinfo(INFO_VARIABLES);
}

require ($wwwdir.'/foot.inc.php');
?>
