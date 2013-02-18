<?php


/**
 * @file admin/index.php 
 * @brief Стартовая страница модуля 
 * 
 * 
 * @page admin Модуль Админки
 * Админка проекта 
 *  
 * Подмодули	:
 * - @ref tune
 *
 * Файлы модуля:
 * - admin/index.php	
 * - admin/menu.php
 * - admin/_index1.php
 * - admin/control.php
 * - admin/stats.php
 * - admin/key.php
 * - admin/update.php
 * - admin/bugreport.php
 *  
 *  
 */

/// показывать body елемент
$NOBODY=1;
/// Тайтл страницы в файле переводов
$pageTitle = 'left_logo';
require('../head.inc.php');
DENY($admin_status);

print '<frameset border=1 framespacing=0 cols="200,*" rows="*">'."\n";
print '<frame src="'.$conf['prefix'].'/admin/menu.php?'.urlencode($_SERVER['QUERY_STRING']).'" align=right noresize marginheight=5 marginwidth=5 scrolling="auto" name="menu">'."\n";
print '<frame src="'.$conf['prefix'].'/admin/_index1.php?'.urlencode($_SERVER['QUERY_STRING']).'" marginheight=5 marginwidth=5 scrolling="auto" name="content">'."\n";
print '</frameset>'."\n";
require('../foot.inc.php');
?>
