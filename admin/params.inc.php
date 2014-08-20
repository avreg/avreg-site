<?php

function translit_ru($rustr)
{
    if (empty($rustr)) {
        return $rustr;
    }
    mb_regex_encoding('UTF-8');

    $patterns = array(
        'а',
        'б',
        'в',
        'г',
        'д',
        'е',
        'ё',
        'з',
        'и',
        'й',
        'к',
        'л',
        'м',
        'н',
        'о',
        'п',
        'р',
        'с',
        'т',
        'у',
        'ф',
        'х',
        'ъ',
        'ы',
        'э',
        'А',
        'Б',
        'В',
        'Г',
        'Д',
        'Е',
        'Ё',
        'З',
        'И',
        'Й',
        'К',
        'Л',
        'М',
        'Н',
        'О',
        'П',
        'Р',
        'С',
        'Т',
        'У',
        'Ф',
        'Х',
        'Ъ',
        'Ы',
        'Э',
        'ж',
        'ц',
        'ч',
        'ш',
        'щ',
        'ь',
        'ю',
        'я',
        'Ж',
        'Ц',
        'Ч',
        'Ш',
        'Щ',
        'Ь',
        'Ю',
        'Я'
    );

    $replacements = array(
        'a',
        'b',
        'v',
        'g',
        'd',
        'e',
        'e',
        'z',
        'i',
        'y',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'r',
        's',
        't',
        'u',
        'f',
        'h',
        '`',
        'i',
        'e',
        'A',
        'B',
        'V',
        'G',
        'D',
        'E',
        'E',
        'Z',
        'I',
        'Y',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'R',
        'S',
        'T',
        'U',
        'F',
        'H',
        '`',
        'I',
        'E',
        'zh',
        'ts',
        'ch',
        'sh',
        'shch',
        '',
        'yu',
        'ya',
        'ZH',
        'TS',
        'CH',
        'SH',
        'SHCH',
        '',
        'YU',
        'YA'
    );

    for ($i = 0; $i < sizeof($patterns); $i++) {
        $rustr = mb_ereg_replace($patterns[$i], $replacements[$i], $rustr);
    }

    return preg_replace(array('/[^A-Za-z0-9\-_\.~]/u'), array('_'), $rustr);
}

function WhatViddev($dev_file)
{
    $ret = -1;

    if (is_readable($dev_file)) {
        $handle = fopen($dev_file, 'r');
        while (!feof($handle)) {
            $buffer = fgets($handle, 256);
            if (preg_match('/^type +: VID_TYPE_CAPTURE.+/', $buffer)) {
                $ret = 1;
                break;
            } elseif (preg_match('/^name +: Video loopback \d* input$/', $buffer)) {
                $ret = 2;
                break;
            }
        }
        fclose($handle);
    }
    return $ret;
}

function find_param_defs($par_name)
{
    $d = null;
    foreach ($GLOBALS['PARAMS'] as &$d) {
        if ($d['name'] == $par_name) {
            return $d;
        }
    }
    return null;
}

function CheckParVal($_param, $_value)
{
    $ret = true;
    if ($_value === '' || is_null($_value)) {
        return $ret;
    }
    $par_defs = find_param_defs($_param);
    if (null == $par_defs) {
        die("Invalid param name: " . $_param);
    }

    if (!empty($par_defs['valid_preg'])) {
        if (!preg_match($par_defs['valid_preg'], $_value)) {
            $ret = false;
        }
    } else {
        switch ($par_defs['type']) {
            case $GLOBALS['INT_VAL']:
                if (!is_numeric($_value)) {
                    $ret = false;
                }
                break;
            case $GLOBALS['BOOL_VAL']:
                if (!($_value == '0' || $_value == '1')) {
                    $ret = false;
                }
                break;
        }
    }
    if (!$ret) {
        print '<div class="error">' . sprintf($GLOBALS['strParInvalid'], $_value, $_param) . '</div>' . "\n";
    }

    return $ret;
}

/***
 * Возвращаем отсортированный массив номеров v4l устройств
 * подходящий по шаблону $glob_patter
 * @param $glob_patter string шаблон имени: /dev/video* или
 *                            /sys/devices/virtual/video4linux/video*
 */
function getV4lDevNbrs($glob_pattern)
{
    $a = array();

    foreach (glob($glob_pattern) as $v4ldev_name) {
        if (preg_match('/\/video(\d+)$/', $v4ldev_name, $matches)) {
            $a[] = (int)$matches[1];
        }
    }
    sort($a, SORT_NUMERIC);
    unset($v4ldev_name);
    unset($matches);
    return $a;
}

function checkParam($parname, $parval, $def_val = null)
{
    switch ($parname) {
        case 'v4l_dev':
            $v4l_nbrs_all = getV4lDevNbrs('/dev/video[0-9]*');
            if (empty($v4l_nbrs_all)) {
                $ret = '<p style="color:' . $GLOBALS['error_color'] . ';">' . $GLOBALS['notVidDevs'] . '</p>'
                    . "\n";
                break;
            }
            $v4l_nbrs_virtual = getV4lDevNbrs('/sys/devices/virtual/video4linux/video[0-9]*');
            if (empty($v4l_nbrs_virtual)) {
                $v4l_nbrs_real = &$v4l_nbrs_all;
            } else {
                $v4l_nbrs_real = array_diff($v4l_nbrs_all, $v4l_nbrs_virtual);
            }

            if (!empty($v4l_nbrs_real)) {
                $ret = getSelectHtmlByName(
                    'fields[' . $parname . ']',
                    $v4l_nbrs_real,
                    false,
                    1,
                    0,
                    $parval,
                    true,
                    false,
                    '/dev/video'
                );
            } else {
                $ret = '<p style="color:"' . $GLOBALS['error_color'] . ';">' . $GLOBALS['notVidDevs'] . '</p>' . "\n";
            }
            break;

        case 'v4l_pipe':
            $v4l_nbrs_virtual = getV4lDevNbrs('/sys/devices/virtual/video4linux/video[0-9]*');
            if (empty($v4l_nbrs_virtual)) {
                $ret = '<p style="color:' . $GLOBALS['error_color'] . ';">' . $GLOBALS['notV4loop'] . '</p>'
                    . "\n";
                break;
            }

            $first_nb = $v4l_nbrs_virtual[0];
            if (strstr(file_get_contents("/sys/devices/virtual/video4linux/video$first_nb/name"), 'input')) {
                /* old v4loop < 2.0; video4linux v1; one pipe = 2 devices, input and output */
                $c = count($v4l_nbrs_virtual);
                $tmp = array();
                for ($i = 0; $i < $c; $i++) {
                    if (!($i % 2)) {
                        $tmp[] = $v4l_nbrs_virtual[$i];
                    }
                }
                $v4l_nbrs_virtual = &$tmp;
            }

            if (count($v4l_nbrs_virtual) > 0) {
                $ret = getSelectHtmlByName(
                    'fields[' . $parname . ']',
                    $v4l_nbrs_virtual,
                    false,
                    1,
                    0,
                    $parval,
                    true,
                    false,
                    '/dev/video'
                );
            } else {
                $ret = '<p style="color:"' . $GLOBALS['error_color'] . ';">' . $GLOBALS['notVidDevs'] . '</p>' . "\n";
            }
            break;

        case 'norm':
            if ($parval === '' || is_null($parval)) {
                $sel = '';
            } else {
                $sel = $GLOBALS['vid_standarts'][$parval];
            }
            $ret = getSelectHtml('fields[' . $parname . ']', $GLOBALS['vid_standarts'], false, 1, 0, $sel, true, false);
            break;
        case 'mask_file':
            if (empty($parval)) {
                $ret = $GLOBALS['strEmptied'] . '<br>';
            } else {
                $ret = '<a href="' . $GLOBALS['conf']['prefix'] . '/masks/' . basename(
                    $parval
                ) . '"  target="_blank">' . basename($parval) . '</a><br>' . "\n";
                $ret .= $GLOBALS['strDelete'] . ' &nbsp;&nbsp;<input type="checkbox" name="' . $parname . '_del"><br>'
                    . "\n";
            }
            $ret .= '<input type="hidden" name="MAX_FILE_SIZE" value="500000">' . "\n";
            $ret .= '<input type="file" name="' . $parname . '" size=20 maxlength=200>' . "\n";
            break;
        case 'video_src':
            $ret = getSelectHtmlByName(
                'fields[' . $parname . ']',
                $GLOBALS['video_sources'],
                false,
                1,
                0,
                $parval,
                true,
                false
            );
            break;
        case 'audio_src':
            $ret = getSelectHtmlByName(
                'fields[' . $parname . ']',
                $GLOBALS['audio_sources'],
                false,
                1,
                0,
                $parval
            );
            break;

        case 'decode_video':
            if ($parval === '' || is_null($parval)) {
                $sel = '';
            } else {
                $sel = $GLOBALS['decode_video_a'][$parval];
            }
            $ret = getSelectHtml(
                'fields[' . $parname . ']',
                $GLOBALS['decode_video_a'],
                false,
                1,
                0,
                $sel
            );
            break;

        case 'rtsp_transport':
            $ret = getSelectHtmlByName(
                'fields[' . $parname . ']',
                $GLOBALS['rtsp_transport'],
                false,
                1,
                0,
                $parval,
                true,
                false
            );
            break;

        case 'rec_mode':
            if ($parval == '' || is_null($parval)) {
                $sel = '';
            } else {
                $sel = $GLOBALS['recording_mode'][$parval];
            }
            $ret = getSelectHtml(
                'fields[' . $parname . ']',
                $GLOBALS['recording_mode'],
                false,
                1,
                0,
                $sel,
                true,
                false
            );
            break;

        case 'rec_format':
            $ret = getSelectHtmlByName(
                'fields[' . $parname . ']',
                $GLOBALS['recording_format'],
                false,
                1,
                0,
                $parval,
                true,
                false
            );
            break;

        case 'rec_vcodec':
            $ret = getSelectHtmlByName(
                'fields[' . $parname . ']',
                $GLOBALS['rec_vcodec'],
                false,
                1,
                0,
                $parval,
                true,
                false
            );
            break;

        case 'input':
            $ret = getSelectHtml(
                'fields[' . $parname . ']',
                array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15),
                false,
                1,
                0,
                $parval,
                true,
                false
            );
            break;
        case 'A.force_fmt':
            $ret = getSelectHtmlByName(
                'fields[' . $parname . ']',
                $GLOBALS['str_audio_force_fmt'],
                false,
                1,
                0,
                $parval,
                true,
                false
            );
            break;

        case 'rec_acodec':
            $ret = getSelectHtmlByName(
                'fields[' . $parname . ']',
                $GLOBALS['rec_acodec'],
                false,
                1,
                0,
                $parval,
                true,
                false
            );
            break;

        /*
           case 'rotate':
              if ( $parval == '' || is_null($parval) || $parval == '0' )
                 $sel = '';
              else
                 $sel = $GLOBALS['flip_type'][$parval-1];
              $ret = getSelectHtml('fields['.$parname.']', $GLOBALS['flip_type'], FALSE, 1, 1, $sel, TRUE, FALSE);
              break;
        */
        case 'v4l_hack':
            if ($parval == '' || is_null($parval) || $parval == '0') {
                $sel = '';
            } else {
                $sel = $GLOBALS['v4l_hacks'][$parval - 1];
            }
            $ret = getSelectHtml('fields[' . $parname . ']', $GLOBALS['v4l_hacks'], false, 1, 1, $sel, true, false);
            break;

        case 'events2db':
        case 'events2pipe':
            $ret = getChkbxByAssocAr(
                'fields[' . $parname . ']',
                $GLOBALS['event_groups'],
                $parval,
                false /* не работает select_all для имен содержащих []*/
            );
            break;

        case 'text_font_size':
            if ($parval == '' || is_null($parval) || $parval == '0') {
                $sel = '';
            } else {
                $sel = $GLOBALS['text_font_sizes'][$parval - 1];
            }
            $ret = getSelectHtml(
                'fields[' . $parname . ']',
                $GLOBALS['text_font_sizes'],
                false,
                1,
                1,
                $sel,
                true,
                false
            );
            break;

        case 'ptz':
            $all_ptz_handler_files = glob('../lib/PtzControllers/*.php');
            if (empty($all_ptz_handler_files)) {
                $ret = '<p style="color:' . $GLOBALS['error_color'] . ';">no PTZ handlers installed</p>' . "\n";
                break;
            }

            $all_ptz_handlers = array();
            foreach ($all_ptz_handler_files as $file) {
                if (preg_match('/.*\/([^\/\.]*)\.php$/', $file, $matches)) {
                    $all_ptz_handlers[] = $matches[1];
                }
            }

            $ret = getSelectHtmlByName(
                'fields[' . $parname . ']',
                $all_ptz_handlers,
                false,
                1,
                0,
                $parval,
                true,
                false
            );
            break;

        default:
            $ret = '<p style="color: ' . $GLOBALS['error_color'] . '">' . sprintf(
                $GLOBALS['unknownCheckParams'],
                $parname
            ) . '</p>' . "\n";
    } // switch

    return $ret;
}

/* CorrectParVal($parname, &$parval) */
function CorrectParVal($parname, $parval)
{
    return; /* disable function */
    switch ($parname) {
        case 'text_left':
            $parval = translit_ru($parval);
            break;
    }
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
