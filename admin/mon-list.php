<?php
/**
 * @file admin/mon-list.php
 * @brief Список определённых раскладок.
 */
$lang_file = '_online.php';
require('../head.inc.php');
require('./mon-type.inc.php');

echo '<h1>' . sprintf($r_mons, $named, $sip) . '</h1>' . "\n";

if (isset($cmd)) {
    DENY($admin_status);
    switch ($cmd) {
        case 'DEL':
            echo '<p class="HiLiteBigWarn">' . sprintf(
                $fmtDeleteMonConfirm,
                $mon_nr,
                $mon_name,
                $display == 'R' ? $sRightDisplay1 : $sLeftDisplay1
            ) . '</p>' . "\n";
            print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
            print '<input type="hidden" name="cmd" value="DEL_OK">' . "\n";
            print '<input type="hidden" name="display" value="' . $display . '">' . "\n";
            print '<input type="hidden" name="mon_nr" value="' . $mon_nr . '">' . "\n";
            print '<input type="hidden" name="mon_name" value="' . $mon_name . '">' . "\n";
            print '<input type="submit" name="mult_btn" value="' . $strYes . '">' . "\n";
            print '<input type="submit" name="mult_btn" value="' . $strNo . '">' . "\n";
            print '</form>' . "\n";
            require('../foot.inc.php');
            exit;
            break; /**/
        case 'DEL_OK':
            if (($mult_btn == $strYes) && isset($mon_nr)) {

                $adb->deleteLayouts($display, $mon_nr);

                echo '<p><font color="' . $warn_color . '">' . sprintf(
                    $strDeleteMon,
                    $mon_nr,
                    $mon_name,
                    $display == 'R' ? $sRightDisplay1 : $sLeftDisplay1
                ) . '</font></p>' . "\n";
            }
            unset($mon_nr);
            break;
    }
}

echo '<h2>' . $r_mon_list . '</h2>' . "\n";
/**
 *
 * Функция выводит информацию о раскладке монитора
 * @param string $display правый или левый монитор
 * @param int $l_nr Номер монитора
 * @param array $l_def Настройки монитора
 * @param bool $is_admin Админ
 */
function prt_l($display, $l_nr, $l_def, $is_admin)
{
    print "<td>\n";
    if (!empty($l_def['layout_name'])) {
        print ' &#171;' . $l_def['layout_name'] . "&#187;\n";
    } else {
        print ' &#171;' . $l_def['layout_type'] . "&#187;\n";
    }
    if (!empty($l_def['CHANGE_TIME'])) {
        print '<br>' . $l_def['CHANGE_USER'] . '@' . $l_def['CHANGE_HOST'] . '<br>' . $l_def['CHANGE_TIME'];
    }
    if ($is_admin) {
        print '<br><br><a href="' . $_SERVER['PHP_SELF'] . '?cmd=DEL&display=' . $display . '&mon_nr=' . $l_nr
            . '&mon_name=' . $l_def['layout_name'] . '">' . $GLOBALS['strDelete'] . '</a>&nbsp;/&nbsp;<a href="'
            . $GLOBALS['conf']['prefix'] . '/admin/mon-tune.php?display=' . $display . '&mon_nr=' . $l_nr
            . '&mon_name=' . $l_def['layout_name'] . '&mon_type=' . $l_def['layout_type'] . '">'
            . $GLOBALS['strEdit'] . '</a>' . "\n";
    }
    print '</td>' . "\n";
}

if (!isset($mon_nr) || $mon_nr == '') {
    /* Performing new SQL query */

    $result = $adb->getLayouts();

    $LD = array();
    $RD = array();
    foreach ($result as $row) {
        if ($row['DISPLAY'] == 'R') {
            $D = & $RD;
        } else {
            $D = & $LD;
        }
        $D[(int)$row['MON_NR']] = array(
            'layout_type' => $row['MON_TYPE'],
            'layout_name' => $row['MON_NAME'],
            'CHANGE_TIME' => $row['CHANGE_TIME'],
            'CHANGE_USER' => $row['CHANGE_USER'],
            'CHANGE_HOST' => $row['CHANGE_HOST'],
            'wins' => array(
                $row['WIN1'],
                $row['WIN2'],
                $row['WIN3'],
                $row['WIN4'],
                $row['WIN5'],
                $row['WIN6'],
                $row['WIN7'],
                $row['WIN8'],
                $row['WIN9'],
                $row['WIN10'],
                $row['WIN11'],
                $row['WIN12'],
                $row['WIN13'],
                $row['WIN14'],
                $row['WIN15'],
                $row['WIN16'],
                $row['WIN17'],
                $row['WIN18'],
                $row['WIN19'],
                $row['WIN20'],
                $row['WIN21'],
                $row['WIN22'],
                $row['WIN23'],
                $row['WIN24'],
                $row['WIN25'],
                $row['WIN26'],
                $row['WIN27'],
                $row['WIN28'],
                $row['WIN29'],
                $row['WIN20'],
                $row['WIN31'],
                $row['WIN32'],
                $row['WIN33'],
                $row['WIN34'],
                $row['WIN35'],
                $row['WIN36'],
                $row['WIN37'],
                $row['WIN38'],
                $row['WIN39'],
                $row['WIN40'],
            )
        );
    }

    print $tabletag . "\n";
    print '<tr bgcolor="' . $header_color . '">' . "\n";
    print '<th>' . $strName . '<br>' . $strUpdateControl . '</th>' . "\n";
    print '<th>' . $strCamPosition . '</th>' . "\n";
    print '<th rowspan="2">' . $strOrder . '</th>' . "\n";
    print '<th>' . $strCamPosition . '</th>' . "\n";
    print '<th>' . $strName . '<br>' . $strUpdateControl . '</th>' . "\n";
    print '</tr>' . "\n";
    print '<tr>' . "\n";
    print '<th colspan="2"><span style="font-size:120%;">' . $sLeftDisplay
        . '</span><br>$ avreg-mon [--display=L]</th>' . "\n";
    print '<th colspan="2"><span style="font-size:120%;">' . $sRightDisplay
        . '</span><br>$ avreg-mon --display=R</th>' . "\n";
    print '</tr>' . "\n";
    for ($mon_nr = 0; $mon_nr <= 9; $mon_nr++) {
        print "<tr>\n";
        if (array_key_exists($mon_nr, $LD)) {
            prt_l('L', $mon_nr, $LD[$mon_nr], $admin_user);
            print '<td>';
            layout2table($LD[$mon_nr]['layout_type'], 160, $LD[$mon_nr]['wins']);
            print '</td>' . "\n";
        } else {
            if ($admin_user) {
                print '<td colspan="2" align="center"><a href="' . $conf['prefix']
                    . '/admin/mon-addnew.php?display=L&mon_nr=' . $mon_nr . '">' . $l_mon_addnew . '</a></td>' . "\n";
            } else {
                print '<td colspan="2">&nbsp;</td>' . "\n";
            }
        }
        print '<td align="center"><div style="font-size:24px;font-weight:bold;padding:7px;border: 2px solid #303030;">'
            . $mon_nr . '</div></td>' . "\n";
        if (array_key_exists($mon_nr, $RD)) {
            print '<td>';
            layout2table($RD[$mon_nr]['layout_type'], 160, $RD[$mon_nr]['wins']);
            print '</td>' . "\n";
            prt_l('R', $mon_nr, $RD[$mon_nr], $admin_user);
        } else {
            if ($admin_user) {
                print '<td colspan="2" align="center"><a href="' . $conf['prefix']
                    . '/admin/mon-addnew.php?display=R&mon_nr=' . $mon_nr . '">' . $l_mon_addnew . '</a></td>' . "\n";
            } else {
                print '<td colspan="2">&nbsp;</td>' . "\n";
            }
        }
        print "</tr>\n";
    }
    print "</table>\n";
}

// phpinfo ();
require('../foot.inc.php');
