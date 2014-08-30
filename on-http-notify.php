<?php
/**
 * @file on-http-notify.php
 * @brief Генерирует уведомление
 *
 */

/* need for auth */
require_once('lib/config.inc.php');
require('../lib/get_cams_params.inc.php');

/**
 * Send http headers
 */
// Don't use cache (required for Opera)
$now = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: ' . $now);
header('Last-Modified: ' . $now);
header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0
// Define the charset to be used
header('Content-Type: text/html; charset=ISO-8859-1');

echo "<html><body>\r\n";

/* lookup camera's number from database */
unset($AVREG_CAMS_NR);
/* Массив номеров камер, найденных по InetCam_IP
    Массив, потому что для ip-видеосервера
    мы можем найти несколько камер с одним InetCam_IP */
$AVREG_CAMS_NR=array();
 
$cams_params = get_cams_params(array(
    'work',
    'video_src',
    'text_left',
    'InetCam_IP'));
$cams_nbr = count($cams_params) - 1;
if ($cams_nbr <= 0) {
    die('There are no available cameras!');
}
if (isset($cams_params) && is_array($cams_params)) {
    reset($cams_params);
    each($cams_params); // skip template camera
    while (list($_cam_nr, $CAM_PARAMS) = each($cams_params)) {
        if (($CAM_PARAMS['video_src']['v'] === 'rtsp' ||
            $CAM_PARAMS['video_src']['v'] === 'http') &&
            $CAM_PARAMS['InetCam_IP']['v'] === $_SERVER["REMOTE_ADDR"]) {
            /* define CAM_NR var */
            $AVREG_CAMS_NR[]  = (int)$_cam_nr;
            break;
        }
    }
}

if (isset($AVREG_CAMS_NR)) {
    print_syslog(
        LOG_NOTICE,
        sprintf(
            'received http notify, query string - "%s", AVReg\'s camera(s) number(s) - [%s]',
            $_SERVER['QUERY_STRING'],
            implode(',', $AVREG_CAMS_NR)
        )
    );
} else {
    print_syslog(LOG_ERR, sprintf('received http notify, query string - "%s"', $_SERVER['QUERY_STRING']));
}

/* include user scripts */
if (!empty($conf['on-http-notify'])) {
    @include ($conf['on-http-notify']);
}

echo "<h1>Received!</h1>\r\n</body></html>\r\n";
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
