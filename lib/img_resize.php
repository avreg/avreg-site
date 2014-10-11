<?php
/**
 *
 * @file lib/img_resize.php
 * @brief Изменение размеров изображения
 *
 */
require_once('../lib/config.inc.php');
setlocale(LC_ALL, 'C');
// TODO  rotate live image

/// разрешить ресайз только локально
$local = true; /* local or http only supported */
/**
 *
 * Функция отправляющая заголовки о 412 ошибке
 * @param string $msg сообщение
 */
function die412($msg)
{
    header("$_SERVER[SERVER_PROTOCOL] 412 Precondition failed");
    echo $msg;
    exit;
}

/**
 *
 * Функция отправляющая заголовки о 403 ошибке
 * @param string $msg сообщение
 */
function die403($msg)
{
    header("$_SERVER[SERVER_PROTOCOL] 403 Forbidden");
    echo $msg;
    exit;
}

/**
 *
 * Функция отправляющая заголовки о 404 ошибке
 * @param string $msg сообщение
 */
function die404($msg)
{
    header("$_SERVER[SERVER_PROTOCOL] 404 Not found");
    echo $msg;
    exit;
}

/**
 *
 * Функция отправляющая заголовки о 500 ошибке
 * @param string $msg сообщение
 */
function die500($msg)
{
    header("$_SERVER[SERVER_PROTOCOL] 500 Server Error");
    echo $msg;
    exit;
}

if (empty($_REQUEST['file']) && !isset($_REQUEST['camera'])) {
    die412("couldn't set either \"file\" or \"camera\" param");
}
/// текущее время
$now = time();
/// максимальный ресайз
$max_age = & $conf['pda-thumb-image-max-age'];
/// кеш изображения
$etag = null;
/// ссылка на изображение
$img_uri = '';
/// файл изображения
$img_file_stat = false;
/// локальный просмотр
$is_local = !empty($_REQUEST['file']);
if ($is_local) {
    /* get the local file */
    $img_uri = $conf['storage-dir'] . '/' . $_REQUEST['file'];
    $etag = sha1($img_uri);
    $img_file_stat = stat($img_uri);
    if ($img_file_stat === false) {
        if (file_exists($img_uri)) {
            die403("image file \"$img_uri\" cannot be read: $last_err[message]");
        } else {
            die404("image file \"$img_uri\" not found");
        }
    }

    /* проверяем If-Modified-Since и If-None-Match: Etag */
    $cached_mtime = null; /* empty($cached_mtime) - признак отсутствия/ошибки_парсинга If-Modified-Since */
    $cached_etag = null; /* empty($cached_etag) - признак отсутвия If-None-Match */
    if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $cached_mtime = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        if ($cached_mtime < 0) {
            $cached_mtime = null;
        }
    }
    if (!empty($_SERVER['HTTP_IF_NONE_MATCH'])) {
        $cached_etag = trim($_SERVER['HTTP_IF_NONE_MATCH'], "\"'\t\n\r ");
    }

    if ((!empty($cached_etag)  and (($cached_etag === $etag) or ($cached_etag === '*'))) or
        (!empty($cached_mtime) and (($img_file_stat['mtime'] <= $cached_mtime) and ($now > $cached_mtime)))
    ) {
        /* предполагаем что если клиент и спрашивает одним из
         * то, у него в кеше есть уже файл и если всё норм. то отв. 304 */
        header("$_SERVER[SERVER_PROTOCOL] 304 Not Modified");
        exit;
    }

    $path_info = pathinfo($img_uri);
    switch (strtolower($path_info['extension'])) {
        case 'jpeg':
        case 'jpg':
            $image_type = IMG_JPEG;
            break;
        case 'png':
            $image_type = IMG_PNG;
            break;
        default:
            die("only jpeg and png supported");
    }
    $gd = @imagecreatefromjpeg($img_uri);
} else {
    /* live camera image over http:// */
    $cam_nr = (int)$_REQUEST['camera'];
    require_once('../lib/get_cam_url.php');
    $img_uri = preg_replace(
        '%http(s)?://([^:/]+)([:/])%', /* TODO ipv6 */
        'http://127.0.0.1$3',
        get_avregd_cam_url($conf, $cam_nr, 'jpeg', true)
    );
    $jpeg_data = file_get_contents($img_uri, false);
    if ($jpeg_data === false) {
        $last_err = error_get_last();
        die500("image file \"$img_uri\" cannot be read: $last_err[message]");
    }
    $image_type = IMG_JPEG;
    $gd = imagecreatefromstring($jpeg_data);
    if (!$gd) {
        $last_err = error_get_last();
        die500("image file \"$img_uri\": $last_err[message]");
    }
}

/// ширина изображения
$width_src = imagesx($gd);
/// высота изображения
$height_src = imagesy($gd);

//-->>

//пропорции изображения
$im_proportion = $width_src / $height_src;

//размеры отображения
$w = isset($_GET['width']) ? (int)$_GET['width'] : 0;
$h = isset($_GET['height']) ? (int)$_GET['height'] : 0;

//если передан параметр один к одному
if ($w == '1:1' || $h == '1:1') {
    $w = $width_src;
    $h = $height_src;
}

if ($w == 0 && $h == 0) {
    $w = $width_src;
    $h = $height_src;
} elseif ($w == 0) {
    $w = $h * $im_proportion;
} elseif ($h == 0) {
    $h = $w / $im_proportion;
}

// //resulted sizes
$new_width = $w;
$new_height = $h;

$saveProp = isset($_GET['prop']) ? $_GET['prop'] : true;
//режим сохранять пропорции?
if ($saveProp == 'true') {
    $im_proportion = $width_src / $height_src;
    $el_proportion = $w / $h;

    if ($im_proportion > $el_proportion) {
        $new_height = round($w / $im_proportion);
    } else {
        $new_width = round($h * $im_proportion);
    }
}

/// новое изображение
$thumb = imagecreatetruecolor($new_width, $new_height);
// Resize
imagecopyresized($thumb, $gd, 0, 0, 0, 0, $new_width, $new_height, $width_src, $height_src);
//-->>

if ($is_local) {
    /* включаем агрессивное кеширование */
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $img_file_stat['mtime']) . ' GMT');
    header('Expires: ' . gmdate('D, d M Y H:i:s', $now + $max_age) . ' GMT');
    header("Etag: \"$etag\"");
    header("Cache-Control: private, max-age=$max_age");
} else {
    /* live image отключаем вовсе кеширование */
    $gmt_now = gmdate('D, d M Y H:i:s', $now) . ' GMT';
    header('Last-Modified: ' . $gmt_now);
    header('Expires: ' . $gmt_now);
    header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
    header('Pragma: no-cache'); // HTTP/1.0
}
header("Content-Type: " . image_type_to_mime_type($image_type));

switch ($image_type) {
    case IMG_JPEG:
        imagejpeg($thumb, null, $conf['pda-jpeg-quality']);
        break;
    case IMG_PNG:
        imagepng($thumb);
        break;
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
