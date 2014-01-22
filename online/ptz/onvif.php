<?php
/**
 * @file online/ptz/onvif.php
 * @brief onvif-based PTZ handler
 */

$pageTitle = 'Onvif PTZ';
require('common.inc.php');

if (empty($cam_nr)) {
    die('cam_nr is empty');
}
$GCP_cams_list = "$cam_nr";
$GCP_query_param_list = array(
    'text_left',
    'InetCam_IP',
    'InetCam_http_port',
    'InetCam_USER',
    'InetCam_PASSWD'
);
require('../../lib/get_cams_params.inc.php');
$cam_http_params = array_merge($GCP_def_pars, $GCP_cams_params[$cam_nr]);
/*
$cam_http_params example
array(5) {
  ["text_left"]=> string(30) "Камера3-эмулятор"
  ["InetCam_IP"]=> string(9) "127.0.0.1"
  ["InetCam_USER"]=> NULL
  ["InetCam_PASSWD"]=> NULL
  ["InetCam_http_port"]=> string(5) "60001",
}
*/

?>

<div class="ptz_area_right">
    <div style="margin-top: 10px; margin-left: 20px;">
        <p>TILT</p>
        <div style="margin: 1em 0;" id="ptzTiltSlider"></div>
    </div>
</div>

<div class="ptz_area_bottom">
    <div style="padding: 5px; overflow: hidden;">
        <div style="float: left; width: 20%">
            <p>ZOOM</p>

            <p>PAN</p>
        </div>
        <div style="float: right; width: 80%">
            <div style="margin: 1em 0;" id="ptzZoomSlider"></div>
            <div style="margin: 1em 0;" id="ptzPanSlider"></div>
        </div>
    </div>
</div>
