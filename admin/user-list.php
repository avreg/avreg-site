<?php
/**
 *
 * @file admin/user-list.php
 *
 * @brief Настройка доступа к веб-интерфейсу (http://...) видеосервера
 * Группы пользователей
 */
/// Файл переводов
$lang_file = '_admin_users.php';
require('../head.inc.php');

echo '<h1>' . sprintf($r_users, $named, $sip) . '</h1>' . "\n";

if (isset($cmd)) {
	$ui = get_user_info($u_host, $u_name);
	if ($ui === false) {
		die('crack?');
	}
	$u_status = $ui['STATUS'];
	if (!$admin_user || !($user_status < $u_status)) {
		die ('Crack or hack???');
	}
	switch ($cmd) {
		case 'DEL':
			echo '<p class="HiLiteBigWarn">' . sprintf(
					$fmtDeleteUserConfirm,
					$u_name,
					$u_host,
					$grp_ar[$u_status]['grname']
				) . '</p>' . "\n";
			print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
			print '<input type="hidden" name="cmd" value="DEL_OK">' . "\n";
			print '<input type="hidden" name="u_host" value="' . $u_host . '">' . "\n";
			print '<input type="hidden" name="u_name" value="' . $u_name . '">' . "\n";
			print '<input type="submit" name="mult_btn" value="' . $strYes . '">' . "\n";
			print '<input type="submit" name="mult_btn" value="' . $strNo . '">' . "\n";
			print '</form>' . "\n";
			require('../foot.inc.php');
			exit;
			break; /**/
		case 'DEL_OK':
			if (($mult_btn == $strYes) && isset($u_host) && isset($u_name) && isset($u_status)) {


				$adb->delete_user($u_name, $u_host, $u_status);

				echo '<p class="HiLiteBigWarn">' . sprintf($fmtDeleteUser, $u_name, $u_host) . '</p>' . "\n";
				print '<div class="warn">' . $strOnUsersUpdateMsg . "</div>\n";
			}
			unset ($u_name);
			break;
	}
}

if (!isset($u_name) || empty($u_name)) {
	/* Performing new SQL query */

	/* Printing results in HTML */
	echo '<h3>' . $r_user_groups . '</h3>' . "\n";
	print '<table cellspacing=0 border=1 cellpadding=5>' . "\n";
	print '<tr bgcolor="' . $header_color . '">' . "\n";
	reset($grp_ar);
	while (list ($grp_status, $groups) = each($grp_ar)) {
		print '<th><a href="#' . $grp_status . '">' . $groups['grname'] . '</th>' . "\n";
	}
	print '</tr></table>' . "\n";

	reset($grp_ar);
	while (list ($grp_status, $groups) = each($grp_ar)) {
		// next($grps);
		// print '<pre>'.$grp_status.'/'.$groups.'</pre>'."\n";
		$result = $adb->get_users($grp_status);

		$num_rows = count($result);
		switch ($grp_status) {
			case 1:
				$bashnia = '<img src="' . $conf['prefix'] . '/img/mozilla-icon.gif" border=0>';
				break;
			case 2:
				$bashnia = '<img src="' . $conf['prefix'] . '/img/gnome-xbill.gif" border=0>';
				break;
			case 3:
				$bashnia = '<img src="' . $conf['prefix'] . '/img/video-x-generic.gif" border=0>';
				break;
			case 4:
				$bashnia = '<img src="' . $conf['prefix'] . '/img/gnome-gnobots2.gif" border=0>';
				break;
			case 5:
				$bashnia = '<img src="' . $conf['prefix'] . '/img/gnome-eyes.gif" border=0>';
				break;
			default:
				$bashnia = '<img src="' . $conf['prefix'] . '/img/nobody.gif" border=0>';
		}
		echo '<h4><a name="' . $grp_status . '" class="HiLite">'
			. $groups['grname'] . '</a>.  ' . $groups['grdesc'] . '</h4>' . "\n";
		if ($num_rows > 0) {
			print '<table cellspacing=0 border=1 cellpadding=3 style="text-align:center;">' . "\n";
			print '<tr bgcolor="' . $header_color . '">' . "\n";
			print '<th>&nbsp;</th>' . "\n";
			print '<th>&nbsp;</th>' . "\n";
			print '<th>&nbsp;</th>' . "\n";
			print '<th nowrap>' . $strLoginName . '</th>' . "\n";
//-->			
			print '<th nowrap>' . $strGuestMode . '</th>' . "\n";
			print '<th nowrap>' . $strPDAversion . '</th>' . "\n";
//-->			
			print '<th>' . $strHost . '</th>' . "\n";
			print '<th>' . $FIO . '</th>' . "\n";
			print '<th>' . $strUpdateControl . '</th>' . "\n";
			print '</tr>' . "\n";
			$r_count = 0;
			foreach ($result as $row) {
				print '<tr>' . "\n";
				print '<td>' . $bashnia . '</td>' . "\n";
				$a_del = sprintf(
					'%s?cmd=DEL&u_name=%s&u_host=%s',
					$_SERVER['PHP_SELF'],
					urlencode($row['USER']),
					urlencode($row['HOST'])
				);
				$a_change = sprintf(
					'./user-tune.php?u_name=%s&u_host=%s',
					urlencode($row['USER']),
					urlencode($row['HOST'])
				);
				if ($row['USER'] == $login_user) {
					print '<td>&nbsp;</td>';
					print '<td><a href="' . $a_change . '">' . $strChange . '</a></td>' . "\n";
				} else {
					if ($admin_user && $user_status < $grp_status) {
						print '<td><a href="' . $a_del . '">' . $strDelete . '</a></td>' . "\n";
						print '<td><a href="' . $a_change . '">' . $strChange . '</a></td>' . "\n";
					} else {
						print ('<td>&nbsp;</td>');
						print ('<td>&nbsp;</td>');
					}
				}
				print '<td valign="center" nowrap><b>' . $row['USER'] . '</b></td>' . "\n";
//-->
				print '<td valign="center" nowrap><b>' . ($row['GUEST'] ? '+' : '-') . '</b></td>' . "\n";
				print '<td valign="center" nowrap><b>' . ($row['PDA'] ? '+' : '-') . '</b></td>' . "\n";
//-->				
				print '<td valign="center" nowrap><b>' . $row['HOST'] . '</b></td>' . "\n";
				print '<td>' . htmlspecialchars($row['LONGNAME'], ENT_QUOTES, $chset) . '</td>' . "\n";
				if (empty($row['CHANGE_TIME'])) {
					print "<td align=\"center\">-</td>\n";
				} else {
					print '<td>' . $row['CHANGE_USER'] . '@' . $row['CHANGE_HOST'] . '<br>' . $row['CHANGE_TIME'] . '</td>' . "\n";
				}
				print "</tr>\n";
			}
			print '</table><br>' . "\n";
		} else {
			print '<p><b>' . $strNotUserDef . '</b></p>' . "\n";
		}
		if ($admin_user && $user_status < $grp_status) {
			print '<div><a href="' . $conf['prefix'] . '/admin/user-addnew.php?status=' . $grp_status .
				'">+ ' . $l_user_addnew . ' +</a></div>' . "\n";
		}
		$result = null;
		print '<div align="center"><a href="#top">' . $strUp . '</a></div>' . "\n";
		print '<hr align="center" noshade>' . "\n";
	} // while (list ( $grp_status, $groups ) = $grps )
}

//phpinfo ();
require('../foot.inc.php');
?>
