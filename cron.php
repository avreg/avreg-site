<?php

/**
 * @file cron.php
 * @brief Обновление данных в таблице TREE_EVENTS
 *
 * @param '-m' - имя вызываемого метода
 * значения:
 * 1) -m cron_update_tree_events
 *    обновляет таблицу TREE_EVENTS c момента последнего необновленного события в дереве событий, без параметров
 *    пример:    sudo php ~/workspace/avreg-site/cron.php -m cron_update_tree_events
 * 2) -m update_tree_events
 *    частичное обновление таблицы TREE_EVENTS, используется совместно со следующими параметрами:
 *
 * @param '-s' - начало временного диапазона обновления. Если не указан - обновление будет произведено с момента первого соответстующего условиям события
 *    пример: sudo php ~/workspace/avreg-site/cron.php -m update_tree_events -s '2012-09-07 11:00:00'
 *    (будут обновлены все события и для всех камер, начиная с указанного даты и времени
 *    (минуты и секунды игнорируются, т.е. диапазон начинается с указанного часа, 0 минут, 0 секунд)).
 *
 * @param '-e' - конец временного диапазона обновления.  Если не указан - обновление будет произведено до последнего соответстующего условиям события
 *    пример: sudo php ~/workspace/avreg-site/cron.php -m update_tree_events -e '2012-09-07 12:00:00'
 *    (будут обновлены все события и для всех камер, заканчивая указанной датой, и временем до конца указанного часа
 *    (минуты и секунды игнорируются, т.е. диапазон заканчивается в указанный час, 59 минут, 59 секунд)).
 *
 * @param '-c' - номера камер для которых очсуществляется обновление.  Если не указан - обновление будет произведено для всех камер.
 *    пример:  sudo php ~/workspace/avreg-site/cron.php -m update_tree_events -c 2,3
 *    (будут обновлены все события для камер No. 2 и 3).
 *
 *    комплексный пример:    sudo php ~/workspace/avreg-site/cron.php -m update_tree_events -s '2012-09-07 09:00:00' -e '2012-09-07 12:00:00' -c 2,3
 *    (будут обновлены все события для камер No. 2 и 3 в период времени начиная с 2012-09-07 09:00:00 и заканчивая 2012-09-07 12:59:59).
 *
 * @param '-p' - название файла профиля с дополнительными настройками
 *
 */


$methods = array(
	'update_tree_events',
	'cron_update_tree_events',
);
if (in_array('-m', $argv) && isset($argv[array_search('-m', $argv) + 1]) && in_array(
		$argv[array_search('-m', $argv) + 1],
		$methods
	)
) {
	$method = $argv[array_search('-m', $argv) + 1];
	$params = array();
	if (in_array('-s', $argv) && isset($argv[array_search('-s', $argv) + 1])) {
		$params['start'] = $argv[array_search('-s', $argv) + 1];
	}
	if (in_array('-e', $argv) && isset($argv[array_search('-e', $argv) + 1])) {
		$params['end'] = $argv[array_search('-e', $argv) + 1];
	}
	if (in_array('-c', $argv) && isset($argv[array_search('-c', $argv) + 1])) {
		$params['cameras'] = $argv[array_search('-c', $argv) + 1];
	}
	if (in_array('-p', $argv) && isset($argv[array_search('-p', $argv) + 1])) {
		$profile = $argv[array_search('-p', $argv) + 1];
	}

	require('/etc/avreg/site-defaults.php');
	$res = confparse($conf, 'avreg-site');
	if (!$res) {
		die();
	} else {
		$conf = array_merge($conf, $res);
	}

	if (!empty($profile) && $res = confparse($conf, 'avreg-site', $conf['profiles-dir'] . '/' . $profile)) {
		$conf = array_merge($conf, $res);
	}

	$link = null;
	require_once($conf['site-dir'] . '/offline/gallery/memcache.php');
	require_once($conf['site-dir'] . '/offline/gallery/gallery.php');
	$non_config = true;
	require_once($conf['site-dir'] . '/lib/adb.php');

	// Инициализация класа галереи

	$gallery = new Gallery($params);
	$gallery->{$method}($params);
	// Возврат ответа запроса

}

/* $params строковый массив, список параметров, которые нужно читать из файла */
function confparse($_conf, $section = null, $path = '/etc/avreg/avreg.conf', $params = null)
{
	$confile = fopen($path, 'r');
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
			if (1 === preg_match("/^([^\[]+)\[([\"']?)([^\]]*?)([\"']?)\]$/Su", $param, $match2)) {
				/* наш параметр -- массив */
				$param = $match2[1];
				$key = $match2[3];
				$vt = gettype(@$_conf[$param]);
				if (0 !== strcasecmp($vt, 'array')) {
					$res = false;
					break;
				}
				$ret_array[$param][$key] = $value;
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
?>