<?php
/**
 * @file admin/active_pipe.inc.php
 * @brief Содержит js-функции и инициализирует переменные <br />необхоимые для усановки камер в раскладке
 */
?>
<script type="text/javascript" language="JavaScript1.2">
    <!--
    function validate() {
        var cams_selects = document.getElementsByName('mon_wins[]');
        if (typeof(cams_selects) == 'undefined') {
            return false;
        }
        var cams_select = null;
        var i;
        var choised = 0;
        for (i = 0; i < cams_selects.length; i++) {
            cams_select = cams_selects[i];
            if (cams_select.selectedIndex > 0) {
                choised++;
            }
        }
        if (choised > 0) {
            return true;
        }
        else {
            alert('Сначала Вы должны выбрать камеры для просмотра в форме.');
            return false;
        }
    }

    function sel_change(sel) {
        if (sel.selectedIndex == 0) {
            return true;
        }
        var cams_selects = document.getElementsByName('mon_wins[]');
        if (typeof(cams_selects) == 'undefined') {
            return false;
        }
        var cams_select = null;
        var i;
        for (i = 0; i < cams_selects.length; i++) {
            cams_select = cams_selects[i];
            if (cams_select != sel) {
                if (cams_select.selectedIndex == sel.selectedIndex) {
                    alert('Камера №' + sel.options[sel.selectedIndex].text + ' уже выбрана  в другом окне');
                    sel.selectedIndex = 0;
                    break;
                }
            }
        }
        return false;
    }
    // -->
</script>

<?php
require_once('../lib/cams_main_detail.inc.php');

echo '<h2 align="center">' . $r_moncam_list . '</h2>' . "\n";
if (!isset ($pipes_show)) {
    $pipes_show = 1;
} else {
    settype($pipes_show, 'int');
}

print '<form action="' . $_SERVER['REQUEST_URI'] . '" method="POST">' . "\n";
print '<p align="center">' . $strWcListShow .
getSelectHtml(
    'pipes_show',
    $CamListShowMode,
    false,
    1,
    0,
    $CamListShowMode[$pipes_show],
    false,
    true
) . '</p>' . "\n";
if (isset($_POST) && is_array($_POST)) {
    reset($_POST);
    while (list($p, $v) = each($_POST)) {
        if (0 !== strpos($p, 'pipes_show')) {
            print '<input type="hidden" name="' . $p . '" value="' . $v . '">' . "\n";
        }
    }

}
print '</form>' . "\n";

require('../lib/get_cams_params.inc.php');
$cams_params = get_cams_params(array(
    'work',
    'video_src',
    'audio_src',
    'geometry',
    'v4l_dev',
    'input',
    'InetCam_IP',
    'InetCam_http_port',
    'InetCam_rtsp_port',
    'V.http_get',
    'A.http_get',
    'rtsp_play',
    'alsa_dev_name',
    'allow_local',
    'v4l_pipe',
    'text_left'
));
$cams_nbr = count($cams_params) - 1; /// XXX без дефолтной 0

$active_pipes = array();
$active_pipes_nr = 0;

if ($cams_nbr > 0) {
    // строим список активных для просмотра пайпов
    if ($pipes_show > 0) {
        print '<div align="center">' . "\n";
        print $tabletag . "\n";
        print '<tr bgcolor="' . $header_color . '">' . "\n";
        print '<th nowrap colspan=2>' . $strCam . '</th>' . "\n";
        print '<th>' . $strGeo . '</th>' . "\n";
        if ($pipes_show > 1) {
            print '<th>' . $sUnavailableReason . '</th>' . "\n";
        }
        print '</tr>' . "\n";
    }
    $r_count = 0;
    $c_work = 0;
    $c_video_src = 0;
    $c_mon_live = 0;
    $c_v4l_pipe = '';

    $show_colums = array(
       'ICONS' => false,
       'CAM_NR' => true,
       'NAME' => true,
       'SRC' => false,
       'RESOLUTION' => true,
    );

    foreach ($cams_params as $__cam_nr => $cam_detail) {
        /* пропускаем шаблонную */
        if ($__cam_nr == 0) {
            continue;
        }
        $cam_name = getCamName($cam_detail['text_left']['v']);
        $c_work = intval($cam_detail['work']['v']);
        $c_video_src = intval(!is_empty_var($cam_detail['video_src']['v']));
        $c_mon_live = intval($cam_detail['allow_local']['v']);
        $c_v4l_pipe =& $cam_detail['v4l_pipe']['v'];

        if (($c_work && $c_video_src && $c_mon_live && isset($c_v4l_pipe))) {
            $active_pipes[$active_pipes_nr] = $__cam_nr;
            $active_pipes_nr++;
        } else {
            if ($pipes_show == 1) {
                // показывать только доступные для просмотра
                continue;
            }
        }
        if ($pipes_show > 0) {
            $r_count++;
            if ($r_count % 2) {
                print '<tr style="background-color:#FCFCFC">' . "\n";
            } else {
                print "<tr>\n";
            }
            print_cam_detail_row($conf, $__cam_nr, $cam_detail, $show_colums);

            if ($pipes_show > 1) {
                // показывыть все c причиной почему не доступно
                $off_reason = '&nbsp;';
                if ($c_work === 0) {
                    if ($install_user) {
                        $off_reason .= '<a href="./cam-tune.php?&cam_nr=' . $__cam_nr .
                            '&categories=1#work" class="normal_link">';
                    }
                    $off_reason .= 'work';
                    if ($install_user) {
                        $off_reason .= '</a>';
                    }
                    $off_reason .= ' = "' . $flags[0] . '";&nbsp;&nbsp;';
                }
                if ($c_video_src === 0) {
                    if ($install_user) {
                        $off_reason .= '<a href="./cam-tune.php?&cam_nr=' . $__cam_nr .
                            '&categories=3#video_src" class="normal_link">';
                    }
                    $off_reason .= 'video_src';
                    if ($install_user) {
                        $off_reason .= '</a>';
                    }
                    $off_reason .= '="' . $srtUndef . '";&nbsp;&nbsp;';
                }
                if ($c_mon_live === 0) {
                    if ($install_user) {
                        $off_reason .= '<a href="./cam-tune.php?&cam_nr=' . $__cam_nr .
                            '&categories=15#allow_local" class="normal_link">';
                    }
                    $off_reason .= 'allow_local';
                    if ($install_user) {
                        $off_reason .= '</a>';
                    }
                    $off_reason .= ' = "' . $flags[0] . '";&nbsp;&nbsp;';
                }
                if (!isset($c_v4l_pipe)) {
                    if ($install_user) {
                        $off_reason .= '<a href="./cam-tune.php?&cam_nr=' . $__cam_nr .
                            '&categories=15.1#v4l_pipe" class="normal_link">';
                    }
                    $off_reason .= 'v4l_pipe';
                    if ($install_user) {
                        $off_reason .= '</a>';
                    }
                    $off_reason .= '="' . $srtUndef . '";&nbsp;&nbsp;';
                }
                print '<td>' . $off_reason . '</td>' . "\n";
            }
            print '</tr>' . "\n";
        }
    } // for

    if ($pipes_show > 0) {
        print '</table>' . "\n";
        print '</div>' . "\n";
    }
}

if ($active_pipes_nr === 0) {
    print '<div class="warn">' . $strNotViewCams . '</div>' . "\n";
} else {
    print '<script type="text/javascript" language="JavaScript1.2">' . "\n";
    print '<!--' . "\n";
    print 'var CNAMES = new MakeArray(' . $active_pipes_nr . ')' . "\n";
    for ($i = 0; $i < $active_pipes_nr; $i++) {
        print 'CNAMES[' . $i . ']="cam ' . $active_pipes[$i] . '";' . "\n";
    }
    print '// -->' . "\n";
    print '</script>' . "\n";
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
