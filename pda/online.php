<?php
/**
 * @file pda/online.php
 * @brief
 */

$USE_JQUERY = true;

$pageTitle = sprintf('Камера №%u', $_GET['camera']);
// $body_onload='body_loaded();';
require('head_pda.inc.php');
require('../lib/get_cam_url.php');
require('../lib/get_cams_params.inc.php');

if (!isset($camera) || !settype($camera, 'int')) {
    die('should use "camera" cgi param');
}

$cams_params = get_cams_params(array(
    'work',
    'allow_networks',
    'text_left',
    'geometry',
    'Hx2'));
$cam_conf = & $cams_params[$camera];
$cam_name = $cam_conf['text_left']['v'];
list($w, $h) = sscanf($cam_conf['geometry']['v'], '%ux%u');
if ($cam_conf['Hx2']['v']) {
    $h *= 2;
}
$cam_url = "../lib/img_resize.php?camera=$camera&prop=false";

//масштаб изображений
$scale = 0;
if (isset($_COOKIE['scl'])) {
    $scale = $_COOKIE['scl'];
}
if (isset($_GET['scl'])) {
    $scale = $_GET['scl'];
}
require_once('scale.inc.php');

if (!isset($_COOKIE['sort_by']) || $_COOKIE['sort_by'] != 'heigth') {
    $thumb_sizes = get_resolutions($conf['pda_resolutions']);
} else {
    $thumb_sizes = get_resolutions($conf['pda_resolutions'], false);
}

if (empty($thumb_sizes)) {
    //если ничего в конфиге не определено
    $thumb_sizes = array(0 => array('w' => '160', 'h' => '80',));
}
if ($scale >= sizeof($thumb_sizes) - 1) {
    $scale = sizeof($thumb_sizes) - 1;
}

$img_scaled_width = $thumb_sizes[$scale]['w'];
$img_scaled_height = $thumb_sizes[$scale]['h'];

$isFs = 'false';
$reload = 'false';
if ($img_scaled_width == 'FS') {
    $img_scaled_width = isset($_GET['aw']) ? $_GET['aw'] : 0;
    $img_scaled_height = isset($_GET['ah']) ? $_GET['ah'] : 0;
    if ($img_scaled_width == 0) {
        $reload = 'true';
    }
    $isFs = 'true';
}

?>

<script type="text/javascript">
//переменные масштаба изображений
var isFs = <?php print  $isFs; ?>;
var reload = <?php print $reload; ?>;
var scale = <?php print $scale; ?>;
var requst_uri = <?php print '"' . $_SERVER['REQUEST_URI'] . '"' ; ?>;

function img_evt(e_id) {
    if ((typeof window.img_evt2).charAt(0) != 'u') {
        img_evt2(e_id);
    }
    else {
        IMG_EVT_OCCURED = e_id;
    }
}
</script>

<?php
if (!isset($refresh)) {
    //селект масштаба
    print "<div id='div_scl'>\n";
    show_select_resolution($thumb_sizes, $scale, $strScale['scale']);
    print "</div>\n";

    $refresh_img_a = array(
        0 => 'вручную',
        1 => '0,5 сек.',
        2 => '1 сек.',
        4 => '2 сек.',
        6 => '3 сек.'
    );

    if (isset($_SESSION['refresh'])) {
        $refresh = (int)$_SESSION['refresh'];
        if (!array_key_exists($refresh, $refresh_img_a)) {
            $refresh = 0;
        }
    } else {
        $refresh = 0;
    }

    printf(
        '<img id="viewport" class="cam_snapshot" src="%s&width=%s&height=%s" style="border: 1px solid; %s"
        alt="%s снапшот" onerror="img_evt(1);"  />',
        $cam_url,
        $img_scaled_width,
        $img_scaled_height,
        ($reload != 'false') ? 'display:none;' : '',
        $cam_name
    );
    ?>
    <div id="view_cam_"></div>

    <script type="text/javascript">

    $(function () {
        $('body').css({'overflow': 'hidden'});

        corr_h = $('#div_scl').height() + $('#rf_frm').height();
        set_full_screen();

    });
    </script>

    <form id="rf_frm" action="online.php" method="GET">
        <br>
        <input type="hidden" name='camera' value="<?php echo $camera; ?>">
        <input type="hidden" name='scl' value="<?php echo $scale; ?>">

        <div>
            Обновлять изображение:
            <?php print getSelectByAssocAr('refresh', $refresh_img_a, false, 1, 1, $refresh, false); ?>
        </div>
        <div>
            <input type="submit" id="btSubmit" value="<?php echo 'Наблюдать камеру'; ?>">
            &nbsp;<a href='./' title='<?php echo $strHome; ?>'>
                <?php echo $strHome; ?>
            </a>
        </div>
    </form>

    <?php

} else {

    /* смотрим детально и с обновлениями */
    $_SESSION['refresh'] = $refresh;
    $cam_url .= "&width=$img_scaled_width&height=$img_scaled_height";
    if (@isset($_GET['scale'])) {
        $cam_url .= "&scl=$_GET[scale]";
    }
    printf(
        '<img class="cam_snapshot" id="viewport" src="%s"
        alt="Загружается изображение с %s ..."
        border="1px"
        onclick="refresh_img();" onload="img_evt(0);" onerror="img_evt(1);" oabort="img_evt(2);">',
        $cam_url,
        $cam_name
    );

    ?>

    <script type="text/javascript">
    $(function () {
        $('body').css({'overflow': 'hidden'});
        CAM_INFO['url'] = $(IMG).attr('src');
    });

    </script>

    <?php
}
?>

<script type="text/javascript">
var refresh_mode = <?php echo (!isset($refresh) ? '-1' : $refresh) ?>;
var CAM_INFO = {
    'nr': <?php echo $camera; ?>,
    'name': '<?php echo $cam_name; ?>',
    'active': <?php echo ($cam_conf['work']['v'] && $cam_conf['allow_networks']['v']) ? 'true' : 'false' ?>,
    'width': <?php echo $w; ?>,
    'height': <?php echo $h; ?>,
    'url': '<?php echo $cam_url; ?>'
};

var IMG = document.getElementById('viewport'); // FIXME if isn't DOM ready?
var BTSUBMIT = document.getElementById('btSubmit');
var REFRESH = document.getElementById('refresh');

function refresh_img() {
    var now = new Date();
    var update_url = CAM_INFO['url'] + '&_=' + now.getTime(); // prevent local browser caching
    IMG.setAttribute('src', update_url);
}

function img_evt2(e_id) {
    if (typeof(tmr) != 'undefined') {
        clearTimeout(tmr);
    }

    switch (e_id) {
    case 0: // onload
        if (refresh_mode <= 0 /* manual refresh */) {
            return;
        }
        tmr = setTimeout('refresh_img();', refresh_mode * 500);
        break;
    case 1: // onerror
        IMG.setAttribute('alt', 'Ошибка загрузки изображения');
    case 2: // onabort
        if (e_id != 1) {
            IMG.setAttribute('alt', 'Загрузка изображения прервана пользователем');
        }
        IMG.style.color = 'Red';
        BTSUBMIT.setAttribute('disabled', true);
        REFRESH.setAttribute('disabled', true);
        break;
    default:
        alert('unknown event id ' + e_id);
    }
}
/* если img_evt() успел сработать до загрузки страницы FIXME правильней исп. ready или хотя бы body_onload */
if ((typeof IMG_EVT_OCCURED).charAt(0) != 'u') {
    img_evt2(IMG_EVT_OCCURED);
}

</script>

<?php

// tohtml($_SESSION);
require('../foot.inc.php');
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
