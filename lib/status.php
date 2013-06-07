<?php


if (isset($_GET['cams'])) {
	$cams = explode(',', trim($_GET['cams']));
} else {
	//Получить все камеры
	$cams = array(1, 2, 3, 4, 5);
}

$subsytems = array();
if (isset($_GET['subsytems'])) {
	$subsytems = explode(',', trim($_GET['subsytems']));
} else {
	$subsytems = explode(',', trim('capture,record,motion,client'));
}

$subscribe = isset($_GET['subscribe']) ? true : false;

//Если ждем изменения статусов камер
if ($subscribe) {
	$timeout = 30;
	$start = time();
	while ((time() - $start) < $timeout) {
		//if(IS_STATUSES_CHANGED) return status; 
		sleep(1);
	}
}


$status = array("cams" => array());

foreach ($cams as $cam_nr) {
	foreach ($subsytems as $subsys) {
		$status['cams'][$cam_nr][$subsys] = get_subsystem_value($cam_nr, $subsys);
	}
}

echo json_encode($status);


function get_subsystem_value($cam_nr, $subsys)
{
	switch ($subsys) {
		case 'capture':
			return get_capture_status($cam_nr);
		case 'record':
			return get_record_status($cam_nr);
		case 'motion':
			return get_motion_status($cam_nr);
		case 'client':
			return get_client_status($cam_nr);
		default :
			return 'unknown subsystem';
	}
}


function get_capture_status($cam_nr)
{
	return array(
		"status" => true,
		"when" => time(),
		"video" => true,
		"audio" => true,
		"url" => "rtsp://axis/....",
	);

}

function get_record_status($cam_nr)
{
	return array(
		"status" => true,
		"when" => time(),
		"who" => array(2, "motion detector"),
		"rec_id" => 3,
		"video_file" => "2011-03/10/06-Axis_M1031/next0004/18_34_42.avi",
		"audio_file" => "2011-03/10/06-Axis_M1031/next0004/18_34_43.mp4"
	);
}

function get_motion_status($cam_nr)
{
	return array(
		"status" => true,
		"when" => time(),
		"video" => true,
		"audio" => true,
		"url" => "rtsp://axis/....",
	);
}

function get_client_status($cam_nr)
{
	return array(
		"status" => true,
		"when" => time(),
		"video" => true,
		"audio" => true,
		"url" => "rtsp://axis/....",
	);
}


?>