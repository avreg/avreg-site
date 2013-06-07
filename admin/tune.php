<?php
/**
 * @file admin/tune.php
 * @brief Настройки параметров видеорегистратора
 *
 * @page tune Настройки
 * Настройки параметров видеорегистратора
 *
 * Файлы:
 * - admin/menu-tune.php
 * - admin/_index2.php
 * - admin/user-list.php
 * - admin/user-addnew.php
 * - admin/user-tune.php
 * - admin/user-passwd.php
 * - admin/cam-tune.php
 * - admin/cam-list.php
 * - admin/cam-addnew.php
 * - admin/mon-list.php
 * - admin/mon-addnew.php
 * - admin/mon-tune.php
 * - admin/mon-type.inc.php
 * - admin/active_pipe.inc.php
 * - admin/web_mon_list.php
 * - admin/web_mon_addnew.php
 * - admin/web_mon_tune.php
 * - admin/web_set_def.php
 * - admin/web_active_pipe.inc.php
 *
 * @ref admin
 */
$NOBODY = 1;
$pageTitle = 'left_indextune';
require('../head.inc.php');
print '<frameset border=1 framespacing=0 cols="200,*" rows="*">' . "\n";
print '<frame src="' . $conf['prefix'] . '/admin/menu-tune.php?' . urlencode(
		$_SERVER['QUERY_STRING']
	) . '" align=right noresize marginheight=5 marginwidth=5 scrolling="auto" name="menu">' . "\n";
print '<frame src="' . $conf['prefix'] . '/admin/_index2.php?' . urlencode(
		$_SERVER['QUERY_STRING']
	) . '" marginheight=5 marginwidth=5 scrolling="auto" name="content">' . "\n";
print '</frameset>' . "\n";
require('../foot.inc.php');
?>
