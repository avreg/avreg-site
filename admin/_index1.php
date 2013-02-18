<?php
/**
 * @file admin/_index1.php
 * @brief Стартовый iframe админки
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
print '<td>'. $left_control.'</td>'."\n";
print '<td>'.$left_control_desc.'</td>'."\n";
print '</tr>'."\n";
print '<tr>'."\n";
print '<td>'. $left_statistics.'</td>'."\n";
print '<td>'.$left_statistics_desc.'</td>'."\n";
print '</tr>'."\n";
/*
print '<tr>'."\n";
print '<td>'. $left_utils.'</td>'."\n";
print '<td>'.$left_utils_desc.'</td>'."\n";
print '</tr>'."\n";
*/
print '<tr>'."\n";
print '<td>'. $tune_logo.'</td>'."\n";
print '<td>'.$tune_logo_desc.'</td>'."\n";
print '</tr>'."\n";
print '<tr>'."\n";
print '<td>'. $left_key.'</td>'."\n";
print '<td>'.$left_key_desc.'</td>'."\n";
print '</tr>'."\n";
if ($LDVR_VER) {
print '<tr>'."\n";
print '<td>'. $left_update.'</td>'."\n";
print '<td>'.$left_update_desc.'</td>'."\n";
print '<tr>'."\n";
print '<td>'. $left_bug.'</td>'."\n";
print '<td>'.$left_bug_desc.'</td>'."\n";
print '</tr>'."\n";
}
print '</table>'."\n";


require ('../foot.inc.php');
?>
