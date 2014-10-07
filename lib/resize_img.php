<?php

// TODO кэшировать картинки, т.к. имена уникальны
// FIXME почему не используется img_resize.php

require('/etc/avreg/site-defaults.php');
setlocale(LC_ALL, 'C');

//Загрузка исходнгого изображения
$src_jpeg_url = $_GET['url'];

// пробуем открыть файл для чтения
if (fopen($src_jpeg_url, "r")) {

    $path_info = pathinfo($src_jpeg_url);
    switch (strtolower($path_info['extension'])) {
        case 'jpeg':
        case 'jpg':
            $image_type = IMG_JPEG;
            break;
        default:
            die("only jpeg supported");
    }
    $src_gd = @imagecreatefromjpeg($src_jpeg_url);

    //Определение размеров исходного изображения
    $im_width = imageSX($src_gd);
    $im_height = imageSY($src_gd);

    //пропорции изображения
    $im_proportion = $im_width / $im_height;

    //размеры отображения
    $w = isset($_GET['w']) ? (int)$_GET['w'] : 0;
    $h = isset($_GET['h']) ? (int)$_GET['h'] : 0;

    //если передан параметр один к одному
    if ($w == '1:1' || $h == '1:1') {
        $w = $im_width;
        $h = $im_height;
    }

    if ($w == 0 && $h == 0) {
        $w = $im_width;
        $h = $im_height;
    } elseif ($w == 0) {
        $w = $h * $im_proportion;
    } elseif ($h == 0) {
        $h = $w / $im_proportion;
    }

    //resulted sizes
    $new_width = $w;
    $new_height = $h;

    $saveProp = isset($_GET['prop']) ? $_GET['prop'] : true;
    //режим сохранять пропорции?
    if ($saveProp == 'true') {
        $el_proportion = $w / $h;

        if ($im_proportion > $el_proportion) {
            $new_height = $w / $im_proportion;
        } else {
            $new_width = $h * $im_proportion;
        }
    }

    // resize
    $dst_gd = imagecreatetruecolor($new_width, $new_height);
    imagecolorallocate($src_gd, 0xFF, 0xFF, 0xFF);
    ImageCopyResized($dst_gd, $src_gd, 0, 0, 0, 0, $new_width, $new_height, $im_width, $im_height);

    //output
    header("Content-Type: image/jpeg");
    Imagejpeg($dst_gd, null, 80); // quality 80
    ImageDestroy($src_gd);
    ImageDestroy($dst_gd);

    //если файл не найден
} else {
    // TODO отдать статическую картинку
    //изображение банера - "file not found"
    $string1 = "IMAGE NOT FOUND";
    $w = 200;
    $h = 120;
    $src_gd = imagecreatetruecolor($w, $h);
    $orange = imagecolorallocate($src_gd, 220, 210, 60);
    $px = (imagesx($src_gd) - 8.5 * strlen($string1)) / 2;
    $py = (imagesy($src_gd)) / 2.5;
    $fs = 5;
    imagestring($src_gd, $fs, $px, $py, $string1, $orange);

    //Определение размеров исходного изображения
    $im_width = imageSX($src_gd);
    $im_height = imageSY($src_gd);

    //размеры отображения
    $w = $_GET['w'];
    $h = $_GET['h'];

    //resulted sizes
    $new_width = $w;
    $new_height = $h;

    $saveProp = $_GET['prop'];
    //режим сохранять пропорции?
    if ($saveProp == 'true') {
        $im_proportion = $im_width / $im_height;
        $el_proportion = $w / $h;

        if ($im_proportion > $el_proportion) {
            $new_height = $w / $im_proportion;
        } else {
            $new_width = $h * $im_proportion;
        }
    }

    // resize
    $dst_gd = imagecreatetruecolor($new_width, $new_height);
    imagecolorallocate($src_gd, 0xFF, 0xFF, 0xFF);
    ImageCopyResized($dst_gd, $src_gd, 0, 0, 0, 0, $new_width, $new_height, $im_width, $im_height);

    //output
    header("Content-Type: image/jpeg");
    Imagejpeg($dst_gd, null, 80); // quality 80
    ImageDestroy($src_gd);
    ImageDestroy($dst_gd);
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
