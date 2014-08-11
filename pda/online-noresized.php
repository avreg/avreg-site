<?php
/**
 * @file pda/online-noresized.php
 * @brief
 */
$pageTitle = sprintf('Камера №%u', $_GET['camera']);
// $body_onload='body_loaded();';
require('head_pda.inc.php');

if (!isset($camera) || !settype($camera, 'int')) {
    die('should use "camera" cgi param');
}

require_once('../lib/get_cam_url.php');

$GCP_query_param_list = array('work', 'allow_networks', 'text_left', 'geometry', 'Hx2');
require_once('../lib/get_cams_params.inc.php');
$cam_conf = & $GCP_cams_params[$camera];
$cam_name = $cam_conf['text_left'];
list($w, $h) = sscanf($cam_conf['geometry'], '%ux%u');
if ($cam_conf['Hx2']) {
    $h *= 2;
}
$cam_url = get_avregd_cam_url($conf, $camera, 'jpeg', true);

if (!isset($refresh)) {
    printf(
        '<IMG id="viewport" src="%s" height="160" border="1px" alt="%s снапшот" onerror="img_evt(1);" />',
        $cam_url,
        $cam_name
    );
    $refresh_img_a = array(
        -1 => 'вручную',
        0 => 'непрерывно',
        1 => '1 сек.',
        2 => '2 сек.',
        3 => '3 сек.'
    );
    ?>
    <br><br>
    <form action="online.php" method="GET">
        <input type="hidden" name='camera' value="<?php echo $camera; ?>">
        Обновлять изображение:
        <?php print getSelectByAssocAr('refresh', $refresh_img_a, false, 1, 1, 0, false); ?>
        <br><br>
        <input type="submit" id="btSubmit" value="<?php echo 'Наблюдать камеру'; ?>">
    </form>
    <?php
    print "<br><div><a href='./' title='$strHome'>$strHome</a></div>\n";
} else {
    printf(
        '<IMG id="viewport" src="%s"
              alt="Загружается изображение с %s ..."
              border="1px"
              onclick="refresh_img();" onload="img_evt(0);" onerror="img_evt(1);" oabort="img_evt(2);">',
        $cam_url,
        $cam_name
    );
}
?>

<script type="text/javascript">
    var refresh_mode = <?php echo (!isset($refresh) ? '-1' : $refresh) ?>;
    var CAM_INFO = {
        'nr': <?php echo $camera; ?>,
        'name': '<?php echo $cam_name; ?>',
        'active': <?php echo ($cam_conf['work'] && $cam_conf['allow_networks']) ? 'true' : 'false'?>,
        'width': <?php echo $w; ?>,
        'height': <?php echo $h; ?>,
        'url': '<?php echo $cam_url; ?>'
    };

    var LOADING = true;
    var IMG = document.getElementById('viewport'); // FIXME if isn't DOM ready?

    function refresh_img() {
        var now = new Date();
        var update_url = CAM_INFO['url'] + '&_=' + now.getTime(); // prevent caching for stupid browser
        IMG.setAttribute('src', update_url);
        LOADING = true;
    }

    function img_evt(e_id) {
        LOADING = false;
        var ms;

        switch (e_id) {
            case 0: // onload
                if (refresh_mode < 0 /* manual refresh */) {
                    return;
                }
                if (refresh_mode == 0) {
                    ms = 100;
                }
                else {
                    ms = refresh_mode * 1000;
                }
                tmr = setTimeout('refresh_img();', refresh_mode * 1000);
                break;
            case 1: // onerror
                IMG.setAttribute('alt', 'Ошибка загрузки изображения');
                break;
            case 2: // onabort
                IMG.setAttribute('alt', 'Загрузка изображения прервана пользователем');
                break;
            default:
                alert('unknown event id ' + e_id);
        }
    }

    // function body_loaded() {
    //   IMG = document.getElementById('viewport');
    //   if ( refresh_mode < 0 /* manual */)
    //      IMG.click(refresh_img);
    //}

</script>

<?php
require('../foot.inc.php');
