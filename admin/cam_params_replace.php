<?php
/**
 * @file admin/cam_params_replace.php
 * @brief insert POST params to database
 * @return JSON { status => '', description => '' }
 */

require('../head-xhr.inc.php');
DENY($install_status); // FIXME TODO возвратит html
require('./params.inc.php');

if (!isset($cam_nr) || !settype($cam_nr, 'integer')) {
    $ret = array(
        'status' => 'error',
        'description' => 'cam_nr is not set',
    );
    echo json_encode($ret);
    exit();
}
/*
cam_nr:1
cam_name:Axis5014
video_src:rtsp
audio_src:null
InetCam_IP:************
InetCam_http_port:80
InetCam_USER:********
InetCam_PASSWD:********
InetCam_rtsp_port:
rtsp_play:/onvif-media/media.amp?profile=mobile_h264&sessiontimeout=60&streamtype=unicast
geometry:176x144
*/
switch($_SERVER['REQUEST_METHOD'])
{
    case 'GET':
        $the_request = &$_GET;
        break;
    case 'POST':
        $the_request = &$_POST;
        break;
    default:
        error_log("unsuported request method");
        header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
        exit();
}
$GCP_cams_list = $cam_nr;
$GCP_query_param_list = array();
foreach ($the_request as $key => $value) {
    if ($key != 'cam_nr' && $key != 'cam_name') {
        $GCP_query_param_list[] = $key;
    }
}
require('../lib/get_cams_params.inc.php');
//error_log(print_r($GCP_def_pars, TRUE));
//error_log(print_r($GCP_cams_params, TRUE));
//error_log(print_r($the_request, TRUE));

foreach ($GCP_query_param_list as $param_name) {
    $_value = isset($the_request[$param_name]) ? $the_request[$param_name] : '';

    if (is_array($_value)) {
        $value = implode(',', array_map('rawurldecode', $_value));
    } else {
        $value = trim(rawurldecode($_value));
    }

    $par_defs = find_param_defs($param_name);
    if ($GCP_cams_params[$cam_nr][$param_name] != $value &&
        $par_defs['def_val'] != $value &&
        CheckParVal($param_name, $value)) {
        CorrectParVal($param_name, $value);
        $param_value = ($value == '') ? null : html_entity_decode($value);
        $adb->replaceCamera('local', $cam_nr, $param_name, $param_value, $remote_addr, $login_user);

        print_syslog(
            LOG_NOTICE,
            sprintf(
                'cam[%s]: update param "%s", set new value "%s", old value "%s"',
                $cam_nr === 0 ? 'default' : (string)$cam_nr,
                $param_name,
                empty($param_value) ? "<empty>" : $param_value,
                empty($GCP_cams_params[$cam_nr][$param_name]) ? "<empty>" : $GCP_cams_params[$cam_nr][$param_name]
            )
        );
    }
}

$ret = array(
           'status' => 'done',
           'description' => 'success',
       );
echo json_encode($ret);
