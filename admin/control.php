<?php
/**
 * @file admin/control.php
 * @brief Управление основной программой «avregd» и контроль её состояния.
 *
 */
require('../head.inc.php');
DENY($admin_status);
require('warn.inc.php');
require('_vidserv_status.inc.php');

$upstart_used = file_exists('/etc/init/avreg.conf');

/*
printf ("current character set is %s\n", $charset);
 */

if (isset($AVREG_PROFILE)) {
	$profile = & $AVREG_PROFILE;
}

/**
 *
 * Функция выводит информацию из лог-файла
 */
function print_log_messages()
{
	if (!empty($GLOBALS['profile'])) {
		$avreg_flt = ' avreg | ' . $GLOBALS['conf']['grep'] . ' ' . $GLOBALS['profile'] . ' | ';
	} else {
		$avreg_flt = ' avreg | ';
	}

	$_cmd = $GLOBALS['conf']['sudo'] . ' ' .
		$GLOBALS['conf']['tail'] . ' -n 200 ' . $GLOBALS['conf']['daemon-log'] .
		' | ' . $GLOBALS['conf']['grep'] . $avreg_flt . $GLOBALS['conf']['tail'] . ' -n 50';

	$logfile = popen($_cmd . ' 2>&1', 'r');
	print '<div class="tty">' . "\n";
	print '<span style="color: #66FF00;">' . '$ ' . htmlspecialchars(
			str_replace(array('/bin/', '/usr'), '', $_cmd),
			ENT_QUOTES,
			$chset
		) . '</span><br>' . "\n";
	while (!feof($logfile)) {
		$buffer = fgets($logfile, 1024);
		if (preg_match(
			'/crit|err|fail|error|warn|invalid|wrong|bad|unable|notice|could`t|could not| no |cannot|can`t|not|duplicate|reset|reject|drop|unsupport|bnormal/i',
			$buffer
		)
		) {
			print '<font color="#FFFF99">' . htmlspecialchars($buffer, ENT_QUOTES, $chset) . '</font><br>';
		} else {
			print htmlspecialchars($buffer, ENT_QUOTES, $chset) . '<br>';
		}
	}
	pclose($logfile);
	print '</div><br />' . "\n";
}

echo '<h1>' . $r_control . '</h1>' . "\n";
/// Флаг выполнения команды
$cmd_released = null;
if (!empty($cmd)) {
	$status_cmd = get_full_cmd($upstart_used, 'status', $profile);
	unset($outs);
	exec($GLOBALS['conf']['sudo'] . ' ' . $status_cmd, $outs, $retval);
	if ($upstart_used) {
		// avreg-worker (cpu1) start/running, process 6208
		// avreg-worker start/running, process 6208
		$running = (count($outs) > 0 && preg_match('@start/running@', $outs[0]));
	} else {
		$running = ($retval === 0) ? true : false;
	}

	if ($running) {
		if (1 === strpos($cmd, 'tart')) {
			$wrn = sprintf($runVservWarn1, $cmd);
			unset($cmd);
		}
	} else {
		if (1 !== strpos($cmd, 'tart')) {
			$wrn = sprintf($runVservWarn2, $cmd);
			unset($cmd);
		}
	}
	if (!isset($cmd)) {
		print ('<p class="HiLiteBigErr">' . $wrn . '</p>');
	}
}

if (isset($cmd)) {
	$cmd = strtolower($cmd);
	if (isset($confirm_btn)) {
		if ($confirm_btn === $strYes) {
			$strwarning = '';
			if ($cmd == 'start') {
				$strwarning = $strRunA;
			} elseif ($cmd == 'restart') {
				$strwarning = $strRestartA;
			} elseif ($cmd == 'reload') {
				$strwarning = $strReloadA;
			} elseif ($cmd == 'stop') {
				$strwarning = $strStopA;
			}

			if (!empty($strwarning)) {
				print '<p><font size="+1" color="' . $warn_color . '">' . $strwarning;
				$fullcmd = get_full_cmd($upstart_used, $cmd, $profile);
				print_syslog(LOG_WARNING, sprintf('command `%s\'', $fullcmd));
				unset($outs);
				while (@ob_end_flush()) {
					;
				}
				exec($conf['sudo'] . ' ' . $fullcmd . ' 2>&1', $outs, $retval);
				if ($retval === 0) {
					print ' OK</font></p>' . "\n";
					$cmd_released = true;
					// print '<p>' . $outline . '</p>' ."\n";
				} else {
					print ' ' . $strError . '</font></p>' . "\n";
					print '<p><font size="+1" color="Red">' . implode('<br />', $outs) . '</font></p>' . "\n";
					$cmd_released = false;
					print '<div style="color:Red;">' . $strCheckLog . '</div>';
					print_log_messages();
				}
			}
		}
	} else {
		if ($cmd == 'start') {
			print '<p class="HiLiteBigWarn">' . sprintf($fnmWarnControl, $strRunW) . '</p>' . "\n";
		} elseif ($cmd == 'restart') {
			print '<p class="HiLiteBigWarn">' . $sViewerRestartWarn . '</p>' . "\n";
			print '<p class="HiLiteBigWarn">' . sprintf($fnmWarnControl, $strRestartW) . '</p>' . "\n";
		} elseif ($cmd == 'reload') {
			print '<p class="HiLiteBigWarn">' . $strReloadW . '</p>' . "\n";
		} elseif ($cmd == 'stop') {
			print '<p class="HiLiteBigWarn">' . sprintf($fnmWarnControl, $strStopW) . '</p>' . "\n";
		} else {
			MYDIE("invalid command");
		}
		print '<div class="warn">' . "\n";
		echo '# ' . get_full_cmd($upstart_used, $cmd, $profile) . '  [?]';
		print '</div><br />' . "\n";
		print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
		print '<input type="hidden" name="cmd" value="' . $cmd . '">' . "\n";
		print '<input type="submit" name="confirm_btn" value="' . $strYes . '">' . "\n";
		if (isset($profile)) {
			print '<input type="hidden" name="profile" value="' . $profile . '">' . "\n";
		}
		if (isset($from)) {
			print '<input type="button" name="confirm_btn" value="' . $strNo . '" onclick="javascript:window.history.back();">' . "\n";
			print '<input type="hidden" name="from" value="' . $from . '">' . "\n";
		} else {
			print '<input type="submit" name="confirm_btn" value="' . $strNo . '">' . "\n";
		}

		print '</form>' . "\n";
		require('../foot.inc.php');
		exit;
	}
}

$daemon_states = print_daemons_status($upstart_used, $profile);

print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST"><FIELDSET>' . "\n";
echo '<h2>' . "\n";
if (isset($AVREG_PROFILE)) {
	$sel_profile = & $AVREG_PROFILE;
	echo $r_conrol_control . ' &#171;' . $conf['daemon-name'] . '&#187;';
} else {
	$profiles_names = array_map(basename, $EXISTS_PROFILES);
	if (count($profiles_names) === 1) {
		echo $r_conrol_control . ' ' . '&#171;' . $conf['daemon-name'] . '&#187;';
	} else {
		echo $r_conrol_control . ' avregd-' . getSelectHtmlByName(
				'profile',
				$profiles_names,
				false,
				1,
				0,
				$profile,
				true,
				true
			);
	}
}

// tohtml($daemon_states);
echo '</h2>' . "\n";
$allow_start = $allow_stop = $allow_reload = 'disabled';
if (empty($profile)) {
	if (false !== array_search(true, $daemon_states, true)) {
		$allow_stop = 'enabled';
		if (1 === count($daemon_states)) {
			$allow_reload = 'enabled';
		}
	} else {
		$allow_start = 'enabled';
	}
} else {
	if ($daemon_states[$profile]) {
		$allow_stop = $allow_reload = 'enabled';
	} else {
		$allow_start = 'enabled';
	}
}

print "<input type=\"submit\" name=\"cmd\" $allow_start class=\"$allow_start\" value=\"Start\">$strRun\n";
print "<br><input type=\"submit\" $allow_stop name=\"cmd\"  class=\"$allow_stop\" value=\"Restart\">$strRestart&nbsp;<img src=\"$conf[prefix]/img/hotsync_busy.gif\" width=\"22\" height=\"22\" align=\"middle\" border=\"0\">&nbsp;<img src=\"$conf[prefix]/img/hotsync.gif\" width=\"22\" height=\"22\" align=\"middle\" border=\"0\">\n";
print "<br><input type=\"submit\" $allow_reload name=\"cmd\"  class=\"$allow_reload\" value=\"Reload\">$strReload&nbsp;<img src=\"$conf[prefix]/img/hotsync.gif\" width=\"22\" height=\"22\" align=\"middle\" border=\"0\">\n";
print "<br><input type=\"submit\" $allow_stop name=\"cmd\"  class=\"$allow_stop\" value=\"Stop\">$strStop\n";
print '</FIELDSET></form>' . "\n";

if ($cmd_released !== false) {
	print '<div>' . $strCheckLog . '</div>';
	print_log_messages();
}

// phpinfo();
require('../foot.inc.php');
?>
