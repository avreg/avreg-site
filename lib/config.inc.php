<?php

/**
 * @file lib/config.inc.php
 * @brief Файл обеспечивает инициализацию глобальных переменных,<br />а также содержит функции для установки
 * параметров конфигурации
 *
 * Файл реализует функции:
 * <ul>
 *    <li> Инициализации глобальных переменных </li>
 *    <li> Загрузки конфигурационных настроек из файлов конфигурации /etc/avreg/site-defaults.php </li>
 *    <li> Авторизации пользователя </li>
 *    <li> Настройки параметров камер </li>
 * </ul>
 *
 *
 */

require('/etc/avreg/site-defaults.php');

/// Путь к директории сайта
$wwwdir = $conf['site-dir'] . '/';

/*
if (empty($conf['prefix']))
   $wwwdir = $_SERVER['DOCUMENT_ROOT'] . '/';
else
   $wwwdir = $_SERVER['DOCUMENT_ROOT'] . $conf['prefix'] . '/';
 */
require($wwwdir . 'lib/grab_globals.lib.php');
///  1
$BOOL_VAL = 1;
///  2
$INT_VAL = 2;
///  3
$INTPROC_VAL = 3; // int or %
/// 4
$CHECK_VAL = 4;
///  5
$STRING_VAL = 5;
///  6
$STRING200_VAL = 6;
///  7
$PASSWORD_VAL = 7;

/**
 * @brief Форматирование и вывод дампа переменной
 * @param unknown_type $var переменная
 *
 */
function tohtml($var)
{
    print '<div class="dump"><pre class="dump">' . "\n";
    var_dump($var);
    print '</pre></div>' . "\n";
}

/**
 * возвращает объект для конфигурации плеера для клиентской ОС
 * @param array $aplayer_config - массив $conf['aplayerConfig']
 * TODO: Android, iPad, MAC, etc
 */
function aplayer_configurate($aplayer_config)
{
    if (substr_count($_SERVER["HTTP_USER_AGENT"], 'Linux') > 0) {
        config_merging_part($aplayer_config['*'], $aplayer_config['linux']);
        return $aplayer_config['linux'];
    } elseif (substr_count($_SERVER["HTTP_USER_AGENT"], 'Windows') > 0) {
        config_merging_part($aplayer_config['*'], $aplayer_config['windows']);
        return $aplayer_config['windows'];
    } else {
        return $aplayer_config['*'];
    }
}

/**
 *рекурсивно комбинирует конфигурационные общие(для всех ОС) настройки плеера с настройками для ОС пользователя
 * @param unknown_type $star_marked - общие настройки для всех ОС (с индексом ['*'] - $conf['aplayerConfig']['*'])
 * @param unknown_type $res_out - ссылка на результирующий массив с параметрами для клиентской ОС
 */
function config_merging_part($star_marked, &$res_out)
{
    if (gettype($star_marked) != 'array') {
        return;
    }

    foreach ($star_marked as $key => $val) {
        if (isset($res_out[$key])) {
            config_merging_part($val, $res_out[$key]);
        } else {
            $res_out[$key] = $val;
        }
    }
}

/**
 *
 * Фуцнкция парсит строки в csv формате в масив
 * @param string $str csv строка
 * @return array
 */
function parse_csv_numlist($str)
{
    $res = array();
    foreach (explode(',', $str) as $value) {
        if (is_numeric($value)) {
            $res[] = (int)$value;
        } else {
            /* check range */
            if (preg_match('/^\s*(\d+)\s*-\s*(\d+)\s*$/', $value, $matches)) {
                $start = (int)$matches[1];
                $end = (int)$matches[2];
                if ($start >= $end) {
                    return false;
                }
                for (; $start <= $end; $start++) {
                    $res[] = (int)$start;
                }
            } else {
                return false;
            } /* Error */
        }
    }
    return $res;
} /* parse_csv_numlist() */

/* $params строковый массив, список параметров, которые нужно читать из файла */
/**
 *
 * Функция извлекает конфигурационные параметры из файла
 * @param array $_conf масив текущих параметров
 * @param string $section секция в которой искать параметры
 * @param string $path путь к файлу
 * @param array $params параметры, которые нужно извлечь, null если все
 * @return array масив параметров
 */
function confparse($_conf, $section = null, $path = '/etc/avreg/avreg.conf', $params = null)
{
    $confile = @fopen($path, 'r');
    if (false === $confile) {
        return false;
    }
    $skip_section = false;
    $linenr = 0;
    $ret_array = array();
    $res = true;

    while (!feof($confile)) {
        $line = trim(fgets($confile, 1024));
        $linenr++;
        if (empty($line)) {
            continue;
        }

        if (preg_match('/^\s*[;#]/', $line)) {
            continue;
        } /* skip comments */

        if (preg_match('/^([^\s=]+)[\s=]*\{$/', $line, $matches)) {
            # begin section
            if (empty($section) || 0 !== strcasecmp($matches[1], $section)) {
                $skip_section = true;
            }
            continue;
        }

        if (preg_match('/.*\}$/', $line)) {
            $skip_section = false;
            continue;
        }

        if ($skip_section) {
            continue;
        }

        if (1 !== preg_match(
            "/^[\s]*([^\s#;=]+)[\s=]+([\"']?)(.*?)(?<!\\\)([\"']?)\s*$/Su",
            $line,
            $matches
        )
        ) {
            $res = false;
            break;
        }
        // var_dump($matches);

        $start_quote = & $matches[2];
        $end_quote = & $matches[4];
        if ($start_quote !== $end_quote) {
            $res = false;
            break;
        }

        $param = & $matches[1];
        $value = stripslashes($matches[3]);

        if (is_array($params)) {
            if (false === array_search($param, $params)) {
                continue;
            }
        }

        // нашли параметр
        // printf('file %s:%d : %s => %s (%s)<br>', $path, $linenr, $param, $value, gettype(@$_conf[$param]));
        if (0 === strcasecmp($param, 'include')) {
            // вложенный файл
            $res = confparse($_conf, $section, $value);
            if (!$res) {
                echo "ERROR INCLUDE FILE \"$value\" from $path:$linenr\n";
                $res = false;
                break;
            } else {
                $ret_array = array_merge($ret_array, $res);
            }
        } else {
            /* обычное параметр = значение */
            /* проверяем парамет - а мож это массив */
            if (1 === preg_match("/^([^\[]+)\[([\"']?)([^\]]*?)([\"']?)\](.*)/Su", $param, $match2)) {

                /* наш параметр -- массив */
                $param = $match2[1];
                $key = $match2[3];
                $vt = gettype(@$_conf[$param]);
                if (0 !== strcasecmp($vt, 'array')) {
                    $res = false;
                    break;
                }
                //$ret_array[$param][$key] = $value;

                $str = '$ret_array[$param][$key]' . $match2[5] . ' = $value;';

                eval($str);

            } else {
                /* простое параметр, не массив */
                /* пробуем установить тип значения с учётом дефолтного $conf[param] */
                $vt = gettype(@$_conf[$param]);
                if ($vt !== 'NULL' && !settype($value, $vt)) {
                    $res = false;
                    break;
                }
                $ret_array[$param] = $value;
            }
        }
    } // while eof

    fclose($confile);

    if ($res) {
        return $ret_array;
    } else {
        // invalid pair param = value
        echo("INVALID LINE in file $path:$linenr => [ $line ]\n");
        return false;
    }
} /* confparse() */

function get_avreg_profiles($_conf)
{
    $ret = @glob($_conf['profiles-dir'] . '/[A-Za-z0-9][A-Za-z0-9_-:]*');
    if ($ret === false || count($ret) === 0) {
        return array('');
    } else {
        return $ret;
    }
}

/// переменная содержащая настройки avreg-site
$res = confparse($conf, 'avreg-site');
if (!$res) {
    die();
} else { /// масив содержащий настройки
    $conf = array_merge($conf, $res);
}

unset($EXISTS_PROFILES);
unset($AVREG_PROFILE);
if (!empty($conf['prefix']) && preg_match('@^/([^/]+).*@', $_SERVER['REQUEST_URI'], $matches)) {
    if (strcasecmp($matches[1], 'avreg') === 0) {
        $EXISTS_PROFILES = get_avreg_profiles($conf);
    } else {
        $res = confparse($conf, 'avreg-site', $conf['profiles-dir'] . '/' . $matches[1]);
        if (!$res) {
            die("<br /><br />Error: not found active profile " . $conf['profiles-dir'] . '/' . $matches[1]);
        }
        $AVREG_PROFILE = $matches[1];
        // tohtml($res);
        if (is_array($res)) {
            $conf = array_merge($conf, $res);
            $conf['prefix'] = '/' . $AVREG_PROFILE;
            $conf['daemon-name'] .= '-' . $AVREG_PROFILE;
        }
    }
} else {
    $EXISTS_PROFILES = get_avreg_profiles($conf);
}

if ($conf['debug']) {
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
    ini_set('html_errors', '1');
    error_reporting(
        E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_CORE_WARNING |
        E_COMPILE_ERROR | E_COMPILE_WARNING | E_USER_ERROR | E_USER_WARNING |
        E_USER_NOTICE
    );
}

/**
 *
 * Функция позволяет получить настройки камер
 * @param string $application
 * @return array масив настроек
 */
function load_profiles_cams_confs($application = 'avreg-site')
{
    if (!empty($AVREG_PROFILE) || count($GLOBALS['EXISTS_PROFILES']) === 0) {
        return false;
    }

    $cams_profiles = array();
    $profiles_conf = array();
    $i = 0;
    foreach ($GLOBALS['EXISTS_PROFILES'] as &$profile) {
        $a = confparse($GLOBALS['conf'], $application, $profile);
        if (empty($a) || !array_key_exists('devlist', $a)) {
            continue;
        }
        $profiles_conf[$i] = $a;
        $b = parse_csv_numlist($a['devlist']);
        foreach ($b as &$c) {
            $cams_profiles[$c] = & $profiles_conf[$i];
        }
        $i++;
    }
    return $cams_profiles;
} /* get_profiles_cams_confs() */

$sip = & $_SERVER['SERVER_ADDR'];
$named = & $_SERVER['SERVER_NAME'];

///  ip сервера
$localip = ip2long($sip);
///  user agent пользователя
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
///  Движок браузера пользователя
$MSIE = $GECKO = $PRESTO = $WEBKIT = false;

if (false !== strpos($ua, 'msie') || false !== strpos($ua, 'trident')) {
    $MSIE = true;
} elseif (false !== strpos($ua, 'gecko')) {
    $GECKO = true;
} elseif (false !== strpos($ua, 'presto')) {
    $PRESTO = true;
} elseif (false !== strpos($ua, 'webkit') || false !== strpos($ua, 'khtml')) {
    $WEBKIT = true;
}

///  логин пользователя
$login_user = 'unknown';
///  имя пользователя
$login_user_name = 'unknown';
///  ip пользователя
$remote_addr = 'unknown';
///  host пользователя
$login_host = 'unknown';
///  масив пользователей
$users = array();
///  информация пользователя
$user_info = null;

$link = null;
///  статус пользователя
$user_status = 555;
///  статус пользователя групы install
$install_status = 1;
///  статус пользователя групы admin
$admin_status = 2;
///  статус пользователя групы arch
$arch_status = 3;
///  статус пользователя групы operator
$operator_status = 4;
///  статус пользователя групы view
$viewer_status = 5;
///  являеться ли пользователь в групе install
$install_user = false;
///  являеться ли пользователь в групе admin
$admin_user = false;
///  являеться ли пользователь в групе arch
$arch_user = false;
///  являеться ли пользователь в групе operator
$operator_user = false;
///  являеться ли пользователь в групе view
$viewer_user = false;
///	 Доступность PDA версии
$allow_pda = false;

///  кодировка
$chset = 'UTF-8';
///  язык
$lang = 'russian';
///  локаль
$locale_str = 'ru_RU.' . $chset;
setlocale(LC_ALL, $locale_str);
// @apache_setenv('LANG','ru_RU.'.$chset);
mb_internal_encoding('UTF-8');
///  путь к языковым файлам
$lang_dir = $wwwdir . 'lang/' . $lang . '/' . strtolower($chset) . '/';
///  языковый модуль
$lang_module_name = $lang_dir . 'common.inc.php';
///  название локального языкового модуля
$pt = substr($_SERVER['PHP_SELF'], strlen($conf['prefix']));
///  параметры языкового модуля
$params_module_name = $lang_dir . 'params.inc.php';
require($lang_module_name);

///  языковый модуль
$lang_module_name2 = $lang_dir . str_replace('/', '_', $pt);

if (isset($lang_file)) {
    $lang_module_name2 = $lang_dir . $lang_file;
}

if (file_exists($lang_module_name2)) {
    require($lang_module_name2);
}

if ($_SERVER['REMOTE_ADDR'] === '::1') {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}

if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
    $remote_addr = 'localhost';
} else {
    $remote_addr = & $_SERVER['REMOTE_ADDR'];
}

/* set timezone "You are *required* to use the date.timezone setting or the date_default_timezone_set() function." */
$tzname = @file_get_contents('/etc/timezone'); // debian-style
if (!empty($tzname)) {
    date_default_timezone_set(rtrim($tzname));
}

///  шрифт текста
$font_family = 'sans-serif';
///  размер шрифта
$font_size = 'small';
///  цвет контента
$ContentColor = '#F5F5F5';
///
$inactive_h_color = 'darkGray';
///  цвет ошибки
$warn_color = '#003366';
///  цвет критической ошибки
$error_color = '#CC3333';
///  цвет
$rowHiLight = '#FFFFCC';
///  цвет заголовка
$header_color = '#D0DCE0';
///  цвет
$TextHiLight = '#009900';

///  цвет
$NotSetParColor = '#585858';
///  цвет
$ParDefColor = '#6633FF';
///  цвет
$ParSetColor = 'Red';

///  максимальное количество камер
$MAX_CAM = 1000;
///  ширина левой колонки
$cfg['LeftWidth'] = '170';
///  цвет фона левой колонки
$left_bgcolor = '#D0DCE0';
///  тег таблиц
$tabletag = '<table cellspacing="0" border="1" cellpadding="2" align="center">';

$patternIP = '[1-9]\d{1,2}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
$patternAllowedIP = '/^(' . $patternIP . '|any|localhost|*)$/';
$patternUser = '/^[A-Za-z0-9_\-]{4,16}$/';
$patternPasswd = '/^[A-Za-z0-9_\-]{0,16}$/';

$WellKnownAspects = array(
    array(4, 3),
    array(10, 8),
    array(11, 9),
    array(3, 2),
    array(16, 9),
    array(16, 10),
    array(5, 4)
);

require_once('adb.php');
$result = $adb->getUsers();

foreach ($result as $row) {

    /* var_dump($row); */
    $ui = array();
    $ui['HOST'] = $row['HOST'];
    $ui['USER'] = $row['USER'];
    $ui['PASSWD'] = $row['PASSWD'];
    $ui['STATUS'] = (int)$row['STATUS'];
    $ui['GUEST'] = (int)$row['GUEST'];
    $ui['PDA'] = (int)$row['PDA'];
    $ui['ALLOW_CAMS'] = $row['ALLOW_CAMS'];
    $ui['ALLOW_LAYOUTS'] = $row['ALLOW_LAYOUTS'];
    $ui['MAX_FORCED_REC_MINUTES'] = is_null(
        $row['MAX_FORCED_REC_MINUTES']
    ) ? null : (int)$row['MAX_FORCED_REC_MINUTES'];
    $ui['MAX_MEDIA_SESSIONS_NB'] = is_null($row['MAX_MEDIA_SESSIONS_NB']) ? null : (int)$row['MAX_MEDIA_SESSIONS_NB'];
    $ui['MAX_VIDEO_FPS'] = is_null($row['MAX_VIDEO_FPS']) ? null : $row['MAX_VIDEO_FPS'];
    $ui['MAX_VIDEO_NONMOTION_FPS'] = is_null($row['MAX_VIDEO_NONMOTION_FPS']) ? null : $row['MAX_VIDEO_NONMOTION_FPS'];
    $ui['MAX_MEDIA_SESSION_RATE_KB'] = is_null(
        $row['MAX_MEDIA_SESSION_RATE_KB']
    ) ? null : (int)$row['MAX_MEDIA_SESSION_RATE_KB'];
    $ui['MAX_MEDIA_SESSION_MINUTES'] = is_null(
        $row['MAX_MEDIA_SESSION_MINUTES']
    ) ? null : (int)$row['MAX_MEDIA_SESSION_MINUTES'];
    $ui['MAX_MEDIA_SESSION_VOLUME_MB'] = is_null(
        $row['MAX_MEDIA_SESSION_VOLUME_MB']
    ) ? null : (int)$row['MAX_MEDIA_SESSION_VOLUME_MB'];
    $ui['LONGNAME'] = $row['LONGNAME'];
    $ui['CHANGE_HOST'] = $row['CHANGE_HOST'];
    $ui['CHANGE_USER'] = $row['CHANGE_USER'];
    $ui['CHANGE_TIME'] = $row['CHANGE_TIME'];
    $users[] = $ui;
}

unset($result);
/**
 *
 * Функция позволяет получить информацию о пользователе
 * @param string $ipacl хосты
 * @param string $name логин
 * @return информация о пользователе или False
 */
function get_user_info($ipacl, $name)
{
    if (!isset($ipacl) || !isset($name)) {
        return false;
    }
    if (empty($ipacl) || empty($name)) {
        return false;
    }

    foreach ($GLOBALS['users'] as $ui) {
        if (0 === strcasecmp($ui['HOST'], $ipacl) &&
            0 === strcmp($ui['USER'], $name)
        ) {
            return $ui;
        }
    }
    return false;
}

/**
 * Функция получает информацию по пользователю по заданым параметрам
 * @param array $addr хосты
 * @param string $mask маска сети
 * @param string $name логин
 * @return информация о пользователе или False
 */
function avreg_find_user($addr, $mask, $name)
{
    if (!isset($addr) || !isset($mask) || !isset($name)) {
        return false;
    }
    if (is_null($addr) || is_null($mask) || is_null($name)) {
        return false;
    }
    $found = false;
    foreach ($GLOBALS['users'] as $ui) {
        if (0 !== strcmp($ui['USER'], $name)) {
            continue;
        }

        $ipacl = avreg_inet_network($ui['HOST']);
        if ($ipacl === false) {
            continue;
        } // FIXME - may be error/warning?

        $found = avreg_ipv4_cmp(
            $addr,
            $mask,
            $ipacl['addr'],
            $ipacl['mask']
        );
        /*
              syslog(LOG_ERR,sprintf("equal = %d, 0x%X/0x%X 0x%X/0x%X",
                                      $found,
                                      $addr, $mask,
                                      $ipacl['addr'],  $ipacl['mask']));
         */
        if ($found !== false) {
            break;
        }
    }

    if ($found && $GLOBALS['conf']['debug']) {
        syslog(
            LOG_ERR,
            sprintf(
                'ACL %s %s@%s %s/%s',
                $found ? "success" : "failed",
                $name,
                long2ip($addr),
                $ipacl['addr_a'],
                $ipacl['mask_a']
            )
        );
    }

    return ($found) ? $ui : false;
}

/**
 *
 *
 * @param string $dev_acl_str
 */
function parse_dev_acl($dev_acl_str = null)
{
    if (empty($dev_acl_str)) {
        return true;
    } /* any */
    $chunks = explode(',', $dev_acl_str);
    $ret = array();
    foreach ($chunks as &$sub) {
        if (preg_match('/^\s*(\d+)\s*$/', $sub, $matches)) {
            $ret[] = (Int)$matches[1];
        } elseif (preg_match('/^\s*(\d+)\s*-\s*(\d+)\s*$/', $sub, $matches)) {
            $_start = (Int)$matches[1];
            $_stop = (Int)$matches[2];
            if ($_start >= $_stop) {
                return false;
            }
            $ret = array_merge($ret, range($_start, $_stop));
        } else {
            return false;
        }
    }

    if (false === sort($ret, SORT_NUMERIC)) {
        return false;
    }
    return array_unique($ret);
}

/**
 *
 * Функция позволяет получить информацию о процессе
 * @param string $proc_name имя процесса
 * @param int $__pid ид процесса
 * @return array
 */
function proc_info($proc_name, $__pid = null)
{
    $_cmd = $GLOBALS['conf']['ps'] . ' -o %cpu,%mem,vsz,rss --no-headers ';
    if ($__pid) {
        $_cmd .= '-p ' . (int)$__pid;
    } else {
        $_cmd .= '-C ' . $proc_name;
    }
    $_cmd .= ' 2>/dev/null';

    exec($_cmd, $lines, $retval);

    if ($retval !== 0) {
        return false;
    }

    $pids = count($lines);
    $cpu = 0;
    for ($i = 0; $i < $pids; $i++) {
        $kwds = preg_split('/[\s]+/', trim($lines[$i]));
        // var_dump($kwds); echo "<br/>";
        $cpu = $cpu + (float)$kwds[0];
        if ($i === 0) {
            $mem = $kwds[1];
            $vsz = $kwds[2];
            $rss = $kwds[3];
        }
    }
    return array((float)$cpu, (float)$mem, (integer)$vsz, (integer)$rss);
}

/**
 *
 * Функция формирует строку размера файла
 * @param int $fsize_KB размер в килобайтах
 * @return string
 */
function filesizeHuman($fsize_KB)
{
    if ($fsize_KB >= 1048576) {
        return sprintf("%0.1f %s", $fsize_KB / 1048576, $GLOBALS['byteUnits'][3]);
    } elseif ($fsize_KB >= 1024) {
        return sprintf("%0.1f %s", $fsize_KB / 1024, $GLOBALS['byteUnits'][2]);
    } elseif ($fsize_KB >= 1) {
        return sprintf("%d %s", $fsize_KB, $GLOBALS['byteUnits'][1]);
    } else {
        return sprintf("%d %s", $fsize_KB * 1024, $GLOBALS['byteUnits'][0]);
    }
}

/**
 *
 * Функция формирует строку времени
 * @param int $sec время в секундах
 * @return string
 */
function DeltaTimeHuman($sec)
{
    settype($sec, 'int');
    if ($sec >= 86400) {
        return sprintf('%.1f дня', $sec / 86400);
    } elseif ($sec >= 3600) {
        return sprintf('%.1f час', $sec / 3600);
    } elseif ($sec >= 60) {
        return sprintf('%.1f мин', $sec / 60);
    } else {
        return sprintf('%d сек', $sec);
    }
}

/**
 *
 * Функция формирует строку времени
 * @param int $sec время в секундах
 * @return string
 */
function ETA($sec)
{
    settype($sec, 'int');
    if ($sec < 0) {
        return '--:--:--';
    } elseif ($sec === 0) {
        return '00:00:00';
    }
    $_sec = $sec % 60;
    $_min = (int)($sec / 60) % 60;
    $_hour = (int)($sec / 3600);
    return sprintf('%02u:%02u:%02u', $_hour, $_min, $_sec);
}

/* $start,$finish - unix timestamp */
/**
 *
 * Функция формирует строку промежутка времени
 * @param int $start дата и время начала в unix timestamp
 * @param int $finish дата и время окончания в unix timestamp
 * @param bool $print_year учитывать год
 * @param bool $print_sec учитывать секунды
 * @return string
 */
function TimeRangeHuman($start, $finish, $print_year = false, $print_sec = false)
{
    if ($print_sec) {
        $time_fmt = '%02u:%02u:%02u';
    } else {
        $time_fmt = '%02u:%02u';
    }

    $start_tm = localtime($start, true);
    $finish_tm = localtime($finish, true);
    $same_year = ($start_tm['tm_year'] === $finish_tm['tm_year']);
    $same_month = $same_year && ($start_tm['tm_mon'] === $finish_tm['tm_mon']);
    $same_day = $same_month && ($start_tm['tm_mday'] === $finish_tm['tm_mday']);

    $const_str = '';
    $diff_str = '';

    if ($print_sec) {
        $start_time = sprintf('%02u:%02u:%02u', $start_tm['tm_hour'], $start_tm['tm_min'], $start_tm['tm_sec']);
        $finish_time = sprintf('%02u:%02u:%02u', $finish_tm['tm_hour'], $finish_tm['tm_min'], $finish_tm['tm_sec']);
    } else {
        $start_time = sprintf('%02u:%02u', $start_tm['tm_hour'], $start_tm['tm_min']);
        $finish_time = sprintf('%02u:%02u', $finish_tm['tm_hour'], $finish_tm['tm_min']);
    }

    if ($finish < $start /* $finish = null or 0 */) {
        $_date = strftime($print_year ? '%Y %b %d(%a)' : '%b %d(%a)', $start);
        return ("[ $_date $start_time - ??? ]");
    }

    if ($same_day) {
        $const_str = strftime($print_year ? '%Y %b %d(%a) ' : '%b %d(%a) ', $start);
        $diff_str = sprintf('%s - %s', $start_time, $finish_time);
    } else {
        if ($same_month) {
            $const_str = strftime($print_year ? '%Y %b ' : '%b ', $start);
            $date_fmt = '%d(%a)';
        } elseif ($same_year) {
            if ($print_year) {
                $const_str = sprintf('%04u ', $start_tm['tm_year'] + 1900);
            }
            $date_fmt = '%b %d(%a)';
        } else {
            $date_fmt = '%Y %b %d(%a)';
        }
        $diff_str = sprintf(
            "%s $start_time - %s $finish_time",
            strftime($date_fmt, $start),
            strftime($date_fmt, $finish)
        );
    }
    return ($const_str . '[ ' . $diff_str . ' ]');
}

/**
 *
 * Функция получает имя камеры
 * @param string $_text_left имя камеры
 * @return string имя камеры
 */
function getCamName($_text_left)
{
    if (empty ($_text_left)) {
        return $GLOBALS['strNotTextLeft'];
    } else {
        return $_text_left;
    }
}

/*
function PrettyCamName($cam_desc=null)
{
   if (empty($cam_desc) || !is_array($cam_desc))
      return '';
}
 */
/**
 *
 * Функция выводит ссылку нажатия назад в истории браузера
 */
function print_go_back()
{
    print '<br><center><a href="javascript:window.history.back();" title="' . $GLOBALS['strBack'] . '">' .
        '<img src="' . $GLOBALS['conf']['prefix'] . '/img/undo_dark.gif" alt="' . $GLOBALS['strBack'] .
        '" width="24" hspace="24" border="0"></a></center>' . "\n";
}

/**
 *
 * Функция преобразовывает переменную в строку для sql запроса
 * @param  $val
 * @return string
 */
function sql_format_str_val($val)
{
    return empty($val) ? 'NULL' : "'" . addslashes($val) . "'";
}

/**
 *
 * Функция преобразовывает целое число в строку для sql запроса
 * @param  $val
 * @return string
 */
function sql_format_int_val($val)
{
    return empty($val) ? 'NULL' : "'" . (Int)$val . "'";
}

/**
 *
 * Функция преобразовывает число в строку для sql запроса
 * @param  $val
 * @return string
 */
function sql_format_float_val($val)
{
    return empty($val) ? 'NULL' : "'" . str_replace(',', '.', $val) . "'";
}

/**
 *
 * Пустая переменная или нет? (int)0 - не пустая.
 * @param  $var
 * @return boolean
 */
function is_empty_var($var)
{
    return ($var === null || (string)$var === '');
}

/**
 *
 * Функция получения камер
 * @param unknown_type $_sip
 * @param bool $first_defs
 * @return array
 */
function getCamsArray($_sip, $first_defs = false)
{
    global $adb;
    $result = $adb->getCamNames();
    $num_rows = count($result);
    if ($num_rows == 0) {
        unset ($result);
        return null;
    }
    $arr = array();
    if ($first_defs) {
        $arr[0] = $GLOBALS['r_cam_defs3'];
    }
    foreach ($result as $row) {
        $_cam_name = getCamName($row['text_left']);
        $_cam_nr = $row['CAM_NR'];
        settype($_cam_nr, 'int');
        $arr[$_cam_nr] = $_cam_name;
    }
    unset ($result);
    return $arr;
}

/**
 *
 * Функция выводящая ошибку
 * @param string $errstr строка ошибки
 * @param string $file файл
 * @param string $line строка
 */
function MYDIE($errstr = 'internal error', $file = '', $line = '')
{
    if (empty($file)) {
        $file = basename($_SERVER['SCRIPT_FILENAME']);
    }
    print '<div><font color="' . $GLOBALS['error_color'] . '">' . "\n";
    printf('Error in %s:%d<br>', $file, $line);
    print $errstr;
    print '</font></div>' . "\n";
    if (!isset($GLOBALS['NOBODY'])) {
        print '</body>' . "\n";
    }
    print '</html>' . "\n";
    exit(1);
}

/**
 *
 * Функция записывает в системный лог собщение
 * @param unknown_type $priority Приоритет
 * @param string $message Сообщение
 *
 */
function print_syslog($priority, $message)
{
    if (!isset($GLOBALS['syslog_opened'])) {
        $GLOBALS['syslog_opened'] = 1;
        openlog(
            isset($GLOBALS['AVREG_PROFILE']) ? 'avreg-site-' . $GLOBALS['AVREG_PROFILE'] : 'avreg-site',
            0,
            LOG_DAEMON
        );
    }
    if ($GLOBALS['login_user'] === 'unknown') {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $luser = $_SERVER['PHP_AUTH_USER'];
        } elseif (isset($_SERVER['REMOTE_USER'])) {
            $luser = $_SERVER['REMOTE_USER'];
        } else {
            $luser = 'unknown';
        }
    } else {
        $luser = $GLOBALS['login_user'];
    }
    if ($GLOBALS['remote_addr'] === 'unknown') {
        $raddr = $_SERVER['REMOTE_ADDR'];
    } else {
        $raddr = $GLOBALS['remote_addr'];
    }
    syslog($priority, sprintf('%s@%s: %s', $luser, $raddr, $message));
}

/**
 *
 * Функция доступа
 * @param int $good_status статус
 * @param int $http_status http статус
 */
function DENY($good_status = null, $http_status = 403)
{
    $user_status = $GLOBALS['user_status'];
    if (!is_null($good_status)) {
        if (settype($good_status, 'int')) {
            if ($user_status <= $good_status) {
                return;
            }
        }
    }

    $aname = & $GLOBALS['conf']['admin-name'];
    $amail = & $GLOBALS['conf']['admin-mail'];
    $atel = & $GLOBALS['conf']['admin-tel'];
    if (array_key_exists($user_status, $GLOBALS['grp_ar'])) {
        $deny_reason = sprintf(
            $GLOBALS['access_denided'],
            $GLOBALS['grp_ar'][$user_status]['grname']
        );
        $action = & $GLOBALS['fmtServerAdmin'];
    } else {
        $deny_reason = sprintf(
            $GLOBALS['fmtAccessDenied'],
            htmlentities(
                isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : "unknown user",
                ENT_QUOTES,
                $GLOBALS['chset']
            ),
            $_SERVER['REMOTE_ADDR']
        );
        $action = & $GLOBALS['fmtTryOnceMore'];
    }

    if (!empty($aname) || !empty($amail) || !empty($atel)) {
        $deny_reason .= '<br /><br />' . "\n";
        $deny_reason .= sprintf(
            $action,
            '&#034;' . htmlentities($aname, ENT_QUOTES, $GLOBALS['chset']) . '&#034; &#060;' . htmlentities(
                $amail,
                ENT_QUOTES,
                $GLOBALS['chset']
            ) . '&#062; , tel: ' . htmlentities($atel, ENT_QUOTES, $GLOBALS['chset'])
        );
    }

    if (!headers_sent()) {
        switch ($http_status) {
            case 401:
                header('WWW-Authenticate: Basic realm="AVReg server"', true, 401);
                exit();
                break;
            case 403:
            default:
                header('Content-Type: text/html; charset=' . $chset);
                header('Connection: close', true, 403);
        }
    }

    print_syslog(LOG_CRIT, 'access denided: ' . basename($_SERVER['SCRIPT_FILENAME']));
    print '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' . "\n";
    print '<html><head>' . "\n";
    print '<link rel="SHORTCUT ICON" href="' . $conf['prefix'] . '/favicon.ico">' . "\n";
    print '<title>403 error::Access Denided</title>' . "\n";
    print '<meta http-equiv="Content-Type" content="text/html; charset=' . $GLOBALS['chset'] . '">' . "\n";
    print "</head><body>\n";
    print '<img src="' . $GLOBALS['conf']['prefix'] . '/img/password.gif"
        width="48" height="48" border="0"><p><font color="red" size=+1>' . $deny_reason . '</font></p>' . "\n";
    print "</body></html>\r\n";
    exit();
}

/**
 *
 * функция проверяющая входит ли число в промежуток
 * @param unknown_type $int чило
 * @param unknown_type $min минимальное число
 * @param unknown_type $max максимальное число
 * @return bool
 */
function checkIntRange($int, $min, $max)
{
    if ($int == null or $h < $min or $h > $max) {
        return false;
    } else {
        return true;
    }
}

/**
 * Функция возвращает текстовое представление месяца
 *
 * @param int $m месяц от 1 до 12
 * @return string
 */
function getMonth($m)
{
    if (empty ($m) or (($m < 0) or ($m > 12))) {
        $retval = $GLOBALS['charNull'];
    } else {
        $retval = $GLOBALS['month_array'][$m - 1];
    }
    return $retval;
}

/**
 * Функция возвращает текстовое представление дня
 *
 * @param int $d день от 1 до 31
 * @return string
 */
function getDay($d)
{
    if (empty ($d) or (($d < 1) or ($d > 31))) {
        $retval = $GLOBALS['charNull'];
    } else {
        $retval = sprintf('%02u', $d);
    }
    return $retval;
}

/**
 * Функция возвращает текстовое представление часа
 *
 * @param int $h час от 1 до 23
 * @return string
 */
function getHour($h)
{
    if ($h == null or $h < 0 or $h > 23) {
        $retval = $GLOBALS['charNull'];
    } else {
        $retval = sprintf('%02u', $h);
    }
    return $retval;
}

/**
 * Функция возвращает текстовое представление минут
 *
 * @param int $_min минуты от 1 до 59
 * @return string
 */
function getMinute($_min)
{
    if ($_min == null or $_min < 0 or $_min > 59) {
        $retval = $GLOBALS['charNull'];
    } else {
        $retval = sprintf('%02u', $_min);
    }
    return $retval;
}

/**
 * Функция возвращает текстовое представление дня недели
 *
 * @param string $_weekday перечисление дней в неделе
 * @return string
 */
function getWeekday($_weekday)
{
    $retval = '';
    if ($_weekday == '') {
        $retval = $GLOBALS['charNull'];
    } else {
        $pieces = explode(",", $_weekday);
        foreach ($pieces as $_day) {
            $rrr[] = $GLOBALS['day_of_week'][$_day];
        }
        $retval = implode(",", $rrr);
    }
    return $retval;
}

/**
 *
 * Функция формирует и возвращает елемент select
 * @param string $_name названи
 * @param array $value_array масив значений
 * @param bool $_multiple разрешить множественный выбор
 * @param int $_size размер
 * @param int $start_val начальное значение
 * @param string $selected выбранные значения
 * @param bool $first_empty пустое первое значение
 * @param string $onch функция onchange
 * @param string $TITLE заголовок
 * @return string
 */
function getSelectHtml(
    $_name,
    $value_array,
    $_multiple = false,
    $_size = 1,
    $start_val = 1,
    $selected = '',
    $first_empty = true,
    $onch = false,
    $TITLE = null
) {
    settype($selected, 'string');
    if ($_multiple) {
        $m = 'multiple="multiple"';
    } else {
        $m = '';
    }
    if ($onch === true) {
        $onch = 'onchange="this.form.submit()"';
    } elseif (!empty($onch)) {
        $onch = 'onchange="' . $onch . '"';
    } else {
        $onch = '';
    }
    if (!empty($TITLE)) {
        $_title = 'title="' . $TITLE . '"';
    } else {
        $_title = '';
    }
    $a = sprintf(
        '<select name="%s" id="%s" %s size="%d" %s %s>' . "\n",
        $_name,
        $_name,
        $m,
        $_size,
        $_title,
        $onch
    );

    if ($first_empty) {
        $a .= '<option></option>' . "\n";
    }
    $_cnt = $start_val;
    foreach ($value_array as $_element) {
        if ($selected != '') {
            if ($_multiple) {
                $_y = false;
                $ar = explode(',', $selected);
                foreach ($ar as $sss) {
                    if ($_cnt == $sss) {
                        $_y = true;
                        break;
                    }
                }
                if ($_y) {
                    $a .= '<option value="' . $_cnt . '" selected>' . $_element . '</option>' . "\n";
                } else {
                    $a .= '<option value="' . $_cnt . '">' . $_element . '</option>' . "\n";
                }
            } else {
                /* not multiple */
                if ($_element == $selected) {
                    $a .= '<option value="' . $_cnt . '" selected>' . $_element . '</option>' . "\n";
                } else {
                    $a .= '<option value="' . $_cnt . '">' . $_element . '</option>' . "\n";
                }
            }
        } else { // not selected
            $a .= '<option value="' . $_cnt . '">' . $_element . '</option>' . "\n";
        }
        $_cnt++;
    }
    $a .= '</select>' . "\n";
    return $a;
}

/**
 *
 * Функция формирует и возвращает елемент select используя значения
 * @param string $_name названиe
 * @param array $value_array масив значений
 * @param bool $_multiple разрешить множественный выбор
 * @param int $_size размер
 * @param int $start_val начальное значение
 * @param string $selected выбранные значения
 * @param bool $first_empty пустое первое значение
 * @param string $onch функция onchange
 * @param string $text_prefix префикс
 * @param string $TITLE заголовок
 * @return string
 */
function getSelectHtmlByName(
    $_name,
    $value_array,
    $_multiple = false,
    $_size = 1,
    $start_val = 0,
    $selected = '',
    $first_empty = true,
    $onch = false,
    $text_prefix = '',
    $TITLE = null,
    $cams_srcs = null
) {
    if ($_multiple) {
        $m = 'multiple="multiple"';
    } else {
        $m = '';
    }
    if ($onch === true) {
        $onch = 'onchange="this.form.submit()"';
    } elseif (!empty($onch)) {
        $onch = 'onchange="' . $onch . '"';
    } else {
        $onch = '';
    }
    if (isset($TITLE) && !empty($TITLE)) {
        $_title = 'title="' . $TITLE . '"';
    } else {
        $_title = '';
    }

    $posn = strpos($_name, '[]');
    if ($posn !== false) {
        $_id = substr_replace($_name, '', $posn);
        $_class = $_id;
    } else {
        $_id = $_name;
    }
    $_class = $_id;

    $a = sprintf(
        '<select name="%s" id="%s" class="%s" %s size="%d" %s %s>' . "\n",
        $_name,
        $_id,
        $_class,
        $m,
        $_size,
        $_title,
        $onch
    );

    if ($first_empty) {
        $a .= '<option> </option>' . "\n";
    }
    $_cnt = $start_val;

    //Если для веб-раскладок => $cams_srcs - содержит алтернативные источники
    if ($cams_srcs != null) {

        foreach ($value_array as $_element) {
            $set_src_type = 0;
            if ($selected != null) {
                if ($_multiple) {
                    $_y = false;
                    $ar = explode(',', $selected[0]);
                    foreach ($ar as $sss) {
                        if ($_cnt == $sss) {
                            $_y = true;
                            break;
                        }
                    }
                    if ($_y) {
                        $a .= '<option value="' . $_element . '" selected>' . $text_prefix . $_element . '</option>' .
                            "\n";
                    } else {
                        $a .= '<option value="' . $_element . '">' . $text_prefix . $_element . '</option>' . "\n";
                    }
                } else {
                    /* not multiple */
                    if ($_element == $selected[0]) {
                        $a .= '<option value="' . $_element . '" selected>' . $text_prefix . $_element . '</option>' .
                            "\n";
                    } else {
                        $a .= '<option value="' . $_element . '">' . $text_prefix . $_element . '</option>' . "\n";
                    }
                }
            } else { // not selected
                $a .= '<option value="' . $_element . '">' . $text_prefix . $_element . '</option>' . "\n";
            }
            $_cnt++;
        }
        $a .= '</select>' . "\n";


        $set_src_type = (isset($selected[1]) ? $selected[1] : 0);
        $visi_val = $set_src_type != 0 ? 'visible' : 'hidden';

        $a .= '<br />';
        //Добавление типа источника камеры для веб-раскладок
        if ($cams_srcs != false && (isset($cams_srcs[@$selected[0]]['avregd']) ||
                isset($cams_srcs[@$selected[0]]['alt_1']) || isset($cams_srcs[@$selected[0]]['alt_2']))) {

            $a .= '<select class="mon_wins_type" name="mon_wins_type[]" size="' . $_size . '" ' . $_title
                . ' style="font-size:8pt; visibility:' . $visi_val . ';" >' . "\n";

            if (isset($cams_srcs[$selected[0]]['avregd']) && $cams_srcs[$selected[0]]['avregd'] == 'true') {
                $a .= '<option ' . ($set_src_type == 1 ? 'selected="selected"' : '') . ' value="1">avregd</option>' .
                    "\n";
            }
            if (isset($cams_srcs[$selected[0]]['alt_1']) && $cams_srcs[$selected[0]]['alt_1'] == 'true') {
                $a .= '<option ' . ($set_src_type == 2 ? 'selected="selected"' : '') . ' value="2">alt 1</option>' .
                    "\n";
            }
            if (isset($cams_srcs[$selected[0]]['alt_2']) && $cams_srcs[$selected[0]]['alt_2'] == 'true') {
                $a .= '<option ' . ($set_src_type == 3 ? 'selected="selected"' : '') . ' value="3">alt 2</option>' .
                    "\n";
            }
            $a .= '</select>' . "\n";
        }

    } else {

        foreach ($value_array as $_element) {
            if ($selected != '') {
                if ($_multiple) {
                    $_y = false;
                    $ar = explode(',', $selected);
                    foreach ($ar as $sss) {
                        if ($_cnt == $sss) {
                            $_y = true;
                            break;
                        }
                    }
                    if ($_y) {
                        $a .= '<option value="' . $_element . '" selected>' . $text_prefix . $_element . '</option>' .
                            "\n";
                    } else {
                        $a .= '<option value="' . $_element . '">' . $text_prefix . $_element . '</option>' . "\n";
                    }
                } else {
                    /* not multiple */
                    if ($_element == $selected) {
                        $a .= '<option value="' . $_element . '" selected>' . $text_prefix . $_element . '</option>' .
                            "\n";
                    } else {
                        $a .= '<option value="' . $_element . '">' . $text_prefix . $_element . '</option>' .
                            "\n";
                    }
                }
            } else { // not selected
                $a .= '<option value="' . $_element . '">' . $text_prefix . $_element . '</option>' . "\n";
            }
            $_cnt++;
        }
        $a .= '</select>' . "\n";
    }

    return $a;
}

/**
 *
 * Функция формирует и возвращает елемент select используя значения
 * @param string $_name названи
 * @param unknown_type $assoc_array
 * @param bool $_multiple разрешить множественный выбор
 * @param int $_size размер
 * @param int $start_val начальное значение
 * @param string $selected выбранные значения
 * @param bool $first_empty пустое первое значение
 * @param string $onch функция onchange
 * @param string $text_prefix префикс
 * @param string $TITLE заголовок
 * @param bool $reverse использовать обратные ключи-значения
 * @return string
 */
function getSelectByAssocAr(
    $_name,
    $assoc_array,
    $_multiple = false,
    $_size = 1,
    $start_val = null,
    $selected = null,
    $first_empty = true,
    $onch = false,
    $text_prefix = null,
    $TITLE = null,
    $reverse = false
) {
    $array_cnt = count($assoc_array);
    if ($array_cnt == 0) {
        return '';
    }
    if ($_multiple) {
        $m = 'multiple="multiple"';
    } else {
        $m = '';
    }
    if ($onch === true) {
        $onch = 'onchange="this.form.submit()"';
    } elseif (!empty($onch)) {
        $onch = 'onchange="' . $onch . '"';
    } else {
        $onch = '';
    }
    if (!empty($TITLE)) {
        $_title = 'title="' . $TITLE . '"';
    } else {
        $_title = '';
    }
    $a = sprintf(
        '<select name="%s" id="%s" %s size="%d" %s %s>' . "\n",
        $_name,
        $_name,
        $m,
        $_size,
        $_title,
        $onch
    );

    if ($first_empty) {
        $a .= '<option></option>' . "\n";
    }
    foreach ($assoc_array as $k => $v) {
        settype($key, 'string');
        if ($reverse) {
            $key = & $v;
            $value = & $k;
        } else {
            $key = & $k;
            $value = & $v;
        }

        if ($selected != '') {
            if ($_multiple) {
                $_y = false;
                $ar = explode(',', $selected);
                foreach ($ar as $sss) {
                    if ($key == $sss) {
                        $_y = true;
                        break;
                    }
                }
                if ($_y) {
                    $a .= '<option value="' . $key . '" selected>' . $text_prefix . $value . '</option>' . "\n";
                } else {
                    $a .= '<option value="' . $key . '">' . $text_prefix . $value . '</option>' . "\n";
                }
            } else {
                /* not multiple */
                if ($key == $selected) {
                    $a .= '<option value="' . $key . '" selected>' . $text_prefix . $value . '</option>' . "\n";
                } else {
                    $a .= '<option value="' . $key . '">' . $text_prefix . $value . '</option>' . "\n";
                }
            }
        } else { // not selected
            $a .= '<option value="' . $key . '">' . $text_prefix . $value . '</option>' . "\n";
        }
    }

    $a .= '</select>' . "\n";
    return $a;
}

/**
 *
 * Функция формирует разметку с набором чекбоксов для выбора значений
 * @param unknown_type $_name базовое имя для идентификации эл-тов
 * @param unknown_type $assoc_array ассоциативный массив с названиями и значениями чекбоксов
 * @param unknown_type $selected строка с номерами п/п чекнутых эл-тов
 * @param bool|\unknown_type $show_select_all выводить чекбокс выбора/сброса всех чекбоксов
 * @param unknown_type $_size кол-во одновременно отображаемых чекбоксов(без учета "Выбрать все")
 * @param unknown_type $text_prefix строка, добавляемая вначало к названию всех чекбоксов
 * @param bool|\unknown_type $reverse поменять местами использование ключей и значеий ассоц. массива при генерации
 * разметки
 * чекбоксов
 * @return string результирующая разметка
 */
function getChkbxByAssocAr(
    $_name,
    $assoc_array,
    //выбранные значения
    $selected = null,
    //Выбор всех значений
    $show_select_all = true,
    //кол-во отображаемых чекбоксов
    $_size = null,
    $text_prefix = null,
    //меняет местами ключи со значениями в рез-й разметке
    $reverse = false
) {
    $dict = array(
        'select_all' => "Выбрать все",
        'deselect_all' => "Снять выбор"
    );

    $array_cnt = count($assoc_array);
    if ($array_cnt == 0) {
        return '';
    }

    $_size = (int)$_size;

    $a = '<div id="id_main_' . $_name . '" class="chkbx_lsl" >' . "\n";

    $cnt_selected_itms = 0; //счетчик чекнутых чекбоксов
    //заголовок с чекбоксом "Выбрать все"
    if ($show_select_all) {
        $a .= '<div id="id_head_' . $_name . '" class="chkbx_lsl_head" >' . "\n";
        $a .= '<div style="text-align:left; clear:both;">' . "\n";
        $a .= '<div style="float:left; position:relative; top:-3px;">';
        $a .= '<input type="checkbox" id="id_' . $_name . '_select_all" name="' . $_name .
            '_select_all" value="select_all" onclick="chbox_select_all(\'' . $_name . '\')" /> ' . "\n";
        $a .= '</div>' . "\n";
        $a .= '<div>';
        $a .= '<label for="id_' . $_name . '_select_all" class="chbox_head " >' . $dict['select_all'] .
            '</label><br />' . "\n";
        $a .= '</div>' . "\n";
        $a .= '</div>' . "\n";
        $a .= '</div>' . "\n";
    }

    //контейнер набора чекбоксов
    $a .= '<div id="id_' . $_name . '" style="text-align:left; overflow-y:auto; position:relative; width:100%;">' .
        "\n";

    foreach ($assoc_array as $k => $v) {
        settype($key, 'string');

        //реверс - меняем местами ключ-значение
        if ($reverse) {
            $key = & $v;
            $value = & $k;
        } else {
            $key = & $k;
            $value = & $v;
        }

        $a .= '<div style="text-align:left; clear:both;">' . "\n";
        $a .= '<div style="float:left;">';

        //определяем чекнутые эл-ты
        if ($selected != '') {
            $_y = false;
            $ar = explode(',', $selected);

            foreach ($ar as $sss) {
                if ($key == $sss) {
                    $_y = true;
                    break;
                }
            }

            //генерируем разметку чекбоксов
            if ($_y) {
                $a .= '<input type="checkbox" class="chbox_itm " id="id_' . $_name . $key . '"  name="' . $_name .
                    '[]" onclick="chbox_itm_clk(\'' . $_name . '\')" value="' . $key . '" checked />' . "\n";
                $cnt_selected_itms++;
            } else {
                $a .= '<input type="checkbox" class="chbox_itm " id="id_' . $_name . $key . '" name="' . $_name .
                    '[]" onclick="chbox_itm_clk(\'' . $_name . '\')" value="' . $key . '" />' . "\n";
            }
        } else { // not selected
            $a .= '<input type="checkbox" class="chbox_itm " id="id_' . $_name . $key . '" name="' . $_name .
                '[]" onclick="chbox_itm_clk(\'' . $_name . '\')" value="' . $key . '" />' . "\n";
        }
        $a .= '</div>';
        //генерируем разметку подписей чекбоксов
        $a .= '<div><label for="id_' . $_name . $key . '" class="class_' . $_name . ' ">' . $text_prefix . $value .
            '</label>' . "\n";
        $a .= '</div>';
        $a .= '</div>';
    }

    $a .= '</div>' . "\n";
    $a .= '</div>' . "\n";

    //клиентские скрипты поведения и начального состояния
    $a .= '<script type="text/javascript">' . "\n";
    $a .= "initCheckBox('" . $_name . "', $_size) ";
    $a .= '</script>' . "\n";
    return $a;
}

/**
 *
 * Функция
 * @param string $bin_str
 * @return string
 */
function getBinString($bin_str)
{
    if (strlen($bin_str) == 0) {
        return $bin_str;
    }
    $ret = str_replace("\x00", '\0', $bin_str);
    $ret = str_replace("\x08", '\b', $ret);
    $ret = str_replace("\x0a", '\n', $ret);
    $ret = str_replace("\x0d", '\r', $ret);
    $ret = str_replace("\x1a", '\Z', $ret);
    return $ret;
}

/**
 *
 * Функция проверяющая доступ по паролю
 * @param string $saved_pw сохраненный пароль
 * @param string $pw проверяющий пароль
 */
function check_passwd($saved_pw, $pw)
{
    if ($saved_pw == '' && $pw == '') {
        return;
    }

    $z = crypt($saved_pw, $pw);
    if ($z !== $pw) {
        DENY(null, 401);
    }
}

if (!empty($logout)) {
    // print_syslog(LOG_CRIT, '_COOKIE[avreg_logout]: [' . $_COOKIE['avreg_logout'] . ']');
    if (isset($_COOKIE) && empty($_COOKIE['avreg_logout'])) {
        // ob_start();
        header('WWW-Authenticate: Basic realm="AVReg server"', true, 401);
        setcookie('avreg_logout', 1); // FIXME google chrome не воспринимает
    } else {
        $https = (0 === strpos($_SERVER['SERVER_PROTOCOL'], 'HTTPS'));
        $protocol = (!empty($_SERVER['SSL_PROTOCOL']) || ($_SERVER['SERVER_PORT'] != 80)) ?
            (':' . $_SERVER['SERVER_PORT']) : '';
        header(
            sprintf(
                'Location: %s://%s%s%s%s',
                !empty($_SERVER['SSL_PROTOCOL']) ? 'https' : 'http',
                $_SERVER['SERVER_NAME'],
                $protocol,
                $conf['prefix'],
                '/index.php'
            )
        );
        setcookie('avreg_logout', 0);
    }
    setcookie('as_guest', 0, -100);
    unset($_COOKIE['as_guest']);

    exit();
}

//режим гостя
$GuestAuth = false;
$as_guest = false;
if (isset($_GET['user'])) {
    $as_guest = trim($_GET['user']);
    setcookie('as_guest', $as_guest);
}
if (isset($_COOKIE['as_guest']) && !$as_guest) {
    $as_guest = trim($_COOKIE['as_guest']);
}
if (isset($as_guest)) {

    foreach ($GLOBALS['users'] as $key => $val) {
        if ($val["USER"] == $as_guest && $val["GUEST"] == 1) {

            $GuestAuth = true;

            $_SERVER['REMOTE_USER'] = $_SERVER['PHP_AUTH_USER'] = $as_guest;
            break;
        }
    }
}

/// разрешить альтернативный аутентификацию
$ExternalAuth = false;
if (!empty($_SERVER['AUTH_TYPE']) && !empty($_SERVER['REMOTE_USER'])
    && !empty($conf['ExternalAuthMap'])
    && file_exists($conf['ExternalAuthMap']) && $GuestAuth == false
) {

    $lines = file($conf['ExternalAuthMap']);
    foreach ($lines as $line) {
        list($ruser, $auser) = explode('=', trim($line));
        if (trim($ruser) == $_SERVER['REMOTE_USER']) {
            $ExternalAuth = true;
            break;
        }
    }

    if ($ExternalAuth !== false) {
        $_SERVER['PHP_AUTH_USER'] = $_SERVER['REMOTE_USER'] = trim($auser);
    } else {
        $_SERVER['PHP_AUTH_USER'] = $_SERVER['REMOTE_USER'];
        DENY(null, 403);
    }
}

if (isset($_SERVER['PHP_AUTH_USER'])) {
    require_once($wwwdir . 'lib/utils-inet.php');
    if (!isset($_SERVER['REMOTE_USER'])) {
        $_SERVER['REMOTE_USER'] = $_SERVER['PHP_AUTH_USER'];
    }

    $user_info = avreg_find_user(ip2long($_SERVER['REMOTE_ADDR']), -1, $_SERVER['PHP_AUTH_USER']);

    if ($ExternalAuth === false && $GuestAuth === false) {
        if ($user_info !== false) {
            check_passwd($_SERVER['PHP_AUTH_PW'], $user_info['PASSWD']);
        } else {
            DENY(null, 403);
        }
    }

    $login_user = & $_SERVER['REMOTE_USER'];
    $user_status = & $user_info['STATUS'];
    $login_user_name = & $row['LONGNAME'];
    $login_host = & $remote_addr;
    $allow_cams = parse_dev_acl($user_info['ALLOW_CAMS']);
    if (is_array($allow_cams) && count($allow_cams) > 0) {
        $GCP_cams_list = @implode(',', $allow_cams);
    } else {
        $GCP_cams_list = null;
    }
    if ($user_status <= $install_status && !$GuestAuth) {
        $install_user = true;
    }
    if ($user_status <= $admin_status && !$GuestAuth) {
        $admin_user = true;
    }
    if ($user_status <= $arch_status) {
        $arch_user = true;
    }
    if ($user_status <= $operator_status) {
        $operator_user = true;
    }
    if ($user_status <= $viewer_status) {
        $viewer_user = true;
    }

    $allow_pda = $user_info['PDA'];

} else {
    DENY(null, 401);
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
