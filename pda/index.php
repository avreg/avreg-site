<?php

/**
 * @file pda/index.php
 * @brief Страница PDA-версии
 *
 *
 * @page pda Модуль PDA-версии
 * Модуль PDA-версии
 *
 * Файлы модуля:
 * - pda/index.php
 * - pda/snaplist.php
 * - pda/head_pda.inc.php
 * - pda/offline.php
 * - pda/online-noresized.php
 * - pda/online.php
 * - pda/paginator.inc.php
 */

/**
 * @page pda Модуль PDA-версии
 * Модуль просмотра записанного материала на pda-устройствах
 */

# $pageTitle = '';
//$lang_file = '_admin_cams.php';
require('head_pda.inc.php');
session_write_close();
require('../lib/cams_main_detail.inc.php');
require('../lib/get_cams_params.inc.php');

$__cams_params = get_cams_params(array(
    'work',
    'video_src',
    'allow_networks',
    'allow_pda',
    'text_left',
    'geometry',
    'Hx2'));
$cams_params = array();
$cams_nbr = 0;
foreach ($__cams_params as $__cam_nr => $__cam_opt) {
    if ($__cam_nr >= 0) {
        if (empty($__cam_opt['work']['v']) ||
            empty($__cam_opt['video_src']['v']) ||
            empty($__cam_opt['allow_networks']['v']) ||
            empty($__cam_opt['allow_pda']['v'])) {
            continue;
        }
        $cams_nbr++;
    }
    $cams_params[$__cam_nr] = $__cam_opt;
}
unset($__cams_params);
if ($cams_nbr <= 0) {
    die('There are no available cameras!');
}

if (isset($_SESSION['cams'])) {
    $cams =& $_SESSION['cams'];
}
// tohtml($cams);
print "<form action='offline.php' method='GET'>\n";
/* Printing results in HTML */
print "<table cellpadding='2' border='0' cellspacing='0'>\n";
print '<tr bgcolor="' . $header_color . '">' . "\n";
printf(
    '<th><input type="checkbox" id="all_cams" value="1" %s title="Выделить/снять все" ' .
    'onchange="sel_desel(this);"></th>' . "\n",
    (!isset($cams) || count($cams) === $cams_nbr) ? 'checked' : ''
);
print '<th nowrap>&nbsp;№&nbsp;</th>' . "\n";
print '<th>' . $strCam . '</th>' . "\n";
print '</tr>' . "\n";

$show_colums = array(
    'ICONS' => false,
    'CAM_NR' => true,
    'NAME' => array('href' => 'online.php', 'title' => 'Наблюдать камеру'),
    'SRC' => false,
    'RESOLUTION' => false,
);

/* TODO если быть точным то стоит разделить камеры:
 *   allow_networks && allow_pda - ссылка на online,
 *   allow_pda без allow_networks - только архив
 */
foreach ($cams_params as $__cam_nr => $cam_detail) {
    if ($__cam_nr <= 0) {
        continue;
    }
    print "<tr>\n";
    $checked = (!isset($cams) || in_array($__cam_nr, $cams)) ? 'checked' : '';
    print "<td><input type='checkbox' name='cams[]' value='$__cam_nr' $checked></td>\n";
    print_cam_detail_row($conf, $__cam_nr, $cam_detail, $show_colums);
    print "</tr>\n";
}
print "</table>\n";

$tm_timestamp = isset($_SESSION['timestamp']) ? localtime($_SESSION['timestamp']) : localtime();
$minute = 0;
foreach ($minute_array as &$value) {
    if ((int)$value >= $tm_timestamp[1]) {
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
print "<tr><th>&nbsp;</th><th>$strYear</th><th>$strMonth</th><th>$strDay</th><th>$strHour</th>
    <th>$strMinute</th></tr>\n";
print "</thead>\n";
print "<tbody>\n";
print "<tr valign='bottom'><td align='right'>$strFrom</td>";
print "<td>" . getSelectHtml('year', $year_array, false, 1, 0, $tm_timestamp[5] - 100, false, false) . "</td>\n";
print "<td>" . getSelectHtml(
    'month',
    $month_array,
    false,
    1,
    1,
    $month_array[$tm_timestamp[4]],
    false,
    false
) . "</td>\n";
print "<td>" . getSelectHtml('day', $day_array, false, 1, 1, $tm_timestamp[3], false, false) . "</td>\n";
print "<td>" . getSelectHtml('hour', $hour_array, false, 1, 0, $tm_timestamp[2], false, false) . "</td>\n";
print "<td>" . getSelectHtml('minute', $minute_array, false, 1, 0, $minite, false, false) . "</td>\n";
print "</tbody>\n";
print "</table>\n";
print "<table cellpadding='2' border='0' cellspacing='0'>\n";
print "<tr valign='bottom'><td>интервал</td><td>";
$until = isset($_SESSION['until']) ? (int)$_SESSION['until'] : -30;
print getSelectByAssocAr('until', $until_minutes_a, false, 1, 0, $until, false, false);
print "</td><td align='right'>минут</td></tr></table>\n";
printf(
    "<div><input type='checkbox' name='desc' value='1' %s>$strDescOrder</div>\n",
    (isset($_SESSION['desc']) && !$_SESSION['desc']) ? '' : 'checked'
);
printf(
    "<div><input type='checkbox' name='oims' value='1' %s>только &quot;внутри&quot; сеанса движения</div>\n",
    empty($_SESSION['oims']) ? '' : 'checked'
); // only into motion session
print "<div><input type='submit' id='btSubmit' value='Смотреть архив'></div>\n";
print "</form>\n";
// phpinfo();
?>

<script type="text/javascript">
var cams_chkboxes = null;
function sel_desel(elem) {
    var checked = elem.checked;
    if (cams_chkboxes == null) {
        cams_chkboxes = document.getElementsByName('cams[]');
    }
    for (var i = 0, len = cams_chkboxes.length; i < len; i++)
        cams_chkboxes[i].checked = checked;
    return true;
}
</script>

<?php

require('../foot.inc.php');
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
