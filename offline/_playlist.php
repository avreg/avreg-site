<?php
/**
 * @file offline/_playlist.php
 * @brief Страица формирования плейлиста
 */
ob_start();

require('../lib/config.inc.php');
DENY($arch_status);

session_start();

function input_data_invalid($err_code)
{
    $backurl = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $GLOBALS['_SERVER']['SERVER_NAME'] . ':' .
        $GLOBALS['_SERVER']['SERVER_PORT'] . $GLOBALS['conf']['prefix'] . '/offline/playlist.php';
    header("Location: $backurl");
    $_SESSION['error'] = $err_code;
    ob_end_flush();
    exit;
}

/* validate input form's data */
$expire = time() + 5184000;
$_pg = dirname($_SERVER['SCRIPT_NAME']) . '/';

if (!is_array($cams) || (count($cams) === 0)) {
    input_data_invalid('cams');
}
foreach ($cams as &$value) {
    if (!settype($value, 'int')) {
        input_data_invalid('cams');
    }
}
if (is_array($allow_cams) && count($allow_cams) > 0) {
    $a = array_intersect($allow_cams, $cams);
    $cams = array_values($a);
    if (count($cams) === 0) {
        input_data_invalid('cams');
    }
}
setcookie('avreg_cams[]', implode('-', $cams), $expire, $_pg);

if (!is_array($ftypes) || (count($ftypes) === 0)) {
    input_data_invalid('ftypes');
}
foreach ($cams as &$value) {
    if (!settype($value, 'int')) {
        input_data_invalid('ftypes');
    }
}
setcookie('avreg_ftypes[]', implode('-', $ftypes), $expire, $_pg);

$int_vals = array(
    'timemode',
    'year1',
    'month1',
    'day1',
    'hour1',
    'minute1',
    'year2',
    'month2',
    'day2',
    'hour2',
    'minute2'
);
foreach ($int_vals as &$value) {
    if (!isset($GLOBALS[$value])) {
        input_data_invalid($value);
    }
    if (!settype($GLOBALS[$value], 'int')) {
        input_data_invalid($value);
    }
    $_SESSION[$value] = $GLOBALS[$value];
}

setcookie('avreg_timemode', $_POST['timemode'], $expire, $_pg);
if ($timemode === 2) {
    /* интервалы */
    setcookie('avreg_timemode', '2', $expire, $_pg);
    if (!is_array($dayofweek) || (count($dayofweek) === 0)) {
        input_data_invalid('dayofweek');
    }
    foreach ($dayofweek as &$value) {
        if (!settype($value, 'int')) {
            input_data_invalid('dayofweek');
        }
    }
    $_SESSION['dayofweek'] = $dayofweek;
} elseif ($timemode == '1') {
    /* диапазон */
    setcookie('avreg_timemode', '1', $expire, $_pg);
} else {
    input_data_invalid('timemode');
}

if (empty($pl_fmt)) {
    input_data_invalid('pl_fmt');
}
if (0 == strcasecmp('XSPF', $pl_fmt)) {
    setcookie('avreg_pl_fmt', 'XSPF', $expire, $_pg);
} elseif (0 == strcasecmp('M3U', $pl_fmt)) {
    setcookie('avreg_pl_fmt', 'M3U', $expire, $_pg);
} elseif (0 == strcasecmp('TXT', $pl_fmt)) {
    setcookie('avreg_pl_fmt', 'TXT', $expire, $_pg);
} else {
    input_data_invalid('pl_fmt' . $pl_fmt);
}

/*
if (($_POST['cams_ordered']))
      setcookie('avreg_cams_ordered','1',$expire,$_pg);
  else
      setcookie('avreg_cams_ordered','0',$expire,$_pg);
*/

$events = & $ftypes;
$_cams_csv = implode(',', $cams);

$date = array(
    'from' => array($year_array[$year1], $month1, $day1, $hour1, $minute_array[$minute1]),
    'to' => array($year_array[$year2], $month2, $day2, $hour2, $minute_array[$minute2]),
);

//Если ищем видео - добавляем в поиск video+audio (EVT_ID==12)
$is_video = false;
foreach ($events as $val) {
    if ($val == 23) {
        $is_video = true;
    }
}
if ($is_video) {
    array_push($events, 12);
}

$res_array = $adb->eventsSelect($cams, $date, $events, isset($dayofweek) ? $dayofweek : array(), $timemode);

if ($conf['debug']) {
    $_SESSION['sql'] = $query;
}
$num_rows = @count($res_array);

if (!$num_rows) {
    input_data_invalid('0');
}

/* отправляем http headers */
while (@ob_end_clean()) {
    ;
}

// Don't use cache (required for Opera)
$now_ts = time();
$now = gmdate('D, d M Y H:i:s', $now_ts) . ' GMT';
header('Expires: ' . $now);
header('Last-Modified: ' . $now);
header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

function getDayName($n)
{
    return $GLOBALS['day_of_week'][$n];
}

/* вычисляем протокол доступа к медиа-файлам
   на основании адреса клиента и правил в конфиге */
require_once($wwwdir . 'lib/utils-inet.php');
$remote_ip = ip2long($_SERVER['REMOTE_ADDR']);
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$CRLF = "\r\n";
if ($MSIE /* only win */ || false !== strpos($ua, 'windows')) {
    $platform = 'win';
} else {
    if (false !== strpos($ua, 'linux') ||
        false !== strpos($ua, 'solaris') ||
        false !== strpos($ua, 'bsd') ||
        false !== strpos($ua, 'mac os x')
    ) {
        $platform = 'nix';
        if ($lineending == 'AUTO' || $lineending == 'CR') {
            $CRLF = "\n";
        }
    } else {
        $platform = 'other';
    }
}

$_ipacl_list = array_keys($conf["murl-pre-$platform"]);
foreach ($_ipacl_list as &$_ipacl_str) {
    $_ipacl = avreg_inet_network($_ipacl_str);
    if ($_ipacl === false) {
        /* неправильно задан IP ACL */
        break;
    }
    if (true === avreg_ipv4_cmp($remote_ip, -1, $_ipacl['addr'], $_ipacl['mask'])) {
        $MediaUrlPref = & $conf["murl-pre-$platform"][$_ipacl_str];
        break;
    }
}
if (!isset($MediaUrlPref) /* не нашли совпадения по ACL */) {
    $MediaUrlPref = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'];
    if ($_SERVER['SERVER_PORT'] != '80') {
        $MediaUrlPref .= ':' . $_SERVER['SERVER_PORT'];
    }
    $MediaUrlPref .= $conf['prefix'] . $conf['media-alias'];
}

$fname = "avreg_cams-$_cams_csv";
if ($pl_fmt === 'XSPF') {
    header('Content-Type: application/xspf+xml');
    header("Content-Disposition: attachment; filename=\"$fname.xspf\"");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>$CRLF";
    echo "<playlist version=\"0\" xmlns=\"http://xspf.org/ns/0/\">$CRLF";
    if ($timemode === 2) {
        printf(
            "\t<title>time: [%s - %s], [%s], [%s-%s]</title>$CRLF",
            substr($GLOBALS['__EVENTS_QUERY_INFO']['timebegin'], 0, -9),
            substr($GLOBALS['__EVENTS_QUERY_INFO']['timeend'], 0, -9),
            implode(',', array_map("getDayName", $GLOBALS['__EVENTS_QUERY_INFO']['dayofweek'])),
            substr($GLOBALS['__EVENTS_QUERY_INFO']['time_in_day_begin'], 0, -3),
            substr($GLOBALS['__EVENTS_QUERY_INFO']['time_in_day_end'], 0, -3)
        );
    } else {
        printf(
            "\t<title>[%s - %s]</title>$CRLF",
            substr($GLOBALS['__EVENTS_QUERY_INFO']['timebegin'], 0, -3),
            substr($GLOBALS['__EVENTS_QUERY_INFO']['timeend'], 0, -3)
        );
    }
    echo "\t<creator>cams: [" . $_cams_csv . "]</creator>$CRLF";
    echo "\t<annotation>" . $GLOBALS['__EVENTS_QUERY_INFO']['query'] . "</annotation>$CRLF";
    echo "\t<info>http://avreg.net</info>$CRLF";
    $main_date = gmdate("Y-m-d\TH:i:s", $now_ts);
    $tz = date("O", $timestamp);
    $tz = substr_replace($tz, ':', 3, 0);
    echo "\t<date>$main_date$tz</date>$CRLF";
    echo "\t<trackList>$CRLF";
} elseif ($pl_fmt === 'M3U') {
    header('Content-Type: audio/x-mpegurl');
    header("Content-Disposition: attachment; filename=\"$fname.m3u\"");
    echo "#EXTM3U$CRLF";
} else {
    header('Content-Type: text/plain');
    header("Content-Disposition: attachment; filename=\"$fname.txt\"");
}

while (@ob_end_flush()) {
    ;
}

for ($i = 1; $i <= $num_rows; $i++) {
    $row = & $res_array[$i - 1];
    $location = $MediaUrlPref . '/' . $row['EVT_CONT'];
    if ($row['EVT_ID'] == '23') {
        $_media_str = '(video)';
    } else {
        if ($row['EVT_ID'] == '32') {
            $_media_str = '(audio)';
        } else {
            if ($row['EVT_ID'] == '12') {
                $_media_str = '(video+audio)';
            } else {
                $_media_str = 'unknown';
            }
        }
    }

    $duration = (int)$row['UDT1'] - (int)$row['UDT2'];
    if ($pl_fmt === 'XSPF') {
        $title = strftime('%a, %d %b %H:%M:%S', (int)$row['UDT2']);
        $duration *= 1000;
        echo "\t\t<track>$CRLF";
        echo "\t\t\t<location>$location</location>$CRLF";
        echo "\t\t\t<title>$title</title>$CRLF";
        printf("\t\t\t<creator>cam #%02u %s</creator>$CRLF", (int)$row['CAM_NR'], $_media_str);
        echo "\t\t\t<annotation>" . filesizeHuman(
            (int)$row['FILESZ_KB']
        ) . ', ' . $row['FRAMES'] . " frames</annotation>$CRLF";
        echo "\t\t\t<duration>$duration</duration>$CRLF";
        // echo "\t\t\t<album>". $row['EVT_ID'] ."</album>$CRLF";
        echo "\t\t\t<trackNum>$i</trackNum>$CRLF";
        echo "\t\t</track>$CRLF";
    } else {
        if ($pl_fmt === 'M3U') {
            $title = sprintf('cam #%02u %s - ', (int)$row['CAM_NR'], $_media_str) . date(
                'Y-m-d H:i:s',
                (int)$row['UDT2']
            );
            echo "#EXTINF:$duration,$title$CRLF";
        }
        echo "$location$CRLF";
    }
}

if ($pl_fmt === 'XSPF') {
    echo "\t</trackList>$CRLF";
    echo "</playlist>$CRLF";
}

if (isset($_SESSION) && isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
