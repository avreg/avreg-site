<?php
/**
 * @file
 * @brief Меню админки
 */
/// Заголовок страницы в файле переводов
$PgTitle = 'left_tune';
/// Меню используеться	
$MENU = 1;
require('../head.inc.php');
print '<div align="center"><a href="' . $conf['prefix'] . '/" target="_parent">' . $MainPage . '</a></div>' . "\n";
//print '<div align="center"><h2>'.$left_logo.'</h2></div>'."\n";
print "<br /><div>$strYou: <a href='../index.php?logout=1' target='_top' title='$strLogout'>$login_user@$remote_addr</a></div>\n";
print '<hr noshade>' . "\n";
/*
print '<h3>+&nbsp;<a href="/logout.php" target="content">logout</a></h3>'."\n";
*/
if ($admin_user) {
	// $the_a = sprintf ('http://%s/admin/control.php?sip=%s&named=%s', urlencode($sip), urlencode($sip), urlencode ($named));
	print '<h3 class="menu0">+&nbsp;<a href="' . $conf['prefix'] . '/admin/control.php" target="content" title="' . $left_control_title . '">' . $left_control . '</a></h3>' . "\n";
}

// $the_a = sprintf ('http://%s/admin/stats.php?sip=%s&named=%s', urlencode($sip), urlencode($sip), urlencode ($named));
print '<h3 class="menu0">+&nbsp;<a href="' . $conf['prefix'] . '/admin/stats.php" target="content" title="' . $left_statistics_title . '">' . $left_statistics . '</a></h3>' . "\n";
// print '<h3>+&nbsp;'.$left_utils.'</h3>'."\n";
// $the_a = sprintf (''.$conf['prefix'].'/admin/tune.php?sip=%s&named=%s', urlencode($sip), urlencode ($named));
print '<h3 class="menu0">&nbsp;&nbsp;<a href="' . $conf['prefix'] . '/admin/tune.php" target="_parent" title="' . $tune_title . '">' . $left_indextune . '&nbsp;&nbsp;&gt;&gt;</a></h3>' . "\n";

print '<br>' . "\n";
print '<div class="menu0">-&nbsp;<a href="' . $conf['prefix'] . '/admin/key.php"  target="content" title="' . $license . '">' . $license . '</a></div>' . "\n";
print '<br><br><br><br><hr noshade>' . "\n";
require('menu-bottom.inc.php');
require('../foot.inc.php');
?>
