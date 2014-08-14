<?php
/**
 * @file admin/cam-addnew.php
 * @brief Добавление новой видеокамеры на видеосервере
 */
/// Языковый файл
$lang_file = '_admin_cams.php';
require('../head.inc.php');
DENY($install_status);

?>

<script type="text/javascript" language="javascript">
    <!--
    function reset_to_list() {
        window.open('<?php echo $conf['prefix']; ?>/admin/cam-list.php', target = '_self');
    }
    // -->
</script>

<?php

if (isset($cmd) && $cmd == '_ADD_NEW_CAM_') {
    if (isset($cam_nr) && !empty($cam_nr)) {
        if (($cam_nr < 1) || ($cam_nr > $MAX_CAM)) {
            $str_err_fmt = "<font color=\"$error_color\"><p>" . $strError . ": " . $strField . " '%s' %s</p></font>\n";
            printf($str_err_fmt, $strCam_nr_Range, $cam_nr);
            require('../foot.inc.php');
            exit;
        }
        /* insert CAMS with min PARAMS */
        settype($cam_nr, 'integer');

        $adb->addCamera('local', $cam_nr, 'work', 0, $remote_addr, $login_user);

        if (isset($cam_text) && !empty($cam_text)) {
            $adb->addCamera('local', $cam_nr, 'text_left', $cam_text, $remote_addr, $login_user);
        }
        print ('<h4><font color="' . $warn_color . '">' . sprintf(
            $r_cam_addnew_ok1,
            $cam_nr,
            $cam_text
        ) . '</font></h4>');
        print ('<div class="warn">' . sprintf($r_cam_addnew_ok2, $cam_nr, $cam_text) . '</div>');
    } else {
        print ('<p><font color="' . $error_color . '">' . $strInvalidParams . '</font></p>');
    }
    print '<hr style="height:2px;border-width:0;color:gray;background-color:gray">'. "\n";
}

/// Номер добавляемой камеры
$cam_nr = $adb->maxCamNr();
if ($cam_nr) {
    $cam_nr = $cam_nr + 1;
} else {
    $cam_nr = 1;
}

echo '<h2>' . sprintf($r_cam_addnew, $cam_nr, $named, $sip) . '</h2>' . "\n";
print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
print $strSetCamName . ': ' . '<input type="text" name="cam_text" size=15 maxlength=15 value="' . $strCam . '_'
    . $cam_nr . '"><br>' . "\n";
print '<input type="hidden" name="cmd" value="_ADD_NEW_CAM_">' . "\n";
print '<input type="hidden" name="cam_nr" value="' . $cam_nr . '">' . "\n";
print '<br><input type="submit" name="btn" value="' . $l_cam_addnew . '" >' . "\n";
print '&nbsp;&nbsp;' . "\n";
print '<input type="reset" name="btnRevoke" value="' . $strRevoke . '" onclick="reset_to_list();">' . "\n";
print '</form>' . "\n";

require('../foot.inc.php');
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
