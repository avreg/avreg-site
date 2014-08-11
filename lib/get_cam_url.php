<?php

/**
 *
 * @file lib/get_cam_url.php
 * @brief Формирование ссылки на видео с камеры сервера avregd
 *
 */

$cams_subconf = load_profiles_cams_confs();

$__tmp = & $conf['avregd-httpd'];
eval("\$http_cam_location = \"$__tmp\";");
unset($__tmp);

/**
 * Возвращает $url если переданный url верный и url avred в случае, если url некорректный
 * @return null|string
 */
function checkUrlParam($url = null)
{
    if (isset($url) && filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }

    return null;
}

/**
 *
 * Функция, которая возвращает ссылку на просмотр видео с камеры
 * @param array $conf масив настроек
 * @param int $cam_nr номер камеры
 * @param string $media тип медиа
 * @param bool $append_abenc аутентификация пользователя
 * @return string адрес видео с камеры
 */
function get_cam_http_url($conf, $cam_nr, $media, $append_abenc = false)
{
    $cams_subconf = & $GLOBALS['cams_subconf'];

    if ($cams_subconf && isset($cams_subconf[$cam_nr])
        && !empty($cams_subconf[$cam_nr]['avregd-httpd'])) {
        $_a = & $cams_subconf[$cam_nr]['avregd-httpd'];
        eval("\$url = \"$_a\";");
    } else {
        $url = $GLOBALS['http_cam_location'];
    }
    $path_var = sprintf('avregd-%s-path', $media);
    if (isset($conf[$path_var])) {
        $url .= sprintf("%s?camera=%d", $conf[$path_var], $cam_nr);
    }
    if ($append_abenc && !empty($GLOBALS['user_info']['USER'])) {
        $url .= '&ab=' . base64_encode($GLOBALS['user_info']['USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
    }

    return $url;
}

function get_cam_alt_url($alt_src, $cam_nr, $append_abenc)
{
    if (!isset($alt_src) || $alt_src == "") {
        return '';
    }
    $url = $alt_src;
    $test = array();
    preg_match("/\?camera=\d*/", $alt_src, $test);
    if (sizeof($test) == 0) {
        $url .= sprintf("?camera=%d", $cam_nr);
    }
    if ($append_abenc && !empty($GLOBALS['user_info']['USER'])) {
        $url .= '&ab=' . base64_encode($GLOBALS['user_info']['USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
    }

    return $url;
}

/***
 * Возвращает url медиа-потоков камеры из настроек аврега.
 *
 * @param array $cam_params      масив настроек камеры $GCP_cams_params[$cam_nr],
 *                               необходимый для постройки URL
 * @param string $pref_proto_csv CSV-список протоколов в порядке предпочтения
 * @return null|string           адрес видео с камеры
 */
function build_cam_url($cam_params, $pref_proto_csv = null)
{
    if (@empty($cam_params['video_src'])) {
        return null;
    }

    if (is_empty_var($pref_proto_csv)) {
        $proto_a = array($cam_params['video_src']);
    } else {
        $proto_a = array_map('trim', explode(',', $pref_proto_csv));
        $proto_a = array_map('strtolower', $proto_a);
    }

    foreach ($proto_a as &$proto_pref) {
        $req = '/';
        $port = '';
        $auth = '';
        if ($proto_pref === 'rtsp') {
            if (!@empty($cam_params['InetCam_rtsp_port']) && (int)$cam_params['InetCam_rtsp_port'] !== 554) {
                $port = ':' . $cam_params['InetCam_rtsp_port'];
            }
            if (!@empty($cam_params['rtsp_play'])) {
                $req =& $cam_params['rtsp_play'];
            }
        } elseif ($proto_pref === 'http') {
            if (!@empty($cam_params['InetCam_http_port']) && (int)$cam_params['InetCam_http_port'] !== 80) {
                $port = ':' . $cam_params['InetCam_http_port'];
            }
            if (!@empty($cam_params['V.http_get'])) {
                $req =& $cam_params['V.http_get'];
            }
        } else {
            return null;
        }
        if (@empty($cam_params['InetCam_IP'])) {
            return null;
        }

        if (!@empty($cam_params['InetCam_USER'])) {
            $pwd = '';
            if (!@empty($cam_params['InetCam_USER'])) {
                $pwd =& $cam_params['InetCam_PASSWD'];
            }
            $auth = $cam_params['InetCam_USER'] . ':' . $pwd . '@';
        }

        $url = $proto_pref . '://' . $auth . $cam_params['InetCam_IP'] . $req;
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
    }
    return null;
} /* build_cam_url() */

function get_alt_url($conf, $cam_nr, $cams_params, $alt_url = null)
{
    $url = null;
    if (!is_empty_var($alt_url)) {
        $a = explode(':', $alt_url);
        if (is_array($a) && count($a) >= 1) {
            $proto = strtolower($a[0]);
            switch ($proto) {
                case 'http':
                case 'rtsp':
                case 'rtmp':
                    $url = checkUrlParam($alt_url);
                    break;
                default:
                    /* excect cam number */
                    if (settype($proto, 'integer')) {
                        $target_cam_nr = $proto;
                        if (array_key_exists($target_cam_nr, $cams_params) &&
                            !empty($cams_params[$target_cam_nr]['video_src'])) {
                            if (@empty($a[1]) || 0 === stripos($a[1], 'avreg')) {
                                $url_src = 'avregd';
                                $url = get_cam_http_url($conf, $target_cam_nr, 'mjpeg', true);
                            } else {
                                $url_src = 'camera';
                                $url = build_cam_url($cams_params[$target_cam_nr], $a[2]);
                            }
                        }
                    }
                    break;
            }
        }
    }

    if (is_empty_var($url)) {
        $url = get_cam_http_url($conf, $cam_nr, 'mjpeg', true);
    }

    return $url;
} /* get_alt_url() */
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
