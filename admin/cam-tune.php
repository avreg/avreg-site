<?php
/**
 * @file admin/cam-tune.php
 * @brief Изменение параметров камер
 */
if (isset($_POST)) {
    $expire = time() + 5184000;
    if (isset($_POST['par_filter'])) {
        setcookie('avreg_par_filter', $_POST['par_filter'], $expire, $_SERVER['SCRIPT_NAME']);
    }
}
/// Языковый файл
$lang_file = '_admin_cams.php';
$USE_JQUERY = true;
$link_javascripts = array(
    'lib/js/misc_utils.js',
    'lib/js/checkbox.js',
    'lib/js/third-party/URI.js',
    'lib/js/third-party/jqModal.js'
 );
$css_links = array(
    'lib/js/third-party/jqModal.css',
    'admin/admin.css'
);

$include_javascripts = array(
    'lib/js/onvif-helpers.js',
    'admin/cam-tune.js'
 );
require('../head.inc.php');
DENY($admin_status);
require_once($params_module_name);
require('./params.inc.php');
require('../lib/get_cams_params.inc.php');

if (!isset($cam_nr) || !settype($cam_nr, 'int')) {
    die ('Empty cameras number');
}
/* if ( !isset($sip) || empty($sip) ) die ('Invalid server IP: `'.$sip.'`'); */
if (isset ($par_filter)) {
    settype($par_filter, 'int');
} else {
    if (isset($_COOKIE['avreg_par_filter'])) {
        $par_filter = (int)$_COOKIE['avreg_par_filter'];
    } else {
        $par_filter = 0;
    }
}
if (isset($cmd)) {
    if ($cmd == 'UPDATE_PARAM') {
        if (isset($_FILES) && is_array($_FILES)) {
            require('./upload.inc.php');
        }
        if (isset($types) && is_array($types) && (count($types) > 0)) {
            $cmd = 'SHOW_PARAM';
            while (list($parname, $partype) = each($types)) {
                if ($parname === 'mask_file') {
                    continue;
                }
                if (!isset($olds[$parname])) {
                    die ('Error in post data!');
                }
                $_value = isset($fields[$parname]) ? $fields[$parname] : '';

                if (is_array($_value)) {
                    $value = implode(',', array_map('rawurldecode', $_value));
                } else {
                    $value = trim(rawurldecode($_value));
                }
                // print "<p>'$parname'='$value' old='$olds[$parname]' types='$types[$parname]'</p>\n";
                if (($olds[$parname] != $value) && CheckParVal($parname, $value)) {
                    CorrectParVal($parname, $value);
                    $_val = ($value == '') ? null : html_entity_decode($value);
                    $adb->replaceCamera('local', $cam_nr, $parname, $_val, $remote_addr, $login_user);

                    print_syslog(
                        LOG_NOTICE,
                        sprintf(
                            'cam[%s]: update param "%s", set new value "%s", old value "%s"',
                            $cam_nr === 0 ? 'default' : (string)$cam_nr,
                            $parname,
                            empty($_val) ? "<empty>" : $_val,
                            empty($olds[$parname]) ? "<empty>" : $olds[$parname]
                        )
                    );
                }
            }
        }
    }
}

$cams_params = get_cams_params('work, text_left');
$__cam_arr = array();
foreach ($cams_params as $key => $value) {
    $__cam_arr[$key] = $key . ' ' . getCamName($value['text_left']['v']);
}
$__cam_arr[0] = '0 ' . $GLOBALS['r_cam_defs3'];

if (empty($__cam_arr)) {
    echo '<h3>' . sprintf($r_cam_tune, $cam_nr, $cam_name, $named) . '</h3>' . "\n";
} else {
    print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST" enctype="multipart/form-data">' . "\n";
    if ($cam_nr === 0) {
        echo '<h3>' . sprintf(
            $r_cam_defaults,
            getSelectByAssocAr('cam_nr', $__cam_arr, false, 1, 0, 0, false, true, ''),
            $named,
            $sip
        ) . '</h3>' . "\n";
    } else {
        echo '<h3>' . sprintf(
            $r_cam_tune,
            $cam_nr,
            getSelectByAssocAr('cam_nr', $__cam_arr, false, 1, 0, $cam_nr, false, true, ''),
            $named,
            $sip
        ) . '</h3>' . "\n";
        print '<input type="hidden" name="cam_name" value="' . $__cam_arr[$cam_nr] . '">' . "\n";
    }
    if (isset($categories)) {
        print '<input type="hidden" name="categories" value="' . $categories . '">' . "\n";
    }
    print '</form>' . "\n";
}

$WE_IN_DEFS = ($cam_nr === 0) ? true : false;
require('./param-grp.inc.php');

// выводим таблицу параметров
if (isset($categories)) {
    $result = $adb->getDefCamParams($cam_nr);

    $CAM_PARAMS = array();
    $DEF_CAM_PARAMS = array();
    foreach ($result as $row) {
        if ($cam_nr === 0) {
            // if user choose "group" camera - use $CAM_PARAMS instead $DEF_CAM_PARAMS
            $CAM_PARAMS[$row['PARAM']] = array(
                'value'        => $row['VALUE'],
                'changed_by'   => $row['CHANGE_USER'] . '@' . $row['CHANGE_HOST'],
                'changed_time' => $row['CHANGE_TIME'],
            );
        } else {
            if ($row['CAM_NR'] > 0) {
                $CAM_PARAMS[$row['PARAM']] = array(
                    'value'        => $row['VALUE'],
                    'changed_by'   => $row['CHANGE_USER'] . '@' . $row['CHANGE_HOST'],
                    'changed_time' => $row['CHANGE_TIME'],
                );
            } else {
                $DEF_CAM_PARAMS[$row['PARAM']] = array(
                    'value'        => $row['VALUE'],
                    'changed_by'   => $row['CHANGE_USER'] . '@' . $row['CHANGE_HOST'],
                    'changed_time' => $row['CHANGE_TIME'],
                );
            }
        }
    }
    $result = null;

    print '<br>' . "\n";
    print '<table width="100%" cellspacing="0" border="1" cellpadding="5" bgcolor="#dcdcdc">' . "\n";
    print '<tr>' . "\n";
    print '<td>' . "\n";
    print '<font size="-1" color="' . $NotSetParColor . '">* - ' . $not_defined . '</font>;' . "\n";
    print '<br><font size="-1" color="' . $ParDefColor . '">** - ' . $eval_with_def . '</font>;' . "\n";
    if ($cam_nr > 0) {
        print '<br><font size="-1" color="' . $ParSetColor . '">*** - ' . $not_eval_with_def . '</font>.' . "\n";
    }
    print '</td>' . "\n";
    print '<td>' . "\n";
    print '<img src="' . $conf['prefix'] . '/img/hotsync.gif" alt="Reloaded" border="0"> - ' . $strReloadDesc . "\n";
    print '<br><img src="' . $conf['prefix'] . '/img/hotsync_busy.gif" alt="Restarted" border="0"> - '
        . $strRestartDesc . "\n";
    print '</td>' . "\n";
    print '</tr>' . "\n";
    print '</table>' . "\n";
    print '<br /><form action="' . $_SERVER['PHP_SELF'] . '" method="POST" enctype="multipart/form-data">' . "\n";
    print $strDisplayed . '&nbsp;' . "\n";
    print getSelectByAssocAr('par_filter', $par_filter_ar, false, 1, null, (string)$par_filter, false, true);
    print $strParams . ".\n";
    print '&nbsp;&nbsp;<input type="submit" name="submit_btn" value="' . $strSave . '">' . "\n";
    print '<input type="reset" name="reset_btn" value="' . $strRevoke . '">' . "\n";
    print '<br /><table cellspacing="0" border="1" cellpadding="2" class="paramstbl">' . "\n";
    print '<tr bgcolor="' . $header_color . '">' . "\n";
    print '<th nowrap>' . $strName . '</th>' . "\n";
    print '<th>' . $strDescription . '</th>' . "\n";
    print '<th>' . $strUpdateControl . '</th>' . "\n";
    print '</tr>' . "\n";

    $p_count = count($PARAMS);

    for ($i = 0; $i < $p_count; $i++) {
        $parname1 = & $PARAMS[$i]['name'];
        $VAL_TYPE = & $PARAMS[$i]['type'];
        $VALID_PREG = & $PARAMS[$i]['valid_preg'];
        $DEF_VAL = & $PARAMS[$i]['def_val'];
        $COMMENT = & $PARAMS[$i]['desc'];
        $FLAGS = & $PARAMS[$i]['flags'];
        $PAR_CATEGORY = & $PARAMS[$i]['cats'];
        $SUBCAT_SELECTOR = & $PARAMS[$i]['subcats'];
        $MASTER_STATUS = & $PARAMS[$i]['mstatus'];
        if ($PAR_CATEGORY != $categories) {
            continue;
        }
        if ($user_status > $MASTER_STATUS) {
            continue;
        }

        if ($cam_nr === 0) {
            if (!($FLAGS & $F_IN_DEF)) {
                continue;
            }
        } else {
            if (!($FLAGS & $F_IN_CAM)) {
                continue;
            }
        }

        if ($par_filter === 0 && !($FLAGS & $F_BASEPAR)) {
            continue;
        }

        if (isset($DEF_VALUE)) {
            unset($DEF_VALUE);
        }
        if (isset($DEF_CHANGED_BY)) {
            unset($DEF_CHANGED_BY);
        }
        if (isset($DEF_CHANGED_TIME)) {
            unset($DEF_CHANGED_TIME);
        }
        if (isset($VALUE)) {
            unset($VALUE);
        }
        if (isset($CHANGED_BY)) {
            unset($CHANGED_BY);
        }
        if (isset($CHANGED_TIME)) {
            unset($CHANGED_TIME);
        }

        if ($cam_nr > 0 && array_key_exists($parname1, $DEF_CAM_PARAMS)) {
            $DEF_VALUE        = &$DEF_CAM_PARAMS[$parname1]['value'];
            $DEF_CHANGED_BY   = &$DEF_CAM_PARAMS[$parname1]['changed_by'];
            $DEF_CHANGED_TIME = &$DEF_CAM_PARAMS[$parname1]['changed_time'];
        } else {
            $DEF_VALUE = $DEF_CHANGED_BY = $DEF_CHANGED_TIME = null;
        }
        if (array_key_exists($parname1, $CAM_PARAMS)) {
            $VALUE        = &$CAM_PARAMS[$parname1]['value'];
            $CHANGED_BY   = &$CAM_PARAMS[$parname1]['changed_by'];
            $CHANGED_TIME = &$CAM_PARAMS[$parname1]['changed_time'];
        } else {
            $VALUE = $CHANGED_BY = $CHANGED_TIME = null;
        }
        print '<tr style="vertical-align:top"><td nowrap><div>' . "\n";

        if ($FLAGS & $F_RELOADED) {
            print '<img src="' . $conf['prefix'] . '/img/hotsync.gif" alt="Reloaded" border="0">&nbsp;';
        } else {
            print '<img src="' . $conf['prefix'] . '/img/hotsync_busy.gif" alt="Restarted" border="0">&nbsp;';
        }

        print '<span>' . "\n";
        print "<a href='#' name='$parname1'></a>\n";
        $def_val = ($DEF_VALUE === '' || is_null($DEF_VALUE)) ? null : $DEF_VALUE;
        $val = null;
        $val_owner = null; // avregd
        if (is_empty_var($VALUE)) {
            // не установленное поле
            if ($VALUE != $def_val) {
                print '<span style="color: ' . $ParDefColor . ';">' . $parname1 . '<sup>**</sup></span>';
                $val = $def_val;
                $val_owner = 0;
            } else {
                print '<span style="color: ' . $NotSetParColor . ';">' . $parname1 . '<sup>*</sup></span>';
                $val = null;
                $val_owner = null; // avregd
            }
        } else {
            if ($cam_nr === 0) {
                print '<span style="color: ' . $ParDefColor . ';">' . $parname1 . '<sup>**</sup></span>';
                $val_owner = 0;
            } else {
                print '<span style="color: ' . $ParSetColor . ';">' . $parname1 . '<sup>***</sup></span>';
                $val_owner = $cam_nr;
            }
            $val = $VALUE;
        }
        print '</span><br /><br /><div>' . "\n";
        $max_len = (isset($PARAMS[$i]['max_len'])) ? $PARAMS[$i]['max_len'] : 0;
        $str_f_len = ($max_len > 40) ? 40 : $max_len;

        switch ($VAL_TYPE) {
            case $INT_VAL:
                $a = ($val === '' || is_null($val)) ? '' : (integer)$val;
                $b = $max_len ? $max_len : 6;
                print '<input type="text" name="fields[' . $parname1 . ']" value="' . $a . '" size=6 maxlength='
                    . $b . '>';
                break;
            case $INTPROC_VAL:
                $a = ($val === '' || is_null($val)) ? '' : $val;
                $b = $max_len ? $max_len : 6;
                print '<input type="text" name="fields[' . $parname1 . ']" value="' . $a . '" size=6 maxlength='
                    . $b . '>';
                break;
            case $STRING_VAL:
                $a = getBinString($val);
                $b = $max_len ? $max_len : 60;
                if (!empty($a) && !empty($VALID_PREG) && !preg_match($VALID_PREG, $a)) {
                    printf('<p style="color: ' . $GLOBALS['error_color'] . ';">' . $fmtEINVAL . '</p>', $a);
                }
                print '<input type="text" name="fields[' . $parname1 . ']" value="' . $a . '" size=' . $str_f_len
                    . ' maxlength=' . $b . '>';
                break;
            case $STRING200_VAL:
                $a = getBinString($val);
                $b = $max_len ? $max_len : 200;
                print '<input type="text" name="fields[' . $parname1 . ']" value="' . $a . '" size=' . $str_f_len
                    . ' maxlength=' . $b . '>';
                break;

            case $PASSWORD_VAL:
                $a = getBinString($val);
                $b = $max_len ? $max_len : 60;
                print '<input type="password" name="fields[' . $parname1 . ']" value="' . $a . '" size=' . $str_f_len
                    . ' maxlength=' . $b . '>';
                break;
            case $CHECK_VAL:
                print checkParam($parname1, $val, $DEF_VAL);
                break;

            default: /* BOOL*/
                if ($val === '' || is_null($val)) {
                    print getSelectHtml('fields[' . $parname1 . ']', $flags, false, 1, 0, null, true, false);
                } else {
                    print getSelectHtml(
                        'fields[' . $parname1 . ']',
                        $flags,
                        false,
                        1,
                        0,
                        $flags[(integer)$val],
                        true,
                        false
                    );
                }
        }

        if ($parname1 !== 'work' && $parname1 !== 'text_left') {
            if (!is_empty_var($VALUE)) {
                /* reset action */
                if ($cam_nr <= 0 || $val_owner === $cam_nr) {
                    print '<a href="#" class="update_param" id="a~' .
                        $cam_nr . '~' . $parname1 . '~' . '~' . $def_val . '~' . $categories . '">' .
                        /*                          ^^^^^^^^^
                         *                          XXX - empty value */
                        '<img src="' . $conf['prefix'] . '/img/trash_24x24.png" title="' . $strReset . '" ' .
                        'alt="reset" width="24" height="24" style="vertical-align: bottom;" />' .
                        "</a>\n";
                }
            }
            if ($cam_nr > 0 && !is_empty_var($val) && $val !== $VALUE) {
                print '<a href="#" class="update_param" id="a~' .
                    $cam_nr . '~' . $parname1 . '~' . $val . '~' . $def_val . '~' . $categories . '">' .
                    /* ^^^^^^^^^ */
                    '<img src="' . $conf['prefix'] . '/img/pin_black_24x24.png" title="' . $strSetToCam . '" ' .
                    'alt="set" width="24" height="24" style="vertical-align: bottom;" />' .
                    "</a>\n";
            }
        }
        print '</div></div></td>' . "\n";
        print '<td>' . $COMMENT . '</td>' . "\n";
        if (empty($CHANGED_TIME)) {
            print "<td>-</td>\n";
        } else {
            print '<td nowrap>' . $CHANGED_BY . '<br>'
                . (empty($CHANGED_TIME) ? '-' : $CHANGED_TIME) . "\n";
        }
        print '<input type="hidden" name="types[' . $parname1 . ']" value="' . $VAL_TYPE . '">' . "\n";
        print '<input type="hidden" name="olds[' . $parname1 . ']" value="' . $val . '">' . "\n";
        print '</td>' . "\n";
        print '</tr>' . "\n";
    }
    print "</table>\n";
    print '<input type="hidden" name="cmd" value="UPDATE_PARAM">' . "\n";
    print '<input type="hidden" name="cam_nr" value="' . $cam_nr . '">' . "\n";
    if (isset($cam_name)) {
        print '<input type="hidden" name="cam_name" value="' . $cam_name . '">' . "\n";
    }
    print '<input type="hidden" name="categories" value="' . $categories . '">' . "\n";
    print '<input type="submit" name="submit_btn" value="' . $strSave . '">' . "\n";
    print '<input type="reset" name="reset_btn" value="' . $strRevoke . '">' . "\n";
    print '</form>' . "\n";
    print "<br>\n";

    // объединяем оба массива
    $all = array_merge($DEF_CAM_PARAMS, $CAM_PARAMS);
    $cam_main_info = array(
        'cam_nr' => (int)$cam_nr,
        'categories' => $categories,
        'cam_name' => empty($all['text_left']['value']) ? '' : $all['text_left']['value'],
        'video_src' => empty($all['video_src']['value']) ? '' : $all['video_src']['value'],
        'audio_src' => empty($all['audio_src']['value']) ? '' : $all['video_src']['value'],
        'InetCam_IP' => empty($all['InetCam_IP']['value']) ? '' : $all['InetCam_IP']['value'],
        'InetCam_http_port' => empty($all['InetCam_port']['value']) ? 80 : (int)($all['InetCam_port']['value']),
        'InetCam_USER' => empty($all['InetCam_USER']['value']) ? '' : $all['InetCam_USER']['value'],
        'InetCam_PASSWD' => empty($all['InetCam_PASSWD']['value']) ? '' : $all['InetCam_PASSWD']['value'],
    );

    print "<script type='text/javascript'>\n";
    print 'var cam_tune_info = '. json_encode($cam_main_info) . ";\n";
    print "</script>\n";

    require('./modal_onvif_connect.php');
    require('./modal_onvif_profiles.php');
}

require('../foot.inc.php');
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
