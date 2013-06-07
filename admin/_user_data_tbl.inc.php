<?php
print '<table cellspacing=0 border=1 cellpadding=5>' . "\n";
print '<tr>' . "\n";
print '<td colspan="2">' . $strName1 . '</td>' . "\n";
print '<td><input type="text" name="u_name" value="' . $user2html . '" size="16" maxlength="16">' . "\n";
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td colspan="2">' . $strAllowHost . '</td>' . "\n";
print '<td><input type="text" name="u_host" value="' . $host2html . '" size="40" maxlength="60">' . "\n";
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td colspan="2">' . $FIO . '</td>' . "\n";
print '<td><input type="text" name="u_longname" value="' . $longname2html . '" size="40" maxlength="50">' . "\n";
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td colspan="2">' . $strPassword . '<br>' . $strPasswordAllowed . '</td>' . "\n";
print '<td><input type="password" name="u_pass" maxlength="8" value="' . $passwd2html . '">' . "\n";
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td colspan="2">' . $strPassword2 . '</td>' . "\n";
print '<td><input type="password" name="u_pass2" maxlength="8" value="' . $passwd2html . '">' . "\n";
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td colspan="2">' . $str_group1 . '</td>' . "\n";
print '<td>' . "\n";
reset($grp_ar);
while (list ($gr_status, $groups) = each($grp_ar)) {
	if ($user_status > $gr_status) {
		$addons = 'disabled';
	} else {
		if ($u_status === $gr_status) {
			$addons = 'checked';
		} else {
			$addons = '';
		}
	}
	print '<input type="radio" name="groups" ' . $addons .
		' value="' . $gr_status . '">' . $grp_ar[$gr_status]['grname'] . '<br>' . "\n";
}
print '</td>' . "\n";


//Гостевой режим
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td colspan="2">' . $str_GuestMode . '</td>' . "\n";
print '<td><input type="checkbox" name="guest" ' . ((isset($guest) && $guest) ? 'checked' : '') . '>' . $strGuestMode . "\n";
print '</tr>' . "\n";

//Доступ к PDA версии
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td colspan="2">' . $str_PDAversion . '</td>' . "\n";
print '<td><input type="checkbox" name="pda" ' . ((isset($pda) && $pda) ? 'checked' : '') . '>' . $strPDAversion . "\n";
print '</tr>' . "\n";

//доступные камеры
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td colspan="2">' . $strDeviceACL . '</td>' . "\n";
print '<td><input type="text" name="u_devacl" size="40" maxlength="100" value="' . $u_devacl . '">' . "\n";
print '</tr>' . "\n";

//Доступные раскладки
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td colspan="2">' . $strAllowedWEBLayouts . '</td>' . "\n";
print '<td><input type="text" name="u_layouts" size="40" maxlength="100" value="' . (isset($u_layouts) ? $u_layouts : '') . '">' . "\n";
print '</tr>' . "\n";

print '<tr>' . "\n";
print '<td colspan="2">' . $strForcedSavingLimit . '</td>' . "\n";
print '<td><input type="text" name="u_forced_saving_limit" size="4" maxlength="4" value="' . $u_forced_saving_limit . '">' . "\n";
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td colspan="2">' . $SessionsPerUserInfo . '</td>' . "\n";
print '<td><input type="text" name="sessions_per_user" size="2" maxlength="2"  value="' . $sessions_per_user . '">' . "\n";
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td rowspan="2">' . $strRateLimitsInfo . '</td>' . "\n";
print '<td>' . $strLimitFps . '</td>' . "\n";
print '<td><input type="text" name="limit_fps" value="' . $limit_fps . '" size="5" maxlength="5">' . "\n";
print '<tr>' . "\n";
print '<td>' . $sNonMotionFps . '</td>' . "\n";
print '<td><input type="text" name="nonmotion_fps" value="' . $nonmotion_fps . '" size="5" maxlength="5">' . "\n";
print '</tr>' . "\n";
/*
print '<tr>'."\n";
print '<td>'.$strLimitKbps.'</td>'."\n";
print '<td><input disabled readonly type="text" name="limit_kbps" value="'.$limit_kbps.'" size="6" maxlength="6">'."\n";
print '</tr>'."\n";
*/
print '<tr>' . "\n";
print '<td rowspan="2">' . $sSessionLimitsInfo . '</td>' . "\n";
print '<td>' . $sSessionTime . '</td>' . "\n";
print '<td><input type="text" name="session_time" value="' . $session_time . '" size="6" maxlength="6">' . "\n";
print '</tr>' . "\n";
print '<tr>' . "\n";
print '<td>' . $sSessionVolume . '</td>' . "\n";
print '<td><input type="text" name="session_volume" value="' . $session_volume . '" size="6" maxlength="6">' . "\n";
print '</tr>' . "\n";
print '</table>' . "\n";
?>
