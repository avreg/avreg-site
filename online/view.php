<?php
/**
 * @file online/view.php
 * @brief Наблюдение с камер online
 *
 * Формирует страницу с раскладкой камер для наблюдения в режиме online
 *
 */
session_start();
if (isset($_SESSION['is_admin_mode'])) {
    unset($_SESSION['is_admin_mode']);
}

$NO_OB_END_FLUSH = true; // for setcookie()
$pageTitle = 'WebCam';
$body_style = 'overflow: hidden;  overflow-y: hidden !important; padding: 0; margin: 0; width: 100%; height: 100%;';
$css_links = array(
    'lib/js/third-party/jqModal.css',
    'lib/js/third-party/jquery-ui.css',
    'online/online.css'
);
$USE_JQUERY = true;
$link_javascripts = array(
    'lib/js/third-party/jqModal.js',
    'lib/js/third-party/jquery-ui-1.8.17.custom.min.js',
    'lib/js/third-party/jquery.mousewheel.min.js',
    'lib/js/jquery.aplayer.js',
    'lib/js/user_layouts.js',
    'lib/js/third-party/json2.js',
    'lib/js/misc_utils.js',
    'online/ptz/ptz.js'
);

$body_addons = 'scroll="no"';
$ie6_quirks_mode = true;
$IE_COMPAT='10';
$lang_file = '_online.php';
require('../head.inc.php');

//получение пользовательских раскладок
$user_layouts = array();
$cnt_client_lay = 0;
if (isset($_COOKIE['layouts'])) {
    $layouts_cookie = $_COOKIE['layouts'];
    unset($_COOKIE['layouts']);
}
if (isset($layouts_cookie)) {
    $tmp = json_decode($layouts_cookie, true);
    $l_cook = $tmp;
    // Провераю корректность кодировки
    if (!$tmp) {
        $tmp = json_decode(iconv("CP1251", "UTF8", $layouts_cookie), true);
        $l_cook = $tmp;
    }
}

if (isset($tmp)) {
    $cnt_client_lay = count($tmp);
    foreach ($tmp as $user_layout_nr => $l_val) {
        $_data = array();
        foreach ($l_val as $par_name => $par_data) {
            $_data[$par_name] = $par_data;
        }
        $tmp_data = json_decode($_data['w']);
        $_data['wins'] = array();
        foreach ($tmp_data as $cell_nr => $cell_data) {
            $_data['wins'][$cell_nr] = $cell_data;
        }

        $user_layouts[(int)$user_layout_nr] = array(
            // 			"BIND_MAC"=> "local",
            // 			"CHANGE_HOST"=> "anyhost",
            "CHANGE_USER" => $_data['u'],
            "CHANGE_TIME" => $_data['dd'],
            "MON_NR" => $user_layout_nr,
            'MON_TYPE' => $_data['t'],
            'SHORT_NAME' => $_data['n'],
            'PRINT_CAM_NAME' => $_data['cn'],
            'PROPORTION' => $_data['p'],
            'RECONNECT_TOUT' => $_data['rt'],
            'IS_DEFAULT' => $_data['d'],
            'WINS' => $_data['w']
        );
    }
}

//Загрузка установленных раскладок
$sys_layouts = $adb->webGetLayouts($login_user);
$layouts = array_merge($user_layouts, $sys_layouts);

//Если нет установленных раскладок
if (!count($layouts)) {
    echo "<script type=text/javascript>
        user_layouts.redirect('../admin/web_mon_addnew.php?storage=client&mon_nr=0&counter=1');</script>";
    exit();
}

// Текущий выбранный layout
$cur_layout = null;
$cur_layout_nr = 0;
$is_clients_layout_default = false;

if (isset($_GET['layout_id'])) {
    //устанавливаем запрошенную раскладку
    foreach ($layouts as $key => &$value) {
        if ($value["MON_NR"] == $_GET['layout_id']) {
            $cur_layout = $value;
            $cur_layout_nr = $value["MON_NR"];
            break;
        }
    }
} else {
    //Поиск раскладки по умолчанию
    if (isset($l_cook)) {
        foreach ($l_cook as $key => $value) {
            if ($l_cook[$key]['d'] == 'true') {
                $is_clients_layout_default = true;
                $cur_layout_nr = $key;
                $cur_layout = $value;
                break;
            }
        }
    }
    if (!$is_clients_layout_default) {
        foreach ($sys_layouts as $key => &$value) {
            if (!empty($value['IS_DEFAULT'])) {
                $cur_layout = $value;
                $cur_layout_nr = intval($value['MON_NR']);
                break;
            }
        }
    }
}

//Если раскладка не определена - используем первую
if ($cur_layout == null) {
    $cur_layout = $layouts[0];
    $cur_layout_nr = isset($cur_layout['MON_NR']) ? $cur_layout['MON_NR'] : -1;
}
if (!isset($cur_layout['RECONNECT_TOUT'])) {
    $cur_layout['RECONNECT_TOUT'] = isset($conf['reconnect-timeout']) ? $conf['reconnect-timeout'] : 0;
}
// error_log(print_r($cur_layout_nr, true));

//Определяем соответствующие параметры
// Если не установлена клиентская раскладка по умолчанию
if (!$is_clients_layout_default) {
    $PrintCamNames = $cur_layout['PRINT_CAM_NAME'];
    $AspectRatio = $cur_layout['PROPORTION'];
    $mon_type = $cur_layout['MON_TYPE'];
    $win_cams = json_decode($cur_layout['WINS'], true);
} else { // Устанавливаем по умолчанию клиентскую раскладку
    $PrintCamNames = $cur_layout['cn'];
    $AspectRatio = $cur_layout['p'];
    $mon_type = $cur_layout['t'];
    $win_cams = json_decode($cur_layout['w'], true);
}
if (!isset($win_cams) || empty($win_cams)) {
    die('should use "$win_cams" cgi param');
}

require('../admin/mon-type.inc.php');
if (!isset($mon_type) || empty($mon_type) || !array_key_exists($mon_type, $layouts_defs)) {
    MYDIE("not set ot invalid \$mon_type=\"$mon_type\"", __FILE__, __LINE__);
}
$l_defs = & $layouts_defs[$mon_type];
$wins_nr = count($l_defs[3]); //определяет количество камер в раскладке

$cur_layout_wins = '';
if (isset($cur_layout['WINS'])) {
    $cur_layout_wins = $cur_layout['WINS'];
} else {
    $cur_layout_wins = $cur_layout['w'];
}

$_cookie_value = sprintf(
    '%s-%u-%u-%u-%s',
    $cur_layout_wins, // implode('.', $cams_in_wins),
    isset($OpenInBlankPage),
    isset($PrintCamNames),
    isset($EnableReconnect),
    isset($AspectRatio) ? $AspectRatio : 'calc'
);

while (@ob_end_flush()) {
    ;
}
?>

<div id="canvas"
     style="position:relative; width:100%; height:0; margin:0; padding:0;
           -ms-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;
           -webkit-box-sizing: border-box;">
</div>

<?php

echo "<script type='text/javascript'>\n";

if (isset($conf['aplayerConfig']) && !empty($conf['aplayerConfig']) && is_array($conf['aplayerConfig'])) {
    $res_conf = aplayer_configurate($conf['aplayerConfig']);
    print '$.aplayerConfiguration(' . json_encode($res_conf) . ');' . "\n";
}

//период проверки состояния соединения (работает при отключенном реконнекте)
print "var online_check_period = {$conf['online-check-period']};\n";

//устанавливаем номер текущей раскладки
print "var cur_layout = '$cur_layout_nr';\n";

//Передаем в JS список существующих раскладок
print "var layouts_list = " . json_encode($layouts) . ";\n";
//Передаем в JS возможные варианты раскладок
print "var layouts_defs = " . json_encode($layouts_defs) . ";\n";
//Передаем в JS возможные аспекты раскладок
print "var WellKnownAspects = " . json_encode($WellKnownAspects) . ";\n";

function calcAspectForGeo($w, $h)
{

    foreach ($GLOBALS['WellKnownAspects'] as &$pair) {
        if (0 === $w % $pair[0] && 0 === $h % $pair[1]) {
            if ($w / $pair[0] === $h / $pair[1]) {
                return $pair;
            }
        }
        if ($h % $pair[0] && $w % $pair[1]) {
            if ($h / $pair[0] === $w / $pair[1]) {
                return array($pair[1], $pair[0]);
            }
        }
    }

    $ar = array($w, $h);
    $_stop = ($w > $h) ? $h : $w;
    for ($i = 1; $i <= $_stop; $i++) {
        if (0 === $w % $i && 0 === $h % $i) {
            $ar[0] = $w / $i;
            $ar[1] = $h / $i;
        }
    }

    return $ar;
}

$major_win_cam_geo = null;
$major_win_nr = $l_defs[4] - 1;
$msie_addons_scripts = array();

$GCP_query_param_list = array(
    'work',
    'video_src',
    'InetCam_IP',
    'InetCam_USER',
    'InetCam_PASSWD',
    'rtsp_play',
    'InetCam_rtsp_port',
    'InetCam_http_port',
    'V.http_get',
    'allow_networks',
    'text_left',
    'geometry',
    'Hx2',
    'ipcam_interface_url',
    'fs_url_alt_1',
    'cell_url_alt_1',
    'fs_url_alt_2',
    'cell_url_alt_2',
    'ptz'
);
require('../lib/get_cams_params.inc.php');

if ($GCP_cams_nr == 0) {
    die('There are no available cameras!');
}

require_once('../lib/get_cam_url.php');

print 'var cams_subconf = ' . json_encode($cams_subconf) . ";\n";

print 'var conf_debug = ' . json_encode($conf['debug']) . ";\n";

//передаем базовую часть адреса в JS
print "var http_cam_location = '$http_cam_location' ;\n";

//Передаем инфо о пользователе в JS
print "var user_info_USER = " . json_encode($GLOBALS['user_info']['USER']) . ";\n";
print "var base64_encode_user_info_USER = '" . base64_encode($GLOBALS['user_info']['USER']) . "';\n";
print "var PHP_AUTH_PW = '" . @$_SERVER['PHP_AUTH_PW'] . "';\n";

//Передаем JS параметр operator_user
print "var operator_user = " . json_encode($operator_user) . ";\n";

//передаем titles для контролов toolbara
print "var strToolbarControls = " . json_encode($strToolbarControls) . ";\n";

//для js сопоставление камер и источников
$WINS_DEF = array();
for ($win_nr = 0; $win_nr < $wins_nr; $win_nr++) {
    if (empty($win_cams[$win_nr]) || !array_key_exists($win_cams[$win_nr][0], $GCP_cams_params)) {
        continue;
    } /// DeviceACL
    $cam_nr = $win_cams[$win_nr][0];
    $temp[$win_nr] = $cam_nr;

    list($width, $height) = explode('x', $GCP_cams_params[$cam_nr]['geometry']);
    settype($width, 'integer');
    settype($height, 'integer');
    if (empty($width)) {
        $width = 640;
    }
    if (empty($height)) {
        $height = 480;
    }

    if (!empty($GCP_cams_params[$cam_nr]['Hx2'])) {
        $height *= 2;
    }

    if (is_null($major_win_cam_geo) || $major_win_nr === $win_nr) {
        $major_win_cam_geo = array($width, $height);
    }
    $l_wins = & $l_defs[3][$win_nr];

    //устанавливаем url камеры
    $cam_view_srcs = array();
    switch ($win_cams[$win_nr][1]) {
        case 0:
        case 1: //используем камеру avregd
            $cam_url = get_avregd_cam_url($conf, $cam_nr, 'mjpeg', true);
            $cam_view_srcs['type'] = 'avregd';
            $cam_view_srcs['cell'] = $cam_url;
            $cam_view_srcs['fs'] = $cam_url;
            $cam_view_srcs['stop_url'] = get_avregd_cam_url($conf, $cam_nr, 'jpeg', true);
            break;
        case 2: //используем источник "alt 1"
            // Проверяю есть ли альтернативная ссылка 1 (если нет, то генерирую ссылку на avregd)
            $cam_view_srcs['type'] = 'alt_1';
            $cam_url = get_alt_url(
                $conf,
                $cam_nr,
                $GCP_cams_params,
                $GCP_cams_params[$cam_nr]['cell_url_alt_1']
            );
            $cam_view_srcs['cell'] = $cam_url;
            $cam_view_srcs['fs'] = get_alt_url(
                $conf,
                $cam_nr,
                $GCP_cams_params,
                $GCP_cams_params[$cam_nr]['fs_url_alt_1']
            );
            $cam_view_srcs['stop_url'] = false;
            break;
        case 3: //используем камеру "alt 2"
            $cam_view_srcs['type'] = 'alt_2';
            $cam_url = get_alt_url(
                $conf,
                $cam_nr,
                $GCP_cams_params,
                $GCP_cams_params[$cam_nr]['cell_url_alt_2']
            );
            $cam_view_srcs['cell'] = $cam_url;
            $cam_view_srcs['fs'] = get_alt_url(
                $conf,
                $cam_nr,
                $GCP_cams_params,
                $GCP_cams_params[$cam_nr]['fs_url_alt_2']
            );
            $cam_view_srcs['stop_url'] = false;
            break;
    }

    $direct_link = null;
    if ($operator_user && (@$GCP_cams_params[$cam_nr]['video_src'] == 'rtsp'
        || @$GCP_cams_params[$cam_nr]['video_src'] == 'http')) {
        if (@empty($GCP_cams_params[$cam_nr]['ipcam_interface_url'])) {
            $direct_link = 'http://' . $GCP_cams_params[$cam_nr]['InetCam_IP'];
        } else {
            $direct_link = $GCP_cams_params[$cam_nr]['ipcam_interface_url'];
        }
    }

    if ($operator_user && !empty($GCP_cams_params[$cam_nr]['ptz'])) {
        $ptz_handler = $GCP_cams_params[$cam_nr]['ptz'];
    } else {
        $ptz_handler = null;
    }

    $WINS_DEF[$win_nr] = array(
        'row' => $l_wins[0],
        'col' => $l_wins[1],
        'rowspan' => $l_wins[2],
        'colspan' => $l_wins[3],
        'main' => (($l_defs[4] - 1) == $win_nr ? 1 : 0),
        'cam' => array(
            'nr' => $cam_nr,
            'name' => getCamName($GCP_cams_params[$cam_nr]['text_left']),
            'urls' =>  $cam_view_srcs,
            'orig_w' => $width,
            'orig_h' => $height,
            'direct_link' => $direct_link,
            'ptz' => $ptz_handler,
        )
    );

    if ($MSIE) {
        $msie_addons_scripts[] = sprintf(
            '<script for="cam%d" event="OnClick()">
               var amc = this;
            if (amc.FullScreen)
               amc.FullScreen=0;
            else
               amc.FullScreen=1;
            </script>',
            $cam_nr
        );
    }
}

printf("var WINS_DEF = %s;\n", json_encode($WINS_DEF));

printf("var FitToScreen = %s;\n", empty($FitToScreen) ? 'false' : 'true');

printf("var PrintCamNames = %s;\n", $PrintCamNames ? 'true' : 'false');
printf("var EnableReconnect = %s;\n", empty($EnableReconnect) ? 'false' : 'true');
if (empty($AspectRatio)) {
    print 'var CamsAspectRatio = \'fs\';' . "\n";
} else {
    if (0 === strpos($AspectRatio, 'calc')) {
        $ar = calcAspectForGeo($major_win_cam_geo[0], $major_win_cam_geo[1]);
        printf("var CamsAspectRatio = { num: %d, den: %d };\n", $ar[0], $ar[1]);
    } else {
        if (preg_match('/^(\d+):(\d+)$/', $AspectRatio, $m)) {
            printf("var CamsAspectRatio = { num: %d, den: %d };\n", $m[1], $m[2]);
        } else {
            print 'var CamsAspectRatio = \'fs\';' . "\n";
        }
    }
}

// $user_info config.inc.php
print 'var ___u="' . $user_info['USER'] . "\"\n";
if (empty($user_info['PASSWD']) /* задан пароль */) {
    print 'var ___p="empty"' . ";\n";
} else { // нужно чтобы AMC не запрашивал пароль при пустом пароле
    print 'var ___p="' . @$_SERVER["PHP_AUTH_PW"] . "\";\n";
}

print 'var ___abenc="' . base64_encode($user_info['USER'] . ':' . $_SERVER["PHP_AUTH_PW"]) . "\";\n";

/* other php layout_defs to javascript vars */

print "var WINS_NR = $wins_nr;\n";
print "var ROWS_NR = $l_defs[1];\n";
print "var COLS_NR = $l_defs[2];\n";

print "var REF_MAIN = " . (($install_user || $admin_user || $arch_user) ? 'true' : 'false') . ";\n";

//Подключаем файл
readfile('view.js');

echo "</script>\n";

if (!empty($msie_addons_scripts) || is_array($msie_addons_scripts)) {
    foreach ($msie_addons_scripts as $value) {
        print "$value\n";
    }
}

require('../foot.inc.php');
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
