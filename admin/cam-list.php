<?php
/**
 * @file admin/cam-list.php
 * @brief Список сконфигурированных видеокамер на сервере
 */
/// Языковый файл
$lang_file = '_admin_cams.php';
$USE_JQUERY = true;
require('../head.inc.php');
require('../lib/cams_main_detail.inc.php');

echo '<h1>' . sprintf($r_cam_list, $named, $sip) . '</h1>' . "\n";

if (isset($cmd)) {
    DENY($install_status);
    switch ($cmd) {
        case 'DEL':
            echo '<p class="HiLiteBigWarn">' . sprintf(
                $strDeleteCamConfirm,
                $cam_nr,
                $cam_name,
                $named,
                $sip
            ) . '</p>' . "\n";
            print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
            print '<input type="hidden" name="cmd" value="DEL_OK">' . "\n";
            print '<input type="hidden" name="cam_nr" value="' . $cam_nr . '">' . "\n";
            print '<input type="hidden" name="cam_name" value="' . $cam_name . '">' . "\n";
            print '<input type="submit" name="mult_btn" value="' . $strYes . '">' . "\n";
            print '<input type="submit" name="mult_btn" value="' . $strNo . '">' . "\n";
            print '</form>' . "\n";
            require('../foot.inc.php');
            exit;
            break; /**/
        case 'DEL_OK':
            if (($mult_btn == $strYes) && isset($cam_nr)) {

                $adb->deleteCamera($cam_nr);

                echo '<p class="HiLiteWarn">' . sprintf($strDeleteCam, $cam_nr, $cam_name) . '</p>' . "\n";
            }
            unset($cam_nr);
            break;
    }
}

if (!isset($cam_nr)) {
    if ($admin_user) {
        echo "<div class='warn'>\n";
        echo "<legend class='tip-spoiler-title' title='$strDisclose'>$strAdvices +</legend>\n";
        echo "<div class='tip-spoiler-body'>\n";
        if ($install_user) {
            echo $r_cam_tips_installers;
        } else {
            echo $r_cam_tips_admins;
        }
        echo "\n</div>\n</div>\n";
    }

    $GCP_query_param_list = array(
        'work',
        'video_src',
        'audio_src',
        'decode_video',
        'decode_audio',
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
        'text_left',
        'rec_mode',
        'allow_local',
        'allow_networks'
    );
    require('../lib/get_cams_params.inc.php');
    if ($install_user) {
        print '<p align="center"><a href="' . $conf['prefix'] . '/admin/cam-addnew.php">' . $l_cam_addnew
            . '</a></p>' . "\n";
    }
    if ($GCP_cams_nr > 0) {
        /* Printing results in HTML */
        $show_colums = array(
            'ICONS' => false,
            'CAM_NR' => true,
            'NAME' => true,
            'SRC' => 'tune_link',
            'RESOLUTION' => $install_user ? 'tune_link' : true,
            'RECORDING' => $install_user ? 'tune_link' : true,
        );

        print $tabletag . "\n";
        print '<tr bgcolor="' . $header_color . '">' . "\n";
        if ($admin_user) {
            if ($install_user) {
                print '<th>&nbsp;</th>' . "\n";
            }
            print '<th>&nbsp;</th>' . "\n";
        }
        print '<th nowrap colspan=2>' . $strCam . '</th>' . "\n";
        print '<th>' . $strSources . '</th>' . "\n";
        print '<th>' . $strGeo . '</th>' . "\n";
        print '<th>' . $strRecording . '</th>' . "\n";
        print '</tr>' . "\n";

        print '<tr>' . "\n";
        if ($admin_user) {
            if ($install_user) {
                print '<td>&nbsp;</td>' . "\n";
            }

            print '<td align="center" valign="center"><a href="./cam-tune.php?cam_nr=0">' .
                '<img width="24" height="24" src="' . $conf['prefix'] . '/img/gearwheel.png" '.
                'title="' . $strTune . '" alt="' . $strTune . '" />' .
                '</a></td>' . "\n";
        }
        $__cam_nr = 0;
        $cam_detail = & $GCP_def_pars;
        print_cam_detail_row($conf, $__cam_nr, $cam_detail, $show_colums);
        print "</tr>\n";

        $r_count = 0;
        reset($GCP_cams_params);

        //-->
        /*
        print '<pre>';
        var_dump($GCP_cams_params);
        print '</pre>';
        */
        //-->
        while (list($__cam_nr, $cam_detail) = each($GCP_cams_params)) {
            $cam_name = getCamName($cam_detail['text_left']);
            $r_count++;
            if ($r_count % 2) {
                print '<tr style="background-color:#FCFCFC">' . "\n";
            } else {
                print "<tr>\n";
            }
            if ($install_user) {
                if ($r_count == $GCP_cams_nr) {
                    // удалить последнюю камеру
                    $del_href = sprintf(
                        '%s?cmd=DEL&cam_nr=%d&cam_name=%s',
                        $_SERVER['PHP_SELF'],
                        $__cam_nr,
                        $cam_name
                    );
                    print '<td align="center" valign="center"><a href="' . $del_href . '" title="' . $strDelete . '">' .
                        '<img src="' . $conf['prefix'] . '/img/trash_24x24.png" '.
                        'title="' . $strDelete . '" alt="' . $strDelete . '" />' .
                        '</a></td>' . "\n";
                } else {
                    print '<td>&nbsp;</td>' . "\n";
                }
            }
            if ($admin_user) {
                //настроить камеру
                $tune_href = sprintf('./cam-tune.php?cam_nr=%d&cam_name=%s', $__cam_nr, $cam_name);
                print '<td><a href="' . $tune_href . '" title="' . $strTune . '">' .
                      '<img width="24" height="24" src="' . $conf['prefix'] . '/img/gearwheel.png" '.
                      'title="' . $strTune . '" alt="' . $strTune . '" />' .
                      '</a></td>' . "\n";
            }
            //вывести инфу о камере
            print_cam_detail_row($conf, $__cam_nr, $cam_detail, $show_colums);

            print "</tr>\n";
        }
        print "</table>\n";

        if ($install_user) {
            print '<p align="center"><a href="' . $conf['prefix'] . '/admin/cam-addnew.php">' . $l_cam_addnew
                . '</a></p>' . "\n";
        }
    } else {
        print '<p><b>' . $strNotCamsDef . '</b></p>' . "\n";
    }
    /* choice number cam */
}
?>

<script type='text/javascript'>
    $(document).ready(function() {
        $('.tip-spoiler-body').hide();
        $('.tip-spoiler-title').click(function() {
            $(this).toggleClass('opened').toggleClass('closed').next().slideToggle();
            if ($(this).hasClass('opened')) {
                $(this).html("<?php echo $strAdvices; ?> -");
                $(this).attr('title', "<?php $strClose; ?>");
            } else {
                $(this).html("<?php echo $strAdvices; ?> +");
                $(this).attr('title', "<?php $strDisclose; ?>");
            }
        });
    });
</script>

<?php
// phpinfo ();
require('../foot.inc.php');
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
