<?php
/**
 * @file pda/snaplist.php
 * @brief
 */
$USE_JQUERY = true;
$pageTitle = sprintf('Снапшоты камеры №%u', $_GET['camera']);
$lang_file = '_admin_cams.php';
require('head_pda.inc.php');

if (!isset($camera) || !settype($camera, 'int')) {
    die('should use "camera" cgi param');
}
if (!isset($ser_nr) || !settype($ser_nr, 'int')) {
    die('should use "ser_nr" cgi param');
}
if (!isset($s) && !isset($f)) {
    die('invalid cgi params');
}

$desc = !@empty($desc);
$oims = !@empty($oims);

$files = null; // массив - ссылок на файлы, вычитанные с базы
$recsess_list_url = null; // url, откуда пришли - со списка серий/записи движения
if (!empty($_SERVER['HTTP_REFERER']) && false != strpos($_SERVER['HTTP_REFERER'], '/offline.php')) {
    $_SESSION['recsess_list_url'] = $_SERVER['HTTP_REFERER'];
    $recsess_list_url = & $_SESSION['recsess_list_url'];
} else {
    if (!empty($_SESSION['recsess_list_url'])) {
        $recsess_list_url = & $_SESSION['recsess_list_url'];
    }
}

$spanlist_session_name = sprintf(
    'snaplist_%u_%u_%u_%u_%d_%d',
    $camera,
    $ser_nr,
    $s,
    $f,
    $desc,
    $oims
);

if (isset($_SESSION[$spanlist_session_name])) {
    $files = & $_SESSION[$spanlist_session_name];
} else {
    $timebegin = strftime('%Y-%m-%d %T', (int)$s);
    $timeend = false;
    if (!empty($f)) {
        $timeend = strftime('%Y-%m-%d %T', (int)$f);
    }

    $files = $adb->getSnapshots($camera, $ser_nr, $timebegin, $timeend, $desc ? 'desc' : '');

    if (!$files) {
        print "<div style='padding: 10px;'>Сохранённых картинок не найдено :-0<br>\n";
        print "<a href='javascript:window.history.back();' title='$strBack'>$strBack</a></div>\n";
        exit;
    }
    /* для свежих запросов не используем сохранённые сессии */
    $use_session = true;
    $now_sec = time();
    if ($oims) {
        if ($now_sec - (int)$s < 3600) {
            $use_session = false;
        }
    } else {
        if ((int)$f > $now_sec) {
            $use_session = false;
        }
    }
    if ($use_session) {
        $_SESSION[$spanlist_session_name] = $files;
    }
}

session_write_close();

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
    $tumb_sizes = get_resolutions($conf['pda_resolutions']);
} else {
    $tumb_sizes = get_resolutions($conf['pda_resolutions'], false);
}

if ($tumb_sizes == null || sizeof($tumb_sizes) == 0) {
    //если ничего в конфиге не определено
    $tumb_sizes = array(0 => array('w' => '160', 'h' => '80',));
}
if ($scale >= sizeof($tumb_sizes) - 1) {
    $scale = sizeof($tumb_sizes) - 1;
}

show_select_resolution($tumb_sizes, $scale, $strScale['scale']);

$width = $tumb_sizes[$scale]['w'];
$heigt = $tumb_sizes[$scale]['h'];

$reload = 'false';
if ($width == 'FS') {
    $width = isset($_GET['aw']) ? $_GET['aw'] : 0;
    $heigt = isset($_GET['ah']) ? $_GET['ah'] : 0;

    if ($width == 0) {
        $reload = 'true';
    }
}

?>

<script type="text/javascript">
    var reload = <?php print $reload; ?>;
    var scale = <?php print $scale; ?>;
    var requst_uri = <?php print '"' . $_SERVER['REQUEST_URI'] . '"'; ?>;
</script>

<?php

/* pagination */
require_once('paginator.inc.php');
$pagi = new \Avreg\PdaPaginator(
    $files,
    isset($off) ? (int)($off) : 0,
    sprintf('snaplist.php?camera=%u&ser_nr=%u&s=%u&f=%u&desc=%d&oims=%d', $camera, $ser_nr, $s, $f, $desc, $oims),
    $conf,
    $conf['pda-thumb-image-per-page']
);
$pagi->printAbove();

function is_ipv6($address)
{
    $ipv4_mapped_ipv6 = strpos($address, '::ffff:');
    return (strpos($address, ':') !== false) &&
           ($ipv4_mapped_ipv6 === false || $ipv4_mapped_ipv6 != 0);
}

/* print objects <img> to page */
$u = 'http://'; // FIXME https for local resizer and httpd ?
if (strcasecmp(gethostname(), $_SERVER['SERVER_NAME']) === 0) {
    $u .= $_SERVER['SERVER_NAME'];
} else {
    if (is_ipv6($_SERVER['SERVER_ADDR'])) {
        $u .= '[' . $_SERVER['SERVER_ADDR'] . ']';
    } else {
        $u .= $_SERVER['SERVER_ADDR'];
    }
}
if ($_SERVER['SERVER_PORT'] != 80) {
    $u .= ':' . $_SERVER['SERVER_PORT'];
}
foreach ($pagi as $row) {
    $START = (int)$row[0];
    $FINISH = (int)$row[1];
    $EVT_ID = (int)$row[2];
    $FILESZ_KB = (int)$row[3];
    $FRAMES = (int)$row[4];
    $U16_1 = (int)$row[5];
    $U16_2 = (int)$row[6];
    $rel_path = $conf['prefix'] . $conf['media-alias'] . '/' . $row[7];

    print "<div style='margin: 0px 0px 10px 0px; pad: 0px 0px 0px 0px; border-bottom: 1px dotted;'>\n";
    print strftime('&nbsp;%h %d(%a) %T<br>', $START);
    if ($EVT_ID >= 15 && $EVT_ID <= 17 /* snapshot jpegs */) {
        $jpeg_info = "$FILESZ_KB kB, [$U16_1 x $U16_2]";
        printf("<a href='$rel_path' title='Открыть оригинал $jpeg_info'>\n");

        printf(
            '<img class="cam_snapshot" src="' . $conf['prefix'] . '/lib/resize_img.php?prop=false&url=%s&w=%s&h=%s"
             alt="Ошибка загрузки">',
            urlencode($u . $rel_path),
            $width,
            $heigt
        );
        print "</a></div>\n";
    }
}

$pagi->printBelow();

print '<div>';
if ($recsess_list_url) {
    print "<a href='$recsess_list_url' title='К списку сеансов записи'>К списку</a>&nbsp;|&nbsp;\n";
}
print "<a href='./' title='$strHome'>$strHome</a></div>\n";

require('../foot.inc.php');
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
