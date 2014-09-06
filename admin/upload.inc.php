<?php

if (!isset($_FILES) || !is_array($_FILES)) {
    MYDIE('$_FILES empty or not an array', __FILE__, __LINE__);
}

$paramsnames = array_keys($_FILES);
if (!is_array($paramsnames)) {
    MYDIE('$paramsnames is not array', __FILE__, __LINE__);
}

$defPAR = ($cam_nr === 0) ? true : false;

$file_cnt = count($paramsnames);
for ($i = 0; $i < $file_cnt; $i++) {
    $do_reset_fileupload_param = false;
    $_parname = & $paramsnames[$i];
    $uplfile = & $_FILES[$_parname];

    if (empty($uplfile['name']) && $uplfile['error'] === UPLOAD_ERR_NO_FILE) {
        if (isset($GLOBALS[$_parname . '_del'])) {
            $do_reset_fileupload_param = true;
        } else {
            continue;
        }
    } else {
        if ($uplfile['error'] != 0) {
            die(sprintf(
                '<p class="HiLiteErr">Error upload file `%s\': %s</p>',
                $uplfile['name'],
                $upload_status[$uplfile['error']]
            ));
        }
    }

    if (!isset($campars4upload)) {
        require_once('../lib/get_cams_params.inc.php');
        $campars4upload = get_cams_params(array( 'work', 'geometry'), $cam_nr);
        list($cam_w, $cam_h) = explode('x', $campars4upload[$cam_nr]['geometry']['v']);
        settype($cam_w, "integer");
        settype($cam_h, "integer");
    }

    if (!$do_reset_fileupload_param) {
        $uploadfile = tempnam($conf['upload-dir'], 'cam_' . $cam_nr . '_mask_');
        $img_info = getimagesize($uplfile['tmp_name']);
        if (empty($img_info)) {
            MYDIE("getimagesize($uplfile[tmp_name]) failed", __FILE__, __LINE__);
        }
        if ($img_info['mime'] !== 'image/jpeg') {
            die(sprintf(
                '<p class="HiLiteErr">Upload file "%s" is not a jpeg file!</p>',
                $uplfile['name']
            ));
        }
        if ($cam_w !== $img_info[0] || $cam_h !== $img_info[1]) {
            die(sprintf(
                '<p class="HiLiteErr">Resolutions mismatch: cam[%d][geometry] = "%dx%d",<br />'
                . 'but the uploaded image "%s" has "%dx%d" resolution!</p>',
                $cam_nr,
                $cam_w,
                $cam_h,
                $uplfile['name'],
                $img_info[0],
                $img_info[1]
            ));
        }
        switch ($_parname) {
            case 'mask_file':
                $mask_target_dir = $conf['masks-dir'];
                if (@empty($AVREG_PROFILE)) {
                    $mask_target_dir .= $AVREG_PROFILE;
                    if (!is_dir($mask_target_dir)) {
                        mkdir($mask_target_dir);
                    }
                }
                if (!$defPAR) {
                    $_val = sprintf('%s/cam%03d_mask.pgm', $mask_target_dir, $cam_nr);
                } else {
                    $_val = sprintf('%s/def_mask.pgm', $mask_target_dir);
                }
                // сохраняем файл и преобразовываем
                if (!move_uploaded_file($uplfile['tmp_name'], $uploadfile)) {
                    @unlink($uploadfile);
                    die(sprintf(
                        '<p class="HiLiteErr">Upload file `%s\' error: %s</p>',
                        $uplfile['name'],
                        $upload_status[$uplfile['error']]
                    ));
                } else {
                    $djpeg = sprintf(
                        '%s -grayscale -pnm -outfile \'%s\' \'%s\' 2>&1 >/dev/null',
                        $conf['djpeg'],
                        $_val,
                        $uploadfile
                    );
                    // tohtml($djpeg);
                    exec($djpeg, $output, $retval);
                    @unlink($uploadfile);
                    if ($retval != 0) {
                        @unlink($_val);
                        echo('<p class="HiLiteErr">MASK FILE `' . $uplfile['name'] . '\': JPEG conversion error:');
                        if (is_array($output)) {
                            foreach ($output as &$line) {
                                echo '<br />' . $line . "\n";
                            }
                        }
                        die('</p>');
                    }
                }
                break;
            default:
                MYDIE($_parname . ' not supported', __FILE__, __LINE__);
                break;
        } // switch (parname)
    }

    // save to database
    if ($do_reset_fileupload_param) {
        $adb->replaceCamera('local', $cam_nr, $_parname, null, $remote_addr, $login_user);

        print_syslog(
            LOG_NOTICE,
            sprintf(
                'cam[%s]: set param `%s\' to NULL, old value `%s\'',
                ($cam_nr) ? sprintf("%d", $cam_nr) : 'default',
                $_parname,
                $olds[$_parname]
            )
        );
    } else {
        $adb->replaceCamera('local', $cam_nr, $_parname, $_val, $remote_addr, $login_user);
        print_syslog(
            LOG_NOTICE,
            sprintf(
                'cam[%s]: set param `%s\' to `%s\', old value `%s\'',
                ($cam_nr) ? sprintf("%d", $cam_nr) : 'default',
                $_parname,
                $_val,
                $olds[$_parname]
            )
        );
    }
} // for all uploaded files
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
