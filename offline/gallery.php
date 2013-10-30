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
ini_set('display_errors', 1);

if (!isset($_POST['method']) && !isset($_GET['method'])) {
    /// Загрузка главной страницы галереи
    $pageTitle = 'gallery_title';
    //$USE_JQUERY = true;
    $ie6_quirks_mode = true;

    /// Подключение стилей
    if (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        //для MSIE
        $main_css = 'offline/gallery/css/ie_main.css';
    } else {
        $main_css = 'offline/gallery/css/main.css';
    }

    $css_links = array(
        'offline/gallery/css/html5reset-1.6.1.css',
        $main_css,
        'offline/gallery/css/jquery-ui-1.8.17.custom.css',
        'offline/gallery/css/tooltip.css'
    );
    $USE_JQUERY = true;

    /// Подключение js скриптов
    $link_javascripts = array(
        'lib/js/jquery-ui-1.8.17.custom.min.js',
        'lib/js/jquery.mousewheel.min.js',
        'lib/js/vlcControl.js',
        'lib/js/jquery.aplayer.js',
        'offline/gallery/js/jquery.jstree.js',
        'offline/gallery/js/jquery.scrollTo-min.js',
        'offline/gallery/js/jquery.checkbox.js',
        'offline/gallery/js/jquery.tooltip.js',
        'offline/gallery/js/main.js',
        'lib/js/json2.js'
    );
    require_once('../head.inc.php');
    $GCP_query_param_list = array('text_left', 'Hx2');
    require('../lib/get_cams_params.inc.php');
    if ($GCP_cams_nr == 0) {
        die('There are no available cameras!');
    }
    $cookies = isset($_COOKIE['gallery']) ? (array)json_decode(base64_decode($_COOKIE['gallery'])) : array();
    /// Подключение самой страницы галереи
    require_once('gallery/index.php');
    require_once('../foot.inc.php');
} else {
    /// Ответ аякс запроса
    require_once('../lib/config.inc.php');

    require_once('../lib/adb.php');

    $GCP_query_param_list = array('text_left', 'Hx2');
    require('../lib/get_cams_params.inc.php');
    if ($GCP_cams_nr == 0) {
        die('There are no available cameras!');
    }
    require_once 'gallery/memcache.php';
    require_once('gallery/gallery.php');
    /// Инициализация класа галереи
    $params = !empty($_POST) ? $_POST : $_GET;
    $gallery = new Gallery($params);
    // Возврат ответа запроса
    $gallery->print_result();
}
?>
