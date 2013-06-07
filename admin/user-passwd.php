<?php
/**
 * @file admin/user-passwd.php
 * @brief Настройка доступа к веб-интерфейсу (http://...) видеосервера
 * Изменение пароля
 */
/// Файл переводов
$lang_file = '_admin_users.php';
require('../head.inc.php');
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

if (isset($cmd)) {
	if (strcmp($u_name, $login_user) || strcmp($u_host, $login_host)) {
		die ('Crack or hack???');
	}
	$u_host = & $user_info['HOST'];
	switch ($cmd) {
		case 'UPDATE_PASSWD':
			//print "<p>old_pass='$u_pass'<br>user_passwd='$u_pass2'</p>\n";
			$u_pass = trim($u_pass);
			$u_pass2 = trim($u_pass2);
			if (!preg_match($patternPasswd, $u_pass)) {
				print '<p class="HiLiteErr">' . sprintf($fmtPasswdBadChar, $u_name, $u_host) .
					'</p>' . "\n";
				print_go_back();
				require('../foot.inc.php');
				exit;
			}
			if (strcmp($u_pass, $u_pass2)) {
				echo '<p class="HiLiteErr">' . $strPassNotPass2 . '</p>' . "\n";
				print '<p class="HiLiteErr">' . $strAddUserErr1 . '</p>' . "\n";
				print_go_back();
			} else {
				if ($u_host === '127.0.0.1' || $u_host === 'localhost') {
					$hosts = array('127.0.0.1', 'localhost');
				} else {
					$hosts = array($u_host);
				}


				$user_passwd = $adb->get_user_passwd($u_name, $hosts);
				var_dump($user_passwd);
				if ($user_passwd === false) {
					die ("Error\n");
				}
				$z = crypt($old_pass, $user_passwd);
				//print "<p>old_pass='$old_pass'<br>user_passwd='$user_passwd'<br>crypt='$z'</p>\n";
				if (($user_passwd == '' && $old_pass == '') ||
					$z === $user_passwd
				) {
					if (strcmp($old_pass, $u_pass)) {
						$result = $adb->update_user_passwd($u_name, $u_pass, $hosts);

						//print ($query);
						if ($result) {
							print '<p class="HiLiteWarn">' . sprintf(
									$fmtPasswdUpdated,
									$u_name,
									$u_host
								) . '</p>' . "\n";
							print '<br><center><a href="' . $conf['prefix'] . '/admin/user-list.php">' . $l_user_list . '</a><center>' . "\n";
						} else {
							print '<p class="HiLiteErr">' . sprintf(
									$fmtPasswdUpdated2,
									$u_name,
									$u_host
								) . '</p>' . "\n";
							print_go_back();
						}
					} else {
						print '<p class="HiLiteWarn">' . sprintf($fmtPasswdUpdated, $u_name, $u_host) . '</p>' . "\n";
						print '<div class="warn">' . $strOnUsersUpdateMsg . "</div>\n";
						print '<br><center><a href="' . $conf['prefix'] . '/admin/user-list.php">' . $l_user_list . '</a><center>' . "\n";
					}
				} else {
					print '<p class="HiLiteErr">' . sprintf($fmtPasswdUpdated2, $u_name, $u_host) . '</p>' . "\n";
					print_go_back();
				}
			}
			require('../foot.inc.php');
			exit;
			break; /**/
	}
}

echo '<h2>' . sprintf($fmtUserPasswd, $login_user, $login_host, $login_user_name) . '</h2>' . "\n";

print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
print '<table cellspacing=0 border=1 cellpadding=5>' . "\n";
print '<tr>' . "\n";
print '<td>' . $strOld . ' ' . $strPassword . '</td>' . "\n";
print '<td><input type="password" name="old_pass" maxlength="8">' . '&nbsp;</td>' . "\n";
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td>' . $strNew . ' ' . $strPassword . '</td>' . "\n";
print '<td><input type="password" name="u_pass" maxlength="8">' . '&nbsp;</td>' . "\n";
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td>' . $strNew . ' ' . $strPassword2 . '</td>' . "\n";
print '<td><input type="password" name="u_pass2" maxlength="8">' . '&nbsp;</td>' . "\n";
print '</tr>' . "\n";
print '</table>' . "\n";
print '<br>' . "\n";
print '<input type="hidden" name="cmd" value="UPDATE_PASSWD">' . "\n";
print '<input type="hidden" name="u_name" value="' . $login_user . '">' . "\n";
print '<input type="hidden" name="u_host" value="' . $login_host . '">' . "\n";
print '<input type="submit" name="submit_btn" value="' . $strChange . '">' . "\n";
print '<input type="reset" name="reset_btn" value="' . $strRevoke . '" onclick="reset_to_list();">' . "\n";
print '</form>' . "\n";

require('../foot.inc.php');
?>
