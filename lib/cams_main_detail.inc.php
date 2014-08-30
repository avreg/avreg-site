<?php

$__DEF_CAM_DETAIL_COLUMNS = array(
    'ICONS' => true,
    'CAM_NR' => true,
    'NAME' => true,
    'SRC' => true,
    'RESOLUTION' => true,
);

function get_css_class_param($val_src)
{
    switch ($val_src) {
        case 0:
            return 'parval_by_avregd';
        case 1:
            return 'parval_by_template';
        case 2:
            return 'parval_by_camera';
        default:
            return 'parval_by_unknown';
    }
}

function print_compact_url(&$url, $max_len = 10)
{
    if (strlen($url) < $max_len) {
        return $url;
    }
    return sprintf('<span title="%s">%s...&rarr;</span>', $url, substr($url, 0, $max_len - 1));
}

function _warn_emptied_param($param, $print_warn)
{
    if ($print_warn) {
        return '<span style="font-weight: bold;"> [«' . $param . '» ' .
        $GLOBALS['strEmptied'] . '] </span>';
    } else {
        return '${' . $param . '}';
    }
}

/**
 * return NULL  if have no video source
 *        TRUE  if has video and complete url
 *        FALSE if has video but not complete url
 */
function get_cam_src_url($cam_nr, $is_audio, $cam_detail, $print_warn, &$url, $do_link = false, $color_val_src = false)
{
    $ret = true;
    $url = '';

    $src_param_name = $is_audio ? 'audio_src' : 'video_src';
    $proto =& $cam_detail[$src_param_name]['v'];

    switch ($proto) {
        case 'alsa':
            if (!$is_audio) {
                return null;
            }
            if ($do_link) {
                $url .= '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=3#audio_src"'
                   . 'class="normal_link" >';
            }
            if ($color_val_src) {
                $url .= '<span class="' . get_css_class_param($cam_detail['audio_src']['s']) . '">';
            }
            $url .= "ALSA://";
            if ($color_val_src) {
                $url .= '</span>';
            }
            if ($do_link) {
                $url .= '</a>';
                $url .= '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=3.3#alsa_dev_name" ' .
                    'class="normal_link" >';
            }
            if ($color_val_src) {
                $url .= '<span class="' . get_css_class_param($cam_detail['alsa_dev_name']['s']) . '">';
            }
            if (empty($cam_detail['alsa_dev_name']['v'])) {
                $url .= _warn_emptied_param('alsa_dev_name', $print_warn);
                $ret = false;
            } else {
                $url .= $cam_detail['alsa_dev_name']['v'];
            }
            if ($color_val_src) {
                $url .= '</span>';
            }
            if ($do_link) {
                $url .= '</a>';
            }
            break;
        case 'video4linux':
            if ($is_audio) {
                return null;
            }
            if ($do_link) {
                $url .= '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=3#video_src"'
                   . 'class="normal_link" >';
            }
            if ($color_val_src) {
                $url .= '<span class="' . get_css_class_param($cam_detail['video_src']['s']) . '">';
            }
            $url .= 'v4l://';
            if ($color_val_src) {
                $url .= '</span>';
            }
            if ($do_link) {
                $url .= '</a>';
                $url .= '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=3.2#v4l_dev" ' .
                    'class="normal_link" >';
            }
            if ($color_val_src) {
                $url .= '<span class="' . get_css_class_param($cam_detail['v4l_dev']['s']) . '">';
            }
            if (!isset($cam_detail['v4l_dev']['v']) || is_empty_var($cam_detail['v4l_dev']['v'])) {
                $url .= _warn_emptied_param('v4l_dev', $print_warn);
                $ret = false;
            } else {
                $url .= '/dev/video' . $cam_detail['v4l_dev']['v'];
            }
            if ($color_val_src) {
                $url .= '</span>';
            }
            $input = isset($cam_detail['input']['v']) &&
               ! is_empty_var($cam_detail['input']['v']) ? $cam_detail['input']['v'] : 0;
            if ($color_val_src) {
                $url .= '<span class="' . get_css_class_param($cam_detail['input']['s']) . '">';
            }
            $url .= ":$input";
            if ($color_val_src) {
                $url .= '</span>';
            }
            if ($do_link) {
                $url .= '</a>';
            }
            break;
        case 'http':
            if ($do_link) {
                $url .= '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=3#' .
                     $src_param_name . '" class="normal_link" >';
            }
            if ($color_val_src) {
                $url .= '<span class="' . get_css_class_param($cam_detail[$src_param_name]['s']) . '">';
            }
            $url .= 'http://';
            if ($color_val_src) {
                $url .= '</span>';
            }
            if ($do_link) {
                $url .= '</a>';
                $url .= '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=3.1#InetCam_IP"'
                    . 'class="normal_link" >';
            }
            if ($color_val_src) {
                $url .= '<span class="' . get_css_class_param($cam_detail['InetCam_IP']['s']) . '">';
            }
            if (empty($cam_detail['InetCam_IP']['v'])) {
                $url .= _warn_emptied_param('InetCam_IP', $print_warn);
                $ret = false;
            } else {
                $url .= $cam_detail['InetCam_IP']['v'];
            }
            if ($color_val_src) {
                $url .= '</span>';
            }
            if ($do_link) {
                $url .= '</a>';
            }
            if (!empty($cam_detail['InetCam_http_port']['v']) && $cam_detail['InetCam_http_port']['v'] != 80) {
                if ($do_link) {
                    $url .= '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=3.1.1#InetCam_http_port" '
                        . 'class="normal_link" >';
                }
                if ($color_val_src) {
                    $url .= '<span class="' . get_css_class_param($cam_detail['InetCam_http_port']['s']) . '">';
                }
                $url .= ':' . $cam_detail['InetCam_http_port']['v'];
                if ($do_link) {
                    $url .= '</a>';
                }
                if ($color_val_src) {
                    $url .= '</span>';
                }
            }

            $http_get_par = $is_audio ? 'A.http_get' : 'V.http_get';
            if ($do_link) {
                $url .= '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=3.1.1.1#' .
                    $http_get_par . '" class="normal_link" >';
            }
            if ($color_val_src) {
                $url .= '<span class="' . get_css_class_param($cam_detail[$http_get_par]['s']) . '">';
            }
            if (empty($cam_detail[$http_get_par]['v'])) {
                $url .= _warn_emptied_param($http_get_par, $print_warn);
                $ret = false;
            } else {
                $url .= print_compact_url($cam_detail[$http_get_par]['v']);
            }
            if ($color_val_src) {
                $url .= '</span>';
            }
            if ($do_link) {
                $url .= '</a>';
            }
            break;
        case 'rtsp':
            if ($do_link) {
                $url .= '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=3#' .
                    $src_param_name . '" class="normal_link" >';
            }
            if ($color_val_src) {
                $url .= '<span class="' . get_css_class_param($cam_detail[$src_param_name]['s']) . '">';
            }
            $url .= 'rtsp://';
            if ($color_val_src) {
                $url .= '</span>';
            }
            if ($do_link) {
                $url .= '</a>';
                $url .= '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=3.1#InetCam_IP" ' .
                    'class="normal_link" >';
            }
            if ($color_val_src) {
                $url .= '<span class="' . get_css_class_param($cam_detail['InetCam_IP']['s']) . '">';
            }
            if (empty($cam_detail['InetCam_IP']['v'])) {
                $url .= _warn_emptied_param('InetCam_IP', $print_warn);
                $ret = false;
            } else {
                $url .= $cam_detail['InetCam_IP']['v'];
            }
            if ($color_val_src) {
                $url .= '</span>';
            }
            if ($do_link) {
                $url .= '</a>';
                $url .= '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=3.1.2#rtsp_play" '
                         . 'class="normal_link" >';
            }
            if ($color_val_src) {
                $url .= '<span class="' . get_css_class_param($cam_detail['InetCam_rtsp_port']['s']) . '">';
            }
            if (!empty($cam_detail['InetCam_rtsp_port']['v']) && $cam_detail['InetCam_rtsp_port']['v'] != 554) {
                $url .= ':' . $cam_detail['InetCam_rtsp_port']['v'];
            }
            if ($color_val_src) {
                $url .= '</span>';
            }
            if ($color_val_src) {
                $url .= '<span class="' . get_css_class_param($cam_detail['rtsp_play']['s']) . '">';
            }
            if (!empty($cam_detail['rtsp_play']['v'])) {
                $url .=  print_compact_url($cam_detail['rtsp_play']['v']);
            }
            if ($color_val_src) {
                $url .= '</span>';
            }
            if ($do_link) {
                $url .= '</a>';
            }
            break;
        default:
            return null;
    }
    return $ret;
}

function print_cam_detail_row($conf, $cam_nr, $cam_detail, $columns = null, $color_val_src = false)
{
    if (isset($columns)) {
        $_cols = & $columns;
    } else {
        $_cols = & $GLOBALS['__DEF_CAM_DETAIL_COLUMNS'];
    }
    $cam_active = ($cam_detail['work']['v'] > 0);
    if ($cam_nr > 0) {
        $cam_name = & $cam_detail['text_left']['v'];
    } else {
        $cam_name = $GLOBALS['r_cam_defs2'];
    }

    $do_link = (isset($_cols['SRC']) && $_cols['SRC'] === 'tune_link');
    $cam_has_video = get_cam_src_url($cam_nr, false, $cam_detail, ($cam_nr > 0), $video_url, $do_link, $color_val_src);
    $cam_has_audio = get_cam_src_url($cam_nr, true, $cam_detail, ($cam_nr > 0), $audio_url, $do_link, $color_val_src);

    /* print icons <td> */
    if (isset($_cols['ICONS']) && $_cols['ICONS']) {
        print '<td>';
        if (!is_null($cam_has_video)) {
            printf(
                '<img src="' . $conf['prefix'] . '%s" title="video" alt="%s" width="35" height="32" border="0">' . "\n",
                $cam_active ? '/img/cam_on_35x32.gif' : '/img/cam_off_35x32.gif',
                $cam_active ? $GLOBALS['flags'][1] : $GLOBALS['flags'][0]
            );
        } else {
            print '<span style="margin-left: 32px"></span>' . "\n";
        }

        if (!is_null($cam_has_audio)) {
            printf(
                '<img src="' . $conf['prefix'] . '%s" title="audio" alt="%s" width="32" height="32" border="0">' . "\n",
                $cam_active ? '/img/mic_on_32x32.gif' : '/img/mic_off_32x32.gif',
                $cam_active ? $GLOBALS['flags'][1] : $GLOBALS['flags'][0]
            );
        }
        print '</td>';
    }

    /* print cameras number <td> */
    if (isset($_cols['CAM_NR']) && $_cols['CAM_NR']) {
        if ($cam_active || $cam_nr <= 0) {
            print '<td align="center" valign="center" nowrap><div style="font-weight: bold;">&nbsp;' .
                $cam_nr . '&nbsp;</div></td>' . "\n";
        } else {
            print '<td align="center" valign="center" nowrap><div>&nbsp;' .  $cam_nr . '&nbsp;</div></td>' . "\n";
        }
    }

    /* print cameras name <td> */
    if (isset($_cols['NAME']) && $_cols['NAME']) {
        print '<td valign="center" nowrap>';
        if (!empty($_cols['NAME']['href'])) {
            $tag_a_cont = sprintf(
                'href="%s?camera=%d" title="%s"',
                $_cols['NAME']['href'],
                $cam_nr,
                $_cols['NAME']['title']
            );
            if ($cam_active || $cam_nr <= 0) {
                print '<a ' . $tag_a_cont . ' style="font-weight: bold;">' . $cam_name . '</a>' . "\n";
            } else {
                print '<a ' . $tag_a_cont . ' style="text-decoration: line-through;">' . $cam_name . '</a>' . "\n";
            }
        } else {
            if ($cam_active || $cam_nr <= 0) {
                print '<div style="font-weight: bold;">' . $cam_name . '</div>' . "\n";
            } else {
                print '<div style="text-decoration: line-through;">' . $cam_name . '</div>' . "\n";
            }
        }
        print "</div></td>\n";
    }

    /* print cameras source/type <td> */
    if (isset($_cols['SRC']) && $_cols['SRC']) {
        print('<td>');
        if (!is_null($cam_has_video) && !is_null($cam_has_video) &&
            $cam_detail['video_src']['v'] === $cam_detail['audio_src']['v'] &&
            $cam_detail['video_src']['v'] === 'rtsp') {
            printf('AV: %s', $video_url);
        } else {
            if (!is_null($cam_has_video)) {
                printf('V: %s', $video_url);
            }
            if (!is_null($cam_has_audio)) {
                if (!is_null($cam_has_video)) {
                    print("<br />\n");
                }
                printf('A: %s', $audio_url);
            }
            if (!is_null($cam_has_video) && !is_null($cam_has_audio)) {
                print('&nbsp;');
            }
        }
        print('</td>');
    }

    /* print cameras short capabilities <td> */
    if (isset($_cols['RESOLUTION']) && $_cols['RESOLUTION']) {
        if (!is_null($cam_has_video) || $cam_nr <= 0) {
            print '<td align="center" valign="center">';
            if ($_cols['RESOLUTION'] === 'tune_link') {
                print '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=3#geometry" ' .
                    'class="normal_link">';
            }
            if ($color_val_src) {
                print '<span class="' . get_css_class_param($cam_detail['geometry']['s']) . '">';
            }
            print $cam_detail['geometry']['v'];
            if ($color_val_src) {
                print '</span>';
            }
            if ($_cols['RESOLUTION'] === 'tune_link') {
                print '</a>';
            }
            print "</td>\n";
        } else {
            print '<td>&nbsp;</td>' . "\n";
        }
    }
    /* print cameras short capabilities <td> */
    if (isset($_cols['RECORDING']) && $_cols['RECORDING']) {
        $rec_mode_int = (int)$cam_detail['rec_mode']['v'];
        print '<td valign="center">';
        if ($rec_mode_int > 0) {
            print '<span style="color:red;">&bull;&nbsp;</span>';
        } else {
            print '<span style="visibility: hidden;">&bull;&nbsp;</span>';
        }
        if ($_cols['RECORDING'] === 'tune_link') {
            print '<a href="./cam-tune.php?&cam_nr=' . $cam_nr . '&categories=11" ' .
                'class="normal_link">';
        }
        if ($color_val_src) {
            print '<span class="' . get_css_class_param($cam_detail['rec_mode']['s']) . '">';
        }
        print $GLOBALS['recording_mode'][$rec_mode_int];
        if ($color_val_src) {
            print '</span>';
        }
        if ($_cols['RECORDING'] === 'tune_link') {
            print '</a>';
        }
        print "</td>\n";
    }
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
