<?php
/**
 * @file admin/_index2.php
 * @brief Стартовый iframe настроек в админки
 */
require ('../head.inc.php');
require('warn.inc.php');
echo '<h1>' . $r_menu . '</h1>' ."\n";

print '<table cellspacing="0" border="1" cellpadding="10" align="center">'."\n";
print '<tr bgcolor="'.$header_color.'">'."\n";
print '<th>'.$strName.'</th>'."\n";
print '<th>'.$strDescription.'</th>'."\n";
print '</tr>'."\n";
print '<tr>'."\n";
print '<td>'. $left_logo.'</td>'."\n";
print '<td>'.$left_logo_desc.'</td>'."\n";
print '</tr>'."\n";
if ($LDVR_VER) {
print '<tr>'."\n";
print '<td>'. $left_system.'</td>'."\n";
print '<td>'.$left_system_desc.'</td>'."\n";
print '</tr>'."\n";
print '<tr>'."\n";
print '<td>'. $left_archive.'</td>'."\n";
print '<td>'.$left_archive_desc.'</td>'."\n";
print '</tr>'."\n";
}
print '<tr>'."\n";
print '<td>'. $left_users.'</td>'."\n";
print '<td>'.$left_users_desc.'</td>'."\n";
print '</tr>'."\n";
print '<tr>'."\n";
print '<td>'. $left_tune.'</td>'."\n";
print '<td>'.$left_tune_desc.'</td>'."\n";
print '</tr>'."\n";
print '<tr>'."\n";
print '<td>'. $left_layouts.'</td>'."\n";
print '<td>'.$left_monitors_desc.'</td>'."\n";
print '</tr>'."\n";
print '</table>'."\n";


require ('../foot.inc.php');
?>
