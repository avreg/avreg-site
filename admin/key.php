<?php
/**
 * @file admin/key.php
 * @brief Ключ защиты определяет разрешённые (оплаченные) возможности видеорегистратора.
 */
require('../head.inc.php');
/**
 *
 * Функция возвращает mac-адресс ethernet устройства
 */
function getmacs()
{
	$mac = '<not ethernet card>';
	$v = popen($GLOBALS['GETMAC'], 'r');
	$mac = fread($v, 12);
	pclose($v);
	return $mac;
}

echo '<h1>' . $r_key . '</h1>' . "\n";

if (!isset($conf['key']) || empty($conf['key'])) {
	echo '<div class="error">key file not defined, see "key" param in avreg.conf</div>' . "\n";
} else if (!file_exists($conf['key'])) {
	echo '<div class="error">' . sprintf($key_not_found, $conf['key']) . '</div>' . "\n";
} else {
	if (!is_readable($conf['key'])) {
		echo '<div class="error">' . sprintf($key_not_rd, $conf['key']) . '</div>' . "\n";
	} else {
		echo '<h2>' . sprintf($r_key_state, $conf['key']) . '</h2>' . "\n";
		clearstatcache();
		$keyfile = fopen($conf['key'], 'r');
		if (false === $keyfile) {
			die();
		}
		$key_array = array();
		$key_opts = '';
		while (!feof($keyfile)) {
			$line = trim(fgets($keyfile, 1024));
			if (preg_match('/^key_ver5_part1=(.*)$/i', $line, $matches)) {
				$key_opts = $matches[1];
				break;
			}
			$key_array[] = htmlentities($line, ENT_QUOTES, $chset) . '<br />' . "\n";
		}
		fclose($keyfile);

		if (!empty($key_opts)) {
			if (0 === strpos($key_opts, 'xx')) {
				$ip_cmd = sprintf('%s addr show primary label eth*', $conf['ip']);
				echo "<div class='info'>$trial_key</div>\n";
				echo "<pre class='tty'>$ $ip_cmd\n";
				passthru($ip_cmd);
				echo "</pre>\n";
			} else {
				if (preg_match(
					'/^(([0-9A-Fa-f]{2}[:-]?){5}[0-9A-Fa-f]{2})-(([0-9A-Fa-f]{2}:?){4})$/',
					$key_opts,
					$matches
				)
				) {
					$bind_mac = $matches[1]; // /sys/class/net/eth?/address
					$mac_opts = $matches[3];
					sscanf(
						$mac_opts,
						"%x:%x:%x:%x",
						$net_videos,
						$v4l_videos,
						$net_audios,
						$analog_audios
					);
					system(
						sprintf(
							'%s link show primary label eth* | %s -iFq \'%s\'',
							$conf['ip'],
							$conf['grep'],
							$bind_mac
						),
						$retval
					);
					if ($retval !== 0) {
						echo '<div class="error">' . $key_alien . '</div>' . "\n";
					} else {
						// print
						echo '<div class="info">' . "\n";
						foreach ($key_array as &$line) {
							echo($line);
						}
						echo '<br />' . "\n";
						echo sprintf($strKeyOptions, $net_videos, $v4l_videos, $net_audios, $analog_audios);
						echo '</div>' . "\n";
					}
				} else {
					echo '<div class="error">' . $key_bad . '</div>' . "\n";
				}
			}
		}
	}
}
print '<p align="center" style="font-weight:bold;">' . "\n";
print $DEV_FIRMS . "\n";
print '<br /><a href="http://avreg.net/" target="_blank">http://avreg.net</a>' . "\n";
print '</p>' . "\n";


// phpinfo();
require('../foot.inc.php');
?>
