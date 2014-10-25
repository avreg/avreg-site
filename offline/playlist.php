<?php
/**
 * @file offline/playlist.php
 * @brief Архив : плейлист
 */
session_start();
$pageTitle = 'PlaylistTitle';
$pageBgColor = '#D0DCE0';
$body_onload = 'switch_timemode();';
$lang_file = '_offline.php';
$USE_JQUERY = true;
$link_javascripts = array('lib/js/checkbox.js');
require('../head.inc.php');
require('../lib/get_cams_params.inc.php');
DENY($arch_status);
?>

<script type="text/javascript" language="javascript">
    <!--

    var ie = document.all;
    var t = null;

    function switch_timemode() {
        if (!$('#timemode').is(':checked')) {
            $("#id_main_dayofweek input[type=checkbox]").prop('disabled', true);
        } else {
            $("#id_main_dayofweek input[type=checkbox]").prop('disabled', false);
        }
    }

    function unlock_submit_btn() {
        var btsubmit = ie ? document.all['btSubmit'] : document.getElementById('btSubmit');
        var btclr = ie ? document.all['btClear'] : document.getElementById('btClear');
        btsubmit.value = "<?php echo $GetPlaylistStr; ?>";
        btsubmit.disabled = false;
        btclr.disabled = false;
    }

    /***
     * Валидация данных и предотвращение частого многократного нажатия кнопки
     */
    function submit_frm(e) {
        $(".warn").remove();
        var warn = "<div class='warn' style='position:relative;'>" +
            "<h3> <?php echo $PlaylistFormValidation['title']; ?> ";
        var frmIsValide = true;
        if ($('#id_cams input.chbox_itm:checked').length == 0) {
            frmIsValide = false;
            warn += "<br> <?php echo $PlaylistFormValidation['no_cam']; ?> ";
        }
        if ($('#id_content_type input:checked').length == 0) {
            frmIsValide = false;
            warn += "<br> <?php echo $PlaylistFormValidation['no_media_type']; ?> ";
        }

        if ($("input[name=timemode]:checked").attr('value') == 2
                && $('#id_dayofweek input.chbox_itm:checked').length == 0) {
            frmIsValide = false;
            warn += "<br> <?php echo $PlaylistFormValidation['no_dayofweek']; ?> ";
        }
        warn += "</h3></div>";
        if (!frmIsValide) {
            $('form:first').before(warn);
            return false;
        }

        var btsubmit = ie ? document.all['btSubmit'] : document.getElementById('btSubmit');
        var btclr = ie ? document.all['btClear'] : document.getElementById('btClear');
        btsubmit.value = '<?php echo $strWait; ?> ...';
        btsubmit.disabled = true;
        btclr.disabled = true;
        t = setTimeout("unlock_submit_btn()", 3000);
        return true;
    }

    function TimeModeHelp() {
        alert("<?php echo $strTimeModeHelp; ?>");
    }
    function TimeModeHelp2() {
        alert("<?php echo $strTimeModeHelp2; ?>");
    }
    // -->
</script>

<?php
$cams_params = get_cams_params(array('work', 'text_left', 'rec_mode'));
$recorded_cams = array();
foreach ($cams_params as $__cam_nr => $_opt) {
    if ($__cam_nr <= 0) {
        continue;
    }
    if (((int)$_opt['rec_mode']['v']) > 0) {
        $recorded_cams[$__cam_nr] = empty($_opt['text_left']['v']) ?
            "cam $__cam_nr" : $_opt['text_left']['v'] . " ($__cam_nr)";
    }
}

if (!count($recorded_cams)) {
    print '<p><b>' . $strNotCamsDef2 . '</b></p>' . "\n";
    require('../foot.inc.php');
    exit;
}

// tohtml($recorded_cams);

/* presets */
$range_checked = 'checked';
$intervals_checked = '';
$xspf_checked = '';
$m3u_checked = 'checked';
$txt_checked = '';
$ftype_video_checked = 'checked';
$ftype_audio_checked = 'checked';

// tohtml($_COOKIE);
// tohtml($_SESSION);

if (isset($_COOKIE)) {
    if (isset($_COOKIE['avreg_cams'])) {
        $cams_sel = str_replace('-', ',', $_COOKIE['avreg_cams'][0]);
    } else {
        $cams_sel = '0,1,2,3';
    }
    if (isset($_COOKIE['avreg_ftypes'])) {
        $_ftypes = explode('-', $_COOKIE['avreg_ftypes'][0]);
        if (false === array_search('23', $_ftypes)) {
            $ftype_video_checked = '';
        }
        if (false === array_search('32', $_ftypes)) {
            $ftype_audio_checked = '';
        }
    }

    if (isset($_COOKIE['avreg_timemode'])) {
        if ($_COOKIE['avreg_timemode'] == '2') {
            $range_checked = '';
            $intervals_checked = 'checked';
        }
    }

    if (isset($_COOKIE['avreg_pl_fmt'])) {
        if (0 == strcasecmp('M3U', $_COOKIE['avreg_pl_fmt'])) {
            $m3u_checked = 'checked';
            $xspf_checked = '';
        } elseif (0 == strcasecmp('TXT', $_COOKIE['avreg_pl_fmt'])) {
            $txt_checked = 'checked';
            $xspf_checked = '';
        }
    }
}

if (isset($_SESSION)) {
    if (isset($_SESSION['dayofweek']) &&
        is_array($_SESSION['dayofweek']) &&
        count($_SESSION['dayofweek']) > 0
    ) {
        $day_of_week_preset = implode(',', $_SESSION['dayofweek']);
    }

    $int_vals = array(
        'timemode',
        'year1',
        'month1',
        'day1',
        'hour1',
        'minute1',
        'year2',
        'month2',
        'day2',
        'hour2',
        'minute2'
    );
    foreach ($int_vals as &$value) {
        if (isset($_SESSION[$value])) {
            $GLOBALS[$value] = (int)$_SESSION[$value];
        }
    }
    $int_vals = array('month1', 'day1', 'month2', 'day2');
    foreach ($int_vals as &$value) {
        if (isset($GLOBALS[$value])) {
            $GLOBALS[$value] -= 1;
        }
    }
} /* session */

if (empty($day_of_week_preset)) {
    $day_of_week_preset = '0,1,2,3,4,5,6';
}

if (!isset($year1) || !isset($year2)) {
    $tm2 = localtime(time(), true);
    $tm1 = localtime(strtotime('-1 hours'), true);
    $year1 = array_search($tm1['tm_year'] - 100, $year_array);
    $month1 = $tm1['tm_mon'];
    $day1 = array_search($tm1['tm_mday'], $day_array);
    $hour1 = array_search($tm1['tm_hour'], $hour_array);
    $year2 = array_search($tm2['tm_year'] - 100, $year_array);
    $month2 = $tm2['tm_mon'];
    $day2 = array_search($tm2['tm_mday'], $day_array);
    $hour2 = array_search($tm2['tm_hour'], $hour_array);
    $minute1 = 0;
    $minute2 = count($minute_array) - 1;
}

if (isset($_SESSION) && isset($_SESSION['error']) /* ошибка */) {

    if ($_SESSION['error'] == '0') {
        /* нет данных */
        echo "<div class=\"warn\">\n";
        echo "<h3>$strNotSavedPict</h3>\n";
        if ($conf['debug'] && !empty($_SESSION['sql'])) {
            echo $_SESSION['sql'];
        }
    } else {
        echo "<div class=\"error\">\n";
        echo 'error: invalid field &quot;' . $_SESSION['error'] . "&quot;\n";
    }
    echo "</div>\n";
}
?>

<form action="<?php echo $conf['prefix']; ?>/offline/_playlist.php" method="POST" onsubmit="return submit_frm()">
    <fieldset>
        <legend><?php echo $left_tune; ?></legend>
        <table cellspacing="0" border="0" cellpadding="5">
            <tr valign="top">
                <td style="width:300px;">
                    <?php

                    //формируем список чекбоксов выбора камер
                    print getChkbxByAssocAr('cams', $recorded_cams, $cams_sel, true, 8);

                    ?>
                </td>
                <td>
                    <fieldset id="id_content_type">
                        <legend><?php echo $strFTypeTitle; ?></legend>
                        <input id="chk_video" type="checkbox" <?php echo $ftype_video_checked; ?> name="ftypes[]"
                               value="23"><?php echo $env_id_ar[23]; ?>
                        <br>
                        <input id="chk_audio" type="checkbox" <?php echo $ftype_audio_checked; ?> name="ftypes[]"
                               value="32"><?php echo $env_id_ar[32]; ?>

                    </fieldset>
                </td>
            </tr>
        </table>
    </fieldset>
    <br>
    <fieldset>
        <legend><?php echo $strTimeRangeSelect; ?></legend>
        <table cellspacing="0" border="0" cellpadding="5">
            <?php print '<tr bgcolor="' . $header_color . '">' . "\n"; ?>
            <?php
            print '<th class="query" valign="bottom">' . $strTimeMode .
                '&nbsp;<a href="javascript:void(0);" onclick="TimeModeHelp();"><sup>help</sup></a></th>' . "\n";
            ?>
            <th class="query" valign="bottom"><?php echo "$strYear / $strMonth / $strDay"; ?></th>
            <th class="query" valign="bottom"><?php echo $strDayOfWeek; ?>&nbsp;
                <a href="javascript:void(0);" onclick="TimeModeHelp2();"><sup>help</sup></a>
            </th>
            <th class="query" valign="bottom"><?php echo "$strHour:$strMinute"; ?></th>
            </tr>
            <tr align="center" valign="top">
                <td align="left">
                    <br>
                    <input type="radio" <?php echo $range_checked; ?> name="timemode" id="timemode" value="1"
                           onclick="switch_timemode();"><?php echo $strUnBreak; ?>
                    <br><br>
                    <input type="radio" <?php echo $intervals_checked; ?> name="timemode" value="2"
                           onclick="switch_timemode();"><?php echo $strBreak; ?>
                </td>
                <td align="left" nowrap>
                    <?php
                    print "$sFromDate<br />\n";
                    print getSelectHtml('year1', $year_array, false, 1, 0, $year_array[$year1], false, false);
                    print getSelectHtml('month1', $month_array, false, 1, 1, $month_array[$month1], false, false);
                    print getSelectHtml('day1', $day_array, false, 1, 1, $day_array[$day1], false, false);
                    print "<br /><br />$sToDate<br />\n";
                    print getSelectHtml('year2', $year_array, false, 1, 0, $year_array[$year2], false, false);
                    print getSelectHtml('month2', $month_array, false, 1, 1, $month_array[$month2], false, false);
                    print getSelectHtml('day2', $day_array, false, 1, 1, $day_array[$day2], false, false);
                    ?>
                </td>
                <td align="center" style="width:150px;">
                    <?php
                    print getChkbxByAssocAr('dayofweek', $day_of_week, '0,1,2,3,4,5,6');
                    ?>
                </td>
                <td align="left" nowrap>
                    <?php
                    print "$sFromTime<br />\n";
                    print getSelectHtml('hour1', $hour_array, false, 1, 0, $hour_array[$hour1], false, false);
                    print getSelectHtml('minute1', $minute_array, false, 1, 0, $minute_array[$minute1], false, false);
                    print "<br /><br />$sToTime<br />\n";
                    print getSelectHtml('hour2', $hour_array, false, 1, 0, $hour_array[$hour2], false, false);
                    print getSelectHtml('minute2', $minute_array, false, 1, 0, $minute_array[$minute2], false, false);
                    ?>
                </td>
            </tr>
        </table>
    </fieldset>
    <br>
    <fieldset>
        <legend><?php echo $strPlFmtTitle; ?></legend>
        <input type="radio" name="pl_fmt" <?php echo $m3u_checked; ?>
               value="M3U"><?php echo $strM3U; ?>
        <br>
        <input type="radio" <?php echo $xspf_checked; ?>  name="pl_fmt"
               value="XSPF"><?php echo $strXSPF; ?>
        <br>
        <input type="radio" name="pl_fmt" <?php echo $txt_checked; ?>
               value="TXT"><?php echo $strTXT; ?>
        <br>
        <?php echo $strLinesEnding ?>
        <input type="radio" checked name="lineending" value="AUTO"> Auto
        &nbsp;&nbsp;
        <input type="radio" name="lineending" value="CRLF"> Windows
        &nbsp;&nbsp;
        <input type="radio" name="lineending" value="CR"> Unix
    </fieldset>
    <br>
    <fieldset>
        <input type="submit" id="btSubmit" value="<?php echo $GetPlaylistStr; ?>">
        &nbsp;&nbsp;
        <input type="reset" id="btClear" value="<?php echo $strReset; ?>">
        &nbsp;&nbsp;
        <a href="<?php echo $conf['prefix']; ?>/"
           target="_parent"><?php echo $MainPage; ?></a>
    </fieldset>
</form>
<div class="help">
    <legend class='tip-spoiler-title' title="<?php echo $strDisclose; ?>">
    <?php echo $strAdvices; ?> +
    </legend>
    <div class='tip-spoiler-body'>
        <ul>
            <li><?php echo $strBrowserHandlers; ?></li>
            <li><?php echo $strViewFrame2; ?></li>
            <li><?php echo $strMassCopy; ?></li>
       </ul>
    </div>
</div>

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
require('../foot.inc.php');
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */

