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
function get_avregd_cam_url($conf, $cam_nr, $media, $append_abenc = false, $query_string = '')
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
    } else {
        $url .= '?f=1';
    }
    if (!empty($query_string)) {
        $url .= '&' . $query_string;
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
 * @param string $query          query: param1=val1&param2=val2&...
 * @return null|string           адрес видео с камеры
 */
function build_cam_url($cam_params, $pref_proto_csv = null, $query = null)
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
        $__query = '';
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

        if (!@empty($query)) {
            if (false !== strrpos($req, '?')) {
                $__query = '&' . $query;
            } else {
                $__query = '?' . $query;
            }
        }

        $url = $proto_pref . '://' . $auth . $cam_params['InetCam_IP'] . $req . $__query;
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
    }
    return null;
} /* build_cam_url() */

/***
 * Строит альтернативный URL
 *   - {cam_nr}[:avregd[:http[:{req_params}]]
 *   - {cam_nr}:camera[:(rtsp|http)[:{req_params}]]
 *   - (http|rtsp)://[login:password@]host:port/path?query
 */
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
                        $who = @empty($a[1]) ? 'avregd' : $a[1];
                        $proto_pref_list = @empty($a[2]) ? null : $a[2];
                        $query_str = @empty($a[3]) ? null : $a[3];
                        if (array_key_exists($target_cam_nr, $cams_params) &&
                            !empty($cams_params[$target_cam_nr]['video_src'])) {
                            if (0 === stripos($who, 'avreg')) {
                                // $url_src = 'avregd';
                                $url = get_avregd_cam_url($conf, $target_cam_nr, 'mjpeg', true, $query_str);
                            } else {
                                // $url_src = 'camera';
                                $url = build_cam_url($cams_params[$target_cam_nr], $proto_pref_list, $query_str);
                            }
                        }
                    }
                    break;
            }
        }
    }

    if (is_empty_var($url)) {
        $url = get_avregd_cam_url($conf, $cam_nr, 'mjpeg', true);
    }

    return $url;
} /* get_alt_url() */
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
