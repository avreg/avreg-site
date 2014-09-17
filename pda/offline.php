<?php
/**
 * @file pda/offline.php
 * @brief
 */
$pageTitle = 'Снапшоты';

$lang_file = '_admin_cams.php';
require('head_pda.inc.php');
if (!isset($cams) || empty($cams)) {
    die('should use "cams" cgi param');
}
if (is_string($cams)) {
    $cams = explode('.', $cams);
}
foreach ($cams as &$value) {
    settype($value, 'int');
}
$one_cam = (count($cams) === 1);
$cams_csv = implode(',', $cams);
$use_desc_order = @empty($desc) ? '' : 'desc';
$oims = !@empty($oims);

if (isset($s) && isset($f)) {
    $timebegin_unix = (int)$s;
    $timeend_unix = (int)$f;
} else {
    if ($until > 0) {
        /* [ dt1 .. (dt1 + until) ]*/
        $timebegin_unix = mktime($hour, $minute_array[$minute], 0, $month, $day, 2000 + $year_array[$year]);
        $_SESSION['timestamp'] = $timebegin_unix;
        $timeend_unix = $timebegin_unix + $until * 60 + 59;
    } else {
        /* [ (dt1 + until) .. dt1 ]*/
        $timeend_unix = mktime($hour, $minute_array[$minute], 59, $month, $day, 2000 + $year_array[$year]);
        $timebegin_unix = $timeend_unix + $until * 60 - 59;
        $_SESSION['timestamp'] = $timeend_unix;
    }
}
$timebegin = strftime('%Y-%m-%d %T', $timebegin_unix);
$timeend = strftime('%Y-%m-%d %T', $timeend_unix);

$_SESSION['cams'] = $cams;
$_SESSION['until'] = (int)$until;
$_SESSION['desc'] = !empty($desc);
$_SESSION['oims'] = !empty($oims);


$recsess_session_name = sprintf(
    'offline_%s_%u_%u_%u_%u',
    implode('_', $cams),
    $use_desc_order ? 1 : 0,
    empty($oims) ? 0 : 1,
    $timebegin_unix,
    $timeend_unix
);


$rec_sessions = null;
if (isset($_SESSION[$recsess_session_name])) {
    $rec_sessions = & $_SESSION[$recsess_session_name];
} else {
    if ($oims) {
        $rec_sessions = $adb->getSnapStatsByRecSeries($cams_csv, $timebegin, $timeend, $use_desc_order);
    } else {
        $rec_sessions = $adb->getSnapStatsByInterval($cams_csv, $timebegin, $timeend, $use_desc_order);
    }
    if (!$rec_sessions) {
        print "<div style='padding: 10px;'>Нет сохранённых картинок (снапшотов) за этот период.<br>\n";
        print "<a href='javascript:window.history.back();' title='$strBack'>$strBack</a></div>\n";
        exit;
    }
    // tohtml($rec_sessions);
    /* для свежих запросов не используем сохранённые сессии */
    $use_session = true;
    $now_sec = time();
    if ($oims) {
        if ($now_sec - $timebegin_unix < 3600) {
            $use_session = false;
        }
    } else {
        if ($timeend_unix > $now_sec) {
            $use_session = false;
        }
    }
    if ($use_session) {
        $_SESSION[$recsess_session_name] = $rec_sessions;
    }
}
session_write_close();

/* pagination */
require_once('paginator.inc.php');
$pagi = new \Avreg\PdaPaginator(
    $rec_sessions,
    isset($off) ? (int)($off) : 0,
    sprintf(
        'offline.php?cams=%s&s=%u&f=%u%s%s',
        $cams_csv,
        $timebegin_unix,
        $timeend_unix,
        @empty($desc) ? '' : '&desc=1',
        @empty($oims) ? '' : '&oims=1'
    ),
    $conf,
    $conf['pda-links-per-page']
);
$pagi->printAbove();

require_once('../lib/get_cams_params.inc.php');
$cams_params = get_cams_params(
    array(
        'work',
        'text_left',
        'geometry',
        'Hx2'
    ),
    $cams
);

/* print record session info into page */
print "<br>\n";
foreach ($pagi as $row) {
    // tohtml($row);
    $start = (int)$row[0];
    $finish = (int)$row[1];
    $cam_nr = (int)$row[2];
    $rec_id = (int)$row[3];
    $snap_nb = (int)$row[4];
    $cam_conf = & $cams_params[$cam_nr];
    $cam_name = isset($cam_conf['text_left']['v']) ? $cam_conf['text_left']['v'] : '';

    print "<div style='margin: 1px 1px 10px 1px; pad: 2px 2px 2px 2px; border-bottom: 1px dotted;'>\n";
    printf(
        '<a href="snaplist.php?camera=%u&ser_nr=%u&s=%u&f=%u&desc=%d&oims=%d">%s (%s)</a>',
        $cam_nr,
        $rec_id,
        $start,
        $finish,
        !@empty($desc),
        !@empty($oims),
        TimeRangeHuman($start, $finish),
        $snap_nb
    );
    printf('<br>Продолжительность: %s', ETA($finish - $start));
    if (!$one_cam) {
        print "<br> $strCam: №$cam_nr $cam_name";
    }
    print "</div>\n";
}

$pagi->printBelow();
print "<div><a href='./' title='$strHome'>$strHome</a></div>\n";

require('../foot.inc.php');
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
