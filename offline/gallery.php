<?php
/**
 * @file offline/gallery.php
 * @brief Загрузка главной страницы галереи для просмотра записей
 *
 * Выполняется:
 * <ul>
 * <li>подключение стилей
 * <li>подключение js-скриптов
 * <li>загрузка страницы галереи
 * <li>загрузка конфигурации
 * <li>подключение к БД
 * <li>загрузка параметров камер
 * <li>инициализация экземпляра класса Gallery
 * </ul>
 *
 * */

error_reporting(E_ALL);
ini_set('display_errors', 0);

function get_recorded_cameras()
{
    $ret = array();
    $cams = get_cams_params(array(
        'work',
        'text_left',
        'rec_mode',
        'Hx2'));
    foreach ($cams as $__cam_nr => $_opt) {
        if ($__cam_nr <= 0) {
            continue;
        }
        if (((int)$_opt['work']['v']) > 0 && ((int)$_opt['rec_mode']['v']) > 0) {
            $ret_a[$__cam_nr] = $_opt;
        }
    }
    return $ret_a;
}

if (!isset($_POST['method']) && !isset($_GET['method'])) {
    /// Загрузка главной страницы галереи
    $pageTitle = 'gallery_title';

    $IE_COMPAT='10';
    $ie6_quirks_mode = true;

    /// Подключение стилей
    if (preg_match('/(msie|trident)/i', $_SERVER['HTTP_USER_AGENT'])) {
        //для MSIE
        $main_css = 'offline/gallery/css/ie_main.css';
    } else {
        $main_css = 'offline/gallery/css/main.css';
    }

    $css_links = array(
        'offline/gallery/css/html5reset-1.6.1.css',
        $main_css,
        'lib/js/third-party/jquery-ui-1.8.17.custom.css',
        'offline/gallery/css/tooltip.css'
    );
    $USE_JQUERY = true;

    /// Подключение js скриптов
    $link_javascripts = array(
        'lib/js/third-party/jquery-ui-1.8.17.custom.min.js',
        'lib/js/third-party/jquery.mousewheel.min.js',
        'lib/js/vlcControl.js',
        'lib/js/jquery.aplayer.js',
        'offline/gallery/js/third-party/jquery.jstree.js',
        'offline/gallery/js/third-party/jquery.scrollTo-min.js',
        'offline/gallery/js/jquery.checkbox.js',
        'offline/gallery/js/jquery.tooltip.js',
        'offline/gallery/js/main.js',
        'lib/js/third-party/json2.js',
        'lib/js/third-party/base64utf.js'
    );
    require_once('../head.inc.php');
    require_once('../lib/get_cams_params.inc.php');
   
    $REC_CAMS_PARAMS = get_recorded_cameras();
    if (count($REC_CAMS_PARAMS) <= 0) {
        echo "<div class='error'>$strNotRecordableCams</div>\n";
        print_go_back();
        require_once('../foot.inc.php');
        die(1);
    }
    $REC_CAMS = array_keys($REC_CAMS_PARAMS);
    $cookies = isset($_COOKIE['gallery']) ? (array)json_decode(base64_decode($_COOKIE['gallery'])) : array();

    /// Подключение самой страницы галереи
    require_once('gallery/index.php');
    require_once('../foot.inc.php');
} else {
    /// Ответ аякс запроса
    require_once('../lib/config.inc.php');
    require_once('../lib/adb.php');
    require_once('../lib/get_cams_params.inc.php');
   
    $REC_CAMS_PARAMS = get_recorded_cameras();
    if (count($REC_CAMS_PARAMS) <= 0) {
        die('There are no available recordable cameras!');
    }
    $REC_CAMS = array_keys($REC_CAMS_PARAMS);

    require_once 'gallery/memcache.php';
    require_once('gallery/gallery.php');

    /// Инициализация класа галереи
    $params = !empty($_POST) ? $_POST : $_GET;
    if ($conf['debug']) {
        error_log('new \Avreg\Gallery(' . print_r($params, true) . ')');
    }
    $gallery = new \Avreg\Gallery($params);

    // Возврат ответа запроса
    $gallery->printResult();
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
