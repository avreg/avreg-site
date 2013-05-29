<?php
/**
 * @file online/view.php
 * @brief Наблюдение с камер online
 * 
 * Формирует страницу с раскладкой камер для наблюдения в режиме online
 * 
 */
session_start();
if (isset($_SESSION['is_admin_mode']))
    unset($_SESSION['is_admin_mode']);

$NO_OB_END_FLUSH = true; // for setcookie()
$pageTitle = 'WebCam';
$body_style='overflow: hidden;  overflow-y: hidden !important; padding: 0; margin: 0; width: 100%; height: 100%;';
$css_links=array(
						'lib/js/jqModal.css',
						'online/online.css'
);
$USE_JQUERY = true;
$link_javascripts=array(
						'lib/js/jqModal.js', 
						'lib/js/jquery-ui-1.8.17.custom.min.js',
						'lib/js/jquery.mousewheel.min.js',
						'lib/js/jquery.aplayer.js',
						'lib/js/user_layouts.js',
						'lib/js/json2.js'
);

$body_addons='scroll="no"';
$ie6_quirks_mode = true;
$lang_file='_online.php';
require ('../head.inc.php');

//получение пользовательских раскладок
$clients_layouts = array();
if (isset($_COOKIE['layouts']))
{
    $layouts_cookie = $_COOKIE['layouts'];
    unset($_COOKIE['layouts']);
}
if (isset($layouts_cookie))
{
    $tmp = json_decode($layouts_cookie, true);
    $l_cook = $tmp;
    // Провераю корректность кодировки
    if (!$tmp)
    {
        $tmp = json_decode(iconv("CP1251", "UTF8", $layouts_cookie), true);
        $l_cook = $tmp;
    }
}

if (isset($tmp))
    foreach ($tmp as $client_mon_nr=>$l_val){
        $_data = array();
        foreach ($l_val as $par_name=>$par_data){
            $_data[$par_name]=$par_data;
        }
        $tmp_data = json_decode($_data['w']);
        $_data['wins'] = array();
        foreach ($tmp_data as $cell_nr=>$cell_data){
            $_data['wins'][$cell_nr]=$cell_data;
        }

        $clients_layouts[(int)$client_mon_nr] = array(
    // 			"BIND_MAC"=> "local",
    // 			"CHANGE_HOST"=> "anyhost",
                "CHANGE_USER"=>$_data['u'],
                "CHANGE_TIME"=>$_data['dd'],
                "MON_NR"=>$client_mon_nr,
                'MON_TYPE' => $_data['t'],
                'SHORT_NAME' => $_data['n'],
                'PRINT_CAM_NAME' => $_data['cn'],
                'PROPORTION' => $_data['p'],
                'RECONNECT_TOUT'=>$_data['rt'],
                'IS_DEFAULT' => $_data['d'],
                'WINS' => $_data['w']
                );
    }

//Загрузка установленных раскладок
$result = $adb->web_get_layouts($login_user);

//$result_ = $clients_layouts + $result;
$result_tmp = array_merge($clients_layouts, $result);
$result = $result_tmp;

//Если нет установленных раскладок
if(!count($result)) {
    echo "<script type=text/javascript>user_layouts.redirect('../admin/web_mon_addnew.php?storage=client&mon_nr=0&counter=1');</script>";
    exit();
}

//$curr_mon_nr = 0;
//foreach ($result as $key=>$value)
//{
//    $result[$key]['MON_NR_ACTUALLY'] = $curr_mon_nr;
//    $curr_mon_nr += 1;
//}
//print_r($result);

//Номер камеры по умолчанию
$def_cam = null;
$cur_layout = 0;
$is_clients_layout_default = false;
if(isset($_GET['layout_id']) ){
	//устанавливаем запрошенную раскладку
	foreach($result as $key=>&$value){
		if($value["MON_NR"]==$_GET['layout_id']){
			$def_cam = $value;
			$cur_layout = $value["MON_NR"];
		}
		if( !isset($value['RECONNECT_TOUT']) ){
			$value['RECONNECT_TOUT'] = isset($conf['reconnect-timeout'])? $conf['reconnect-timeout'] : 0 ;
		}
	}
}else{
	//Поиск раскладки по умолчанию и определение реконнект таймаута
	if (isset($l_cook))
        foreach ($l_cook as $key=>$value)
        {
            if ($l_cook[$key]['d'] == 'true')
            {
                $is_clients_layout_default = true;
                $cur_layout = $key;
                $def_cam = $value;
            }
            if( !isset($value['RECONNECT_TOUT']) ){
                $value['RECONNECT_TOUT'] = isset($conf['reconnect-timeout'])? $conf['reconnect-timeout'] : 0 ;
            }
        }
    if (!$is_clients_layout_default){
        foreach($result as $key=>&$value){
            if($value['IS_DEFAULT']!='0'){
                $def_cam = $value;
                if (isset($l_cook))
                    $cnt_client_lay = count($l_cook);
                else
                    $cnt_client_lay = 0;
                $cur_layout = $value["MON_NR"] + $cnt_client_lay;
            }
            if (!isset($value['RECONNECT_TOUT'])){
                $value['RECONNECT_TOUT'] = isset($conf['reconnect-timeout'])?$conf['reconnect-timeout']:5;
            }
        }
    }
}

//Если раскладка не определена - используем первую
if ($def_cam == null){
    $cur_layout = -1;
	$def_cam = $result[0];
}

//Определяем соответствующие параметры
// Если не установлена клиентская раскладка по умолчанию
if (!$is_clients_layout_default)
{
    $PrintCamNames =  $def_cam['PRINT_CAM_NAME'];
    $AspectRatio =  $def_cam['PROPORTION'];
    $mon_type = $def_cam['MON_TYPE'];

    $win_cams = json_decode($def_cam['WINS'], true);
}
else
    // Устанавливаем по умолчанию клиентскую раскладку
{
    $PrintCamNames =  $def_cam['n'];
    $AspectRatio =  $def_cam['p'];
    $mon_type = $def_cam['t'];
    $win_cams = json_decode($def_cam['w'], true);
}
if ( !isset($win_cams) || empty($win_cams))
die('should use "$win_cams" cgi param');

require('../admin/mon-type.inc.php');
if (!isset($mon_type) || empty($mon_type) || !array_key_exists($mon_type, $layouts_defs) ) 
   MYDIE("not set ot invalid \$mon_type=\"$mon_type\"",__FILE__,__LINE__);
$l_defs = &$layouts_defs[$mon_type];
$wins_nr = count($l_defs[3]);//определяет количество камер в раскладке

$def_cam_wins = '';
if (isset($def_cam['WINS']))
    $def_cam_wins = $def_cam['WINS'];
else
    $def_cam_wins = $def_cam['w'];

$_cookie_value = sprintf('%s-%u-%u-%u-%s', $def_cam_wins, // implode('.', $cams_in_wins),
isset($OpenInBlankPage),
isset($PrintCamNames),
isset($EnableReconnect),
isset($AspectRatio) ? $AspectRatio : 'calc' );

while (@ob_end_flush());

?>

<div id="canvas"
     style="position:relative; width:100%; height:0px; margin:0; padding:0;
           -ms-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; -webkit-box-sizing: border-box;">
</div>

<?php

echo "<script type='text/javascript'>\n";

if (isset($conf['aplayerConfig']) && !empty($conf['aplayerConfig']) && is_array($conf['aplayerConfig'])) {
	$res_conf = aplayer_configurate($conf['aplayerConfig']);
	print '$.aplayerConfiguration('.json_encode($res_conf).	');'."\n";
}

//период проверки состояния соединения (работает при отключенном реконнекте)
print "var online_check_period = {$conf['online-check-period']};\n";

//устанавливаем номер текущей раскладки
print "var cur_layout = '$cur_layout';\n";

//Передаем в JS список существующих раскладок
print "var layouts_list = ".json_encode($result).";\n";
//Передаем в JS возможные варианты раскладок
print "var layouts_defs = ".json_encode($layouts_defs).";\n";
//Передаем в JS возможные аспекты раскладок
print "var WellKnownAspects = ".json_encode($WellKnownAspects).";\n";



function calcAspectForGeo($w,$h) {
	
   foreach ($GLOBALS['WellKnownAspects'] as &$pair) {
      if ( 0 === $w % $pair[0] &&  0 === $h % $pair[1] ) {
         if ( $w/$pair[0] === $h/$pair[1] )
            return $pair;
      }
      if ( $h % $pair[0] &&  $w % $pair[1] ) {
         if ( $h/$pair[0] === $w/$pair[1] )
            return array($pair[1],$pair[0]);
      }
   }

   $ar = array($w,$h);
   $_stop = ($w>$h)?$h:$w;
   for ($i=1; $i<=$_stop; $i++) {
      if ( 0 === $w%$i && 0 === $h%$i ) {
         $ar[0] = $w/$i;
         $ar[1] = $h/$i;
      }
   }

   return $ar;
}

$major_win_cam_geo = null;
$major_win_nr = $l_defs[4] - 1;
$msie_addons_scripts=array();

$GCP_query_param_list=array('work', 'allow_networks', 'text_left', 'geometry', 'Hx2', 'ipcam_interface_url', 'fs_url_alt_1',
    'cell_url_alt_1', 'fs_url_alt_2', 'cell_url_alt_2');
if ( $operator_user )
	array_push($GCP_query_param_list, 'video_src', 'InetCam_IP');
require('../lib/get_cams_params.inc.php');




if ( $GCP_cams_nr == 0 )
   die('There are no available cameras!'); 

require_once('../lib/get_cam_url.php');

print 'var cams_subconf = '.json_encode($cams_subconf).";\n";

print 'var conf_debug = '.json_encode($conf['debug']).";\n";

//передаем базовую часть адреса в JS
print "var http_cam_location = '$http_cam_location' ;\n";

//Передаем инфо о пользователе в JS
print "var user_info_USER = ".json_encode($GLOBALS['user_info']['USER']).";\n";
print "var base64_encode_user_info_USER = '".base64_encode($GLOBALS['user_info']['USER'])."';\n";
print "var PHP_AUTH_PW = '".@$_SERVER['PHP_AUTH_PW']."';\n";

print 'var WINS_DEF = new MakeArray('.$wins_nr.')'."\n";

//Передаем JS параметры длосупных камер
print "var GCP_cams_params = ".json_encode($GCP_cams_params).";\n";

//Передаем JS параметр operator_user
print "var operator_user = ".json_encode($operator_user).";\n";

//передаем titles для контролов toolbara
print "var strToolbarControls = ".json_encode($strToolbarControls).";\n";

//передаем url-ы камер
$cams_urls = array();
foreach ($GCP_cams_params as $key=>$value){
    if (isset($GCP_cams_params[$key]['ipcam_interface_url']) &&
        !empty($GCP_cams_params[$key]['ipcam_interface_url'])){
        $ipcamUrl = $GCP_cams_params[$key]['ipcam_interface_url'];
    }else{
        $ipcamUrl = '';
    }

    $cu = array(
        "ipcam_interface_url" => $ipcamUrl,
        "avregd"=>get_cam_http_url($conf, $key, 'mjpeg', true),
        "cell_url_alt_1"=> checkUrlParam($GCP_cams_params[$key]['cell_url_alt_1'], $conf, $key, 'mjpeg'),
        // get_cam_alt_url($GCP_cams_params[$key]['cell_url_alt_1'], $key, true),
        "fs_url_alt_1"=> checkUrlParam($GCP_cams_params[$key]['fs_url_alt_1'], $conf, $key, 'mjpeg'),
        // get_cam_alt_url($GCP_cams_params[$key]['fs_url_alt_1'], $key, true),
        "cell_url_alt_2"=> checkUrlParam($GCP_cams_params[$key]['cell_url_alt_2'], $conf, $key, 'mjpeg'),
        //get_cam_alt_url( $GCP_cams_params[$key]['cell_url_alt_2'], $key, true),
        "fs_url_alt_2"=> checkUrlParam($GCP_cams_params[$key]['fs_url_alt_2'], $conf, $key, 'mjpeg')
        //get_cam_alt_url( $GCP_cams_params[$key]['fs_url_alt_2'], $key, true)
    );
    $cams_urls[$key]=$cu;}
print "var CAMS_URLS = ".json_encode($cams_urls).";\n\n\n\n\n";

//для js сопоставление камер и источников
$active_cams_srcs = array();

for ($win_nr=0; $win_nr<$wins_nr; $win_nr++)
{
	if ( empty($win_cams[$win_nr]) || !array_key_exists($win_cams[$win_nr][0], $GCP_cams_params)) { continue;  } /// DeviceACL
	$cam_nr = $win_cams[$win_nr][0];
	$temp[$win_nr] = $cam_nr;
 	
	list($width,$height) = explode('x', $GCP_cams_params[$cam_nr]['geometry']);
   settype($width, 'integer'); settype($height, 'integer');
   if ( empty($width)  )  $width  = 640;
   if ( empty($height) )  $height = 480;
   
   if ( !empty($GCP_cams_params[$cam_nr]['Hx2']) ) $height *= 2;

   if (is_null($major_win_cam_geo) || $major_win_nr === $win_nr )
      $major_win_cam_geo = array($width, $height);
   $l_wins = &$l_defs[3][$win_nr];

   //устанавливаем url камеры
   $active_cams_srcs[$win_nr]=array();
   switch($win_cams[$win_nr][1])
   {
   	case 0:
       case 1: //используем камеру avregd
           $cam_url = get_cam_http_url($conf, $cam_nr, 'mjpeg', true, $cams_urls);
           $active_cams_srcs[$win_nr]['type']='avregd';
           $active_cams_srcs[$win_nr]['cell']=$cam_url;
           $active_cams_srcs[$win_nr]['fs']=$cam_url;
           $stop_url = get_cam_http_url($conf, $cam_nr, 'jpeg', true);
           break;
       case 2: //используем источник "alt 1"
           // Проверяю есть ли альтернативная ссылка 1 (если нет, то генерирую ссылку на avregd)
           $cam_url = checkUrlParam($GCP_cams_params[$cam_nr]['cell_url_alt_1'], $conf, $key, 'mjpeg');
           if (!$cam_url){
               $cam_url = get_cam_http_url($conf, $cam_nr, 'mjpeg', true, $cams_urls);
           }
           $active_cams_srcs[$win_nr]['type']='alt_1';
           $active_cams_srcs[$win_nr]['cell']=$cam_url;
           $fsUrl = checkUrlParam($GCP_cams_params[$cam_nr]['fs_url_alt_1'], $conf, $key, 'mjpeg');
           if (!$fsUrl){
               $fsUrl = get_cam_http_url($conf, $cam_nr, 'mjpeg', true, $cams_urls);
           }
           $active_cams_srcs[$win_nr]['fs'] = $fsUrl;
           $stop_url = false;
           break;
       case 3: //используем камеру "alt 2"
           $cam_url = checkUrlParam($GCP_cams_params[$cam_nr]['cell_url_alt_2'], $conf, $key, 'mjpeg');

           if (!$cam_url){
               $cam_url = get_cam_http_url($conf, $cam_nr, 'mjpeg', true, $cams_urls);
           }
           $active_cams_srcs[$win_nr]['type']='alt_2';
           $active_cams_srcs[$win_nr]['cell']= $cam_url;
           $fsUrl = checkUrlParam($GCP_cams_params[$cam_nr]['fs_url_alt_2'], $conf, $key, 'mjpeg');
           if (!$fsUrl){
               $fsUrl = get_cam_http_url($conf, $cam_nr, 'mjpeg', true, $cams_urls);
           }
           $active_cams_srcs[$win_nr]['fs'] = $fsUrl;
           $stop_url = false;
           break;
   }
  // $cam_url= get_cam_alt_url($cam_url,$cam_nr, true);
    
   if ( $operator_user &&  (@$GCP_cams_params[$cam_nr]['video_src'] == 'rtsp' || @$GCP_cams_params[$cam_nr]['video_src'] == 'http') ){
      $netcam_host = '"' . $GCP_cams_params[$cam_nr]['InetCam_IP'] . '"';
   }
   else $netcam_host = 'null';

   printf(
'WINS_DEF[%d]={
   row: %u,
   col: %u,
   rowspan: %u,
   colspan: %u,
   main:  %u,
   cam: {
      nr:   %s,
      name: "%s",
      url:  "%s",
      orig_w: %u,
      orig_h: %u,
      netcam_host: %s,
   	  stop_url: "%s"
   }
};%s',
   $win_nr, 
   $l_wins[0], $l_wins[1],$l_wins[2],$l_wins[3],
   $l_defs[4]-1==$win_nr?1:0,
   $cam_nr, getCamName($GCP_cams_params[$cam_nr]['text_left']),
   $cam_url,
   $width, $height,
   $netcam_host,
   $stop_url,
   "\n" );

if ( $MSIE )
   $msie_addons_scripts[] = sprintf('<script for="cam%d" event="OnClick()">
   var amc = this;
if (amc.FullScreen) 
   amc.FullScreen=0;
else
   amc.FullScreen=1;
</script>', $cam_nr);
}

printf("var active_cams_srcs = %s;\n", json_encode($active_cams_srcs) );

printf("var FitToScreen = %s;\n", empty($FitToScreen) ? 'false' : 'true');

printf("var PrintCamNames = %s;\n", $PrintCamNames ? 'true'  : 'false');
printf("var EnableReconnect = %s;\n", empty($EnableReconnect) ? 'false' : 'true');
if ( empty($AspectRatio) ) {
   print 'var CamsAspectRatio = \'fs\';'."\n";
} else {
   if ( 0 === strpos($AspectRatio, 'calc') ) {
      $ar = calcAspectForGeo($major_win_cam_geo[0], $major_win_cam_geo[1]);
      printf("var CamsAspectRatio = { num: %d, den: %d };\n", $ar[0], $ar[1]);
   } else if (preg_match('/^(\d+):(\d+)$/', $AspectRatio, $m)) {
      printf("var CamsAspectRatio = { num: %d, den: %d };\n", $m[1], $m[2]);
   } else
      print 'var CamsAspectRatio = \'fs\';'."\n";
}


printf("var BorderLeft   = %u;\n", empty($BorderLeft)   ? 2 : $BorderLeft);
printf("var BorderRight  = %u;\n", empty($BorderRight)  ? 2 : $BorderRight);
printf("var BorderTop    = %u;\n", empty($BorderTop)    ? 2 : $BorderTop);
printf("var BorderBottom = %u;\n", empty($BorderBottom) ? 2 : $BorderBottom);

// $user_info config.inc.php
print 'var ___u="'.$user_info['USER']."\"\n";
if (empty($user_info['PASSWD']) /* задан пароль */)
    print 'var ___p="empty"'.";\n"; // нужно чтобы AMC не запрашивал пароль при пустом пароле
else
    print 'var ___p="'.@$_SERVER["PHP_AUTH_PW"]."\";\n";

print 'var ___abenc="'.base64_encode($user_info['USER'].':'.$_SERVER["PHP_AUTH_PW"])."\";\n";

/* other php layout_defs to javascript vars */

print "var WINS_NR = $wins_nr;\n";
print "var ROWS_NR = $l_defs[1];\n";
print "var COLS_NR = $l_defs[2];\n";

print "var REF_MAIN = ".(($install_user || $admin_user || $arch_user )? 'true':'false').";\n";

//Подключаем файл 
 readfile('view.js');

echo "</script>\n";


if ( !empty($msie_addons_scripts) || is_array($msie_addons_scripts) )  {
   foreach ($msie_addons_scripts as $value)
      print "$value\n";
}


require ('../foot.inc.php');
?>
