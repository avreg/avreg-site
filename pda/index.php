<?php
# $pageTitle = '';
//$lang_file = '_admin_cams.php';
require ('head_pda.inc.php');
session_write_close();
require ('../lib/my_conn.inc.php');
require('../lib/cams_main_detail.inc.php');

$GCP_query_param_list=array('work', 'allow_networks', 'text_left', 'geometry', 'Hx2');
require('../lib/get_cams_params.inc.php');
if ( $GCP_cams_nr == 0 )
   die('There are no available cameras!');

if (isset($_SESSION['cams']))
   $cams =& $_SESSION['cams'];
// tohtml($cams);
print "<form action='offline.php' method='GET'>\n";
/* Printing results in HTML */
print "<table cellpadding='2' border='0' cellspacing='0'>\n";
print '<tr bgcolor="'.$header_color.'">'."\n";
printf('<th><input type="checkbox" id="all_cams" value="1" %s title="Выделить/снять все" onchange="sel_desel(this);"></th>'."\n",
       ( !isset($cams) || count($cams) === $GCP_cams_nr ) ? 'checked' : '' );
print '<th nowrap>&nbsp;№&nbsp;</th>'."\n";
print '<th>'.$strCam.'</th>'."\n";
print '</tr>'."\n";

$show_colums = array(
   'ICONS'    => false,
   'CAM_NR'   => true,
   'NAME'     => array('href' => 'online.php', 'title' => 'View online'),
   'SRC'      => false,
   'CAPS'     => false,
);
reset($GCP_cams_params);
while (list($__cam_nr, $cam_detail) = each($GCP_cams_params)) 
{
   print "<tr>\n";
   $checked = (!isset($cams) || in_array($__cam_nr, $cams)) ? 'checked' : '';
   print "<td><input type='checkbox' name='cams[]' value='$__cam_nr' $checked></td>\n";
   print_cam_detail_row(&$conf, $__cam_nr, &$cam_detail, &$show_colums);
   print "</tr>\n";
}
print "</table>\n";

$tm_timestamp = isset($_SESSION['timestamp']) ? localtime($_SESSION['timestamp']) : localtime();
$minute = 0;
foreach ($minute_array as &$value) {
   if ( (int)$value >= $tm_timestamp[1] ) {
      $minite = $value;
      break;
   }
}

$until_minutes_a = array(
   -60 => '-60',
   -45 => '-45',
   -30 => '-30',
   -15 => '-15',
    15 => '+15',
    30 => '+30',
    45 => '+45',
    60 => '+60',
);


print "<fieldset><legend>Поиск по архиву</legend>\n";
print "<table cellpadding='2' border='0' cellspacing='0'>\n";
print "<thead>\n";
print "<tr><th>&nbsp;</th><th>$strYear</th><th>$strMonth</th><th>$strDay</th><th>$strHour</th><th>$strMinute</th></tr>\n";
print "</thead>\n";
print "<tbody>\n";
print "<tr valign='bottom'><td align='right'>$strFrom</td>";
print "<td>" . getSelectHtml('year',   $year_array,   FALSE, 1, 0, $tm_timestamp[5]-100, FALSE, FALSE) . "</td>\n";
print "<td>" . getSelectHtml('month',  $month_array,  FALSE, 1, 1, $month_array[$tm_timestamp[4]], FALSE, FALSE) . "</td>\n";
print "<td>" . getSelectHtml('day',    $day_array,    FALSE, 1, 1, $tm_timestamp[3], FALSE, FALSE) . "</td>\n";
print "<td>" . getSelectHtml('hour',   $hour_array,   FALSE, 1, 0, $tm_timestamp[2], FALSE, FALSE) . "</td>\n";
print "<td>" . getSelectHtml('minute', $minute_array, FALSE, 1, 0, $minite, FALSE, FALSE) . "</td>\n";
print "</tbody>\n";
print "</table>\n";
print "<table cellpadding='2' border='0' cellspacing='0'>\n";
print "<tr valign='bottom'><td>интервал</td><td>";
$until = isset($_SESSION['until']) ? (int)$_SESSION['until'] : -30;
print getSelectByAssocAr('until', $until_minutes_a, FALSE, 1, 0, $until, FALSE, FALSE);
print "</td><td align='right'>минут</td></tr></table>\n";
printf("<div><input type='checkbox' name='desc' value='1' %s>$strDescOrder</div>\n",
   (isset($_SESSION['desc']) && !$_SESSION['desc']) ? '' : 'checked');
printf("<div><input type='checkbox' name='oims' value='1' %s>только &quot;внутри&quot; сеанса движения</div>\n",
   empty($_SESSION['oims']) ? '' : 'checked'); // only into motion session
print "<div><input type='submit' id='btSubmit' value='Смотреть архив'></div>\n";
print "</form>\n";
// phpinfo();
?>

<script type="text/javascript">
var cams_chkboxes = null;
function sel_desel(elem)
{
   var checked = elem.checked;
   if ( cams_chkboxes == null )
      cams_chkboxes = document.getElementsByName('cams[]');
   for ( var i = 0, len = cams_chkboxes.length; i < len; i++ )
      cams_chkboxes[i].checked = checked;
   return true;
}
</script>

<?php

require ('../lib/my_close.inc.php');
require ('../foot.inc.php');
?>
