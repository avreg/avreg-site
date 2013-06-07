<?php
/**
 * @file on-http-notify.php
 * @brief Генерирует уведомление
 *
 */


/* need for auth */
require_once('lib/config.inc.php');

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
$AVREG_CAMS_NR = array(); /* Массив номеров камер, найденных по InetCam_IP
                           Массив, потому что для ip-видеосервера
                           мы можем найти несколько камер с одним InetCam_IP */
$GCP_query_param_list = array('video_src', 'text_left', 'InetCam_IP');
require('lib/get_cams_params.inc.php');
if (isset($GCP_cams_params) && is_array($GCP_cams_params)) {
	reset($GCP_cams_params);
	while (list($_cam_nr, $CAM_PARAMS) = each($GCP_cams_params)) {
		if (($CAM_PARAMS['video_src'] === 'rtsp' || $CAM_PARAMS['video_src'] === 'http') &&
			$CAM_PARAMS['InetCam_IP'] === $_SERVER["REMOTE_ADDR"]
		) {
			/* define CAM_NR var */
			$AVREG_CAMS_NR[] = (int)$_cam_nr;
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
	@include($conf['on-http-notify']);
}

echo "<h1>Received!</h1>\r\n</body></html>\r\n";
?>
