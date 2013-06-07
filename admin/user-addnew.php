<?php
/**
 * @file admin/user-addnew.php
 * @brief Настройка доступа к веб-интерфейсу (http://...) видеосервера
 *
 * Добавление нового пользователя
 */
/// Файл переводов
$lang_file = '_admin_users.php';
require('../head.inc.php');
DENY($admin_status);
require_once('../lib/utils-inet.php');
?>

<script type="text/javascript">
	<!--
	function reset_to_list() {
		window.open('<?php echo $conf['prefix']; ?>/admin/user-list.php', target = '_self');
	}
	// -->
</script>

<?php
echo '<h1>' . sprintf($r_users, $named, $sip) . '</h1>' . "\n";

if (isset($cmd) && isset($u_host) && isset($u_name) && isset($groups)) {
	$limit_kbps = null; // FIXME if readonly
	require('user-check.inc.php');
	switch ($cmd) {
		case 'ADD_NEW_USER':
			$guest = isset($guest);
			$pda = isset($pda);
			$result = $adb->add_user(
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
				$login_user
			);
			break;
		default:
			die('crack');
	}
	// print ($query);


	if ($result) {
		print '<p class="HiLiteWarn">' . sprintf($fmtUserAdded, $u_name, $u_host) . '</p>' . "\n";
		print '<div class="warn">' . $strOnUsersUpdateMsg . "</div>\n";
		print '<br><center><a href="' . $conf['prefix'] . '/admin/user-list.php">' . $l_user_list . '</a><center>' . "\n";
	} else {
		print '<div class="error">' . sprintf($fmtUserAddErr2, $u_name, $u_host, "DB: error") . "</div>\n";
		print_go_back();
	}


	require('../foot.inc.php');
	exit;
} else {
	if (isset($cmd)) {
		print '<p class="HiLiteErr">' . $strInvalidFormParams . '</p>' . "\n";
		print_go_back();
	}
}

echo '<h2>' . $r_user_add . '</h2>' . "\n";

if (isset($status)) {
	if (!settype($status, 'int')) {
		die();
	}
} else {
	$status = -1;
}

if (!isset($u_name) || empty($u_name)) {
	print '<form id="user_info_frm" action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
	print '<table cellspacing=0 border=1 cellpadding=5>' . "\n";
	$u_status = & $status;
	$user2html = $host2html = $longname2html = $passwd2html = $u_devacl = $u_forced_saving_limit = $guest = $pda = null;
	$sessions_per_user = $limit_fps = $nonmotion_fps = $limit_kbps = $session_time = $session_volume = null;

	require '_user_data_tbl.inc.php';

	print '<br>' . "\n";
	print '<input type="hidden" name="cmd" value="ADD_NEW_USER">' . "\n";
	print '<input type="submit" name="submit_btn" value="' . $strAddUser . '">' . "\n";
	print '<input type="reset" name="reset_btn" value="' . $strRevoke . '" onclick="reset_to_list();">' . "\n";
	print '</form>' . "\n";
}

// phpinfo ();
require('../foot.inc.php');
?>
