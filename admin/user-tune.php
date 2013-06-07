<?php
/**
 * @file admin/user-tune.php
 * @brief Настройка доступа к веб-интерфейсу (http://...) видеосервера
 * Редактирование пользователя
 */
/// Файл переводов
$lang_file = '_admin_users.php';
require('../head.inc.php');
DENY($admin_status);
require_once('../lib/utils-inet.php');
?>

<script type="text/javascript" language="javascript">
	<!--
	function reset_to_list() {
		window.open('<?php echo $conf['prefix']; ?>/admin/user-list.php', target = '_self');
	}
	// -->
</script>

<?php

echo '<h1>' . sprintf($r_users, $named, $sip) . '</h1>' . "\n";
if (isset($cmd) && isset($u_host) && isset($u_name) && isset($groups)) {
	$limit_kbps = null;
	require('user-check.inc.php');
	switch ($cmd) {
		case 'UPDATE_USER':
			$guest = isset($guest);
			$pda = isset($pda);
			$result = $adb->update_user(
				$u_host,
				$u_name,
				$u_pass,
				$groups,
				$guest,
				$pda,
				$u_devacl,
				$u_layouts,
				$u_forced_saving_limit,
				$sessions_per_user,
				$limit_fps,
				$nonmotion_fps,
				$limit_kbps,
				$session_time,
				$session_volume,
				$u_longname,
				$remote_addr,
				$login_user,
				$old_u_host,
				$old_u_name
			);
			break;
		default:
			die('crack?');
	}


	if ($result) {
		print '<p class="HiLiteWarn">' . sprintf($fmtUserUpdated, $u_name, $u_host) . '</p>' . "\n";
		print '<div class="warn">' . $strOnUsersUpdateMsg . "</div>\n";
		print '<br><center><a href="' . $conf['prefix'] . '/admin/user-list.php">' . $l_user_list . '</a><center>' . "\n";
	} else {
		print '<p class="HiLiteErr">' . sprintf($fmtUserUpdated2, $u_name, $u_host, "DB:error") .
			'</p>' . "\n";
		print '<br><center><a href="javascript:window.history.back();" title="' . $strBack . '">' .
			'<img src="' . $conf['prefix'] . '/img/undo_dark.gif" alt="' . $strBack .
			'" width="24" hspace="24" border="0"></a></center>' . "\n";
	}


	unset($u_name);
}

if (isset($u_name) && !empty($u_name)) {
	$ui = get_user_info($u_host, $u_name);
	if ($ui === false) {
		die('crack?');
	}


	//tohtml($ui);
	$user2html = stripslashes(htmlspecialchars($ui['USER'], ENT_QUOTES, $chset));
	$host2html = stripslashes(htmlspecialchars($ui['HOST'], ENT_QUOTES, $chset));
	$longname2html = stripslashes(htmlspecialchars($ui['LONGNAME'], ENT_QUOTES, $chset));
	$passwd2html = stripslashes(htmlspecialchars($ui['PASSWD'], ENT_QUOTES, $chset));
	$u_devacl = stripslashes(htmlspecialchars($ui['ALLOW_CAMS'], ENT_QUOTES, $chset));
	$guest = stripslashes(htmlspecialchars($ui['GUEST'], ENT_QUOTES, $chset));
	$pda = stripslashes(htmlspecialchars($ui['PDA'], ENT_QUOTES, $chset));

	//Инициализация доступных раскладок //--->   
	$u_layouts = stripslashes(htmlspecialchars($ui['ALLOW_LAYOUTS'], ENT_QUOTES, $chset));

	$u_forced_saving_limit = $ui['MAX_FORCED_REC_MINUTES'];
	$u_status = $ui['STATUS'];
	$sessions_per_user = $ui['MAX_MEDIA_SESSIONS_NB'];
	$limit_fps = $ui['MAX_VIDEO_FPS'];
	$nonmotion_fps = $ui['MAX_VIDEO_NONMOTION_FPS'];
	$limit_kbps = $ui['MAX_MEDIA_SESSION_RATE_KB'];
	$session_time = $ui['MAX_MEDIA_SESSION_MINUTES'];
	$session_volume = $ui['MAX_MEDIA_SESSION_VOLUME_MB'];

	echo '<h2>' . sprintf($fmtUserTune, $ui['USER'], $ui['HOST']) . '</h2>' . "\n";
	print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";

	//Загрузка таблицы редактирования профиля пользователя
	require '_user_data_tbl.inc.php';

	print '<br>' . "\n";
	print '<input type="hidden" name="cmd" value="UPDATE_USER">' . "\n";
	print '<input type="hidden" name="old_u_name" value="' . $user2html . '">' . "\n";
	print '<input type="hidden" name="old_u_host" value="' . $host2html . '">' . "\n";
	print '<input type="hidden" name="old_u_passwd" value="' . $passwd2html . '">' . "\n";
	print '<input type="submit" name="submit_btn" value="' . $strSave . '">' . "\n";
	print '<input type="reset" name="reset_btn" value="' . $strRevoke . '" onclick="reset_to_list();">' . "\n";
	print '</form>' . "\n";
}

// phpinfo ();
require('../foot.inc.php');
?>
