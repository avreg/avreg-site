<?php
/**
 * @file admin/cam-tune.php
 * @brief Изменение параметров камер
 */
if (isset($_POST)) {
	$expire = time() + 5184000;
	if (isset($_POST['par_filter'])) {
		setcookie('avreg_par_filter', $_POST['par_filter'], $expire, $_SERVER['SCRIPT_NAME']);
	}
}
/// Языковый файл
$lang_file = '_admin_cams.php';
$USE_JQUERY = true;
$link_javascripts = array('lib/js/checkbox.js');
require('../head.inc.php');
DENY($admin_status);
require_once($params_module_name);
require('./params.inc.php');

if (!isset($cam_nr) || !settype($cam_nr, 'int')) {
	die ('Empty cameras number');
}
/* if ( !isset($sip) || empty($sip) ) die ('Invalid server IP: `'.$sip.'`'); */
if (isset ($par_filter)) {
	settype($par_filter, 'int');
} else {
	if (isset($_COOKIE['avreg_par_filter'])) {
		$par_filter = (int)$_COOKIE['avreg_par_filter'];
	} else {
		$par_filter = 0;
	}
}
if (isset($cmd)) {
	if ($cmd == 'UPDATE_PARAM') {
		require('./upload.inc.php');
		if (is_array($types) && (count($types) > 0)) {
			$cmd = 'SHOW_PARAM';
			while (list($parname, $partype) = each($types)) {
				if (!isset($olds[$parname])) {
					die ('Error in post data!');
				}
				$_value = isset($fields[$parname]) ? $fields[$parname] : '';

				if (is_array($_value)) {
					$value = implode(',', array_map('rawurldecode', $_value));
				} else {
					$value = trim(rawurldecode($_value));
				}
				// print "<p>'$parname'='$value' old='$olds[$parname]' types='$types[$parname]'</p>\n";
				if (($olds[$parname] != $value) && CheckParVal($parname, $value)) {
					CorrectParVal($parname, $value);
					$_val = ($value == '') ? null : html_entity_decode($value);
					$adb->replace_camera('local', $cam_nr, $parname, $_val, $remote_addr, $login_user);

					print_syslog(
						LOG_NOTICE,
						sprintf(
							'cam[%s]: update param "%s", set new value "%s", old value "%s"',
							$cam_nr === 0 ? 'default' : (string)$cam_nr,
							$parname,
							empty($_val) ? "<empty>" : $_val,
							empty($olds[$parname]) ? "<empty>" : $olds[$parname]
						)
					);
				}
			}
		}
	}
}

$__cam_arr = getCamsArray($sip, true);
if (empty($__cam_arr)) {
	echo '<h3>' . sprintf($r_cam_tune, $cam_nr, $cam_name, $named) . '</h3>' . "\n";
} else {
	print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST" enctype="multipart/form-data">' . "\n";
	if ($cam_nr === 0) {
		echo '<h3>' . sprintf(
				$r_cam_defaults,
				getSelectByAssocAr('cam_nr', $__cam_arr, false, 1, 0, 0, false, true, ''),
				$named,
				$sip
			) . '</h3>' . "\n";
	} else {
		echo '<h3>' . sprintf(
				$r_cam_tune,
				$cam_nr,
				getSelectByAssocAr('cam_nr', $__cam_arr, false, 1, 0, $cam_nr, false, true, ''),
				$named,
				$sip
			) . '</h3>' . "\n";
		print '<input type="hidden" name="cam_name" value="' . $__cam_arr[$cam_nr] . '">' . "\n";
	}
	if (isset($categories)) {
		print '<input type="hidden" name="categories" value="' . $categories . '">' . "\n";
	}
	print '</form>' . "\n";
}

$WE_IN_DEFS = ($cam_nr === 0) ? true : false;
require('./param-grp.inc.php');


// выводим таблицу параметров
if (isset($categories)) {
	$result = $adb->get_def_cam_params($cam_nr);

	$cam_params = array();
	$def_params = array();
	foreach ($result as $row) {
		if ($cam_nr === 0) {
			$cam_params[$row['PARAM']] = $row['VALUE'] . '~' . $row['CHANGE_HOST'] . '~' . $row['CHANGE_USER'] . '~' . $row['CHANGE_TIME'];
		} else {
			if ($row['CAM_NR'] > 0) {
				$cam_params[$row['PARAM']] = $row['VALUE'] . '~' . $row['CHANGE_HOST'] . '~' . $row['CHANGE_USER'] . '~' . $row['CHANGE_TIME'];
			} else {
				$def_params[$row['PARAM']] = $row['VALUE'] . '~' . $row['CHANGE_HOST'] . '~' . $row['CHANGE_USER'] . '~' . $row['CHANGE_TIME'];
			}
		}
	}
	$result = null;

	print '<br>' . "\n";
	print '<table width="100%" cellspacing="0" border="1" cellpadding="5" bgcolor="#dcdcdc">' . "\n";
	print '<tr>' . "\n";
	print '<td>' . "\n";
	print '<font size="-1" color="' . $NotSetParColor . '">* - ' . $not_defined . '</font>;' . "\n";
	print '<br><font size="-1" color="' . $ParDefColor . '">** - ' . $eval_with_def . '</font>;' . "\n";
	if ($cam_nr > 0) {
		print '<br><font size="-1" color="' . $ParSetColor . '">*** - ' . $not_eval_with_def . '</font>.' . "\n";
	}
	print '</td>' . "\n";
	print '<td>' . "\n";
	print '<img src="' . $conf['prefix'] . '/img/hotsync.gif" alt="Reloaded" border="0"> - ' . $strReloadDesc . "\n";
	print '<br><img src="' . $conf['prefix'] . '/img/hotsync_busy.gif" alt="Restarted" border="0"> - ' . $strRestartDesc . "\n";
	print '</td>' . "\n";
	print '</tr>' . "\n";
	print '</table>' . "\n";
	print '<br /><form action="' . $_SERVER['PHP_SELF'] . '" method="POST" enctype="multipart/form-data">' . "\n";
	print $strDisplayed . '&nbsp;' . "\n";
	print getSelectByAssocAr('par_filter', $par_filter_ar, false, 1, null, (string)$par_filter, false, true);
	print $strParams . ".\n";
	print '&nbsp;&nbsp;<input type="submit" name="submit_btn" value="' . $strSave . '">' . "\n";
	print '<input type="reset" name="reset_btn" value="' . $strRevoke . '">' . "\n";
	print '<br /><table cellspacing="0" border="1" cellpadding="2" class="paramstbl">' . "\n";
	print '<tr bgcolor="' . $header_color . '">' . "\n";
	print '<th nowrap>' . $strName . '</th>' . "\n";
	print '<th>' . $strDescription . '</th>' . "\n";
	print '<th>' . $strUpdateControl . '</th>' . "\n";
	print '</tr>' . "\n";

	$p_count = count($PARAMS);

	for ($i = 0; $i < $p_count; $i++) {
		$parname1 = & $PARAMS[$i]['name'];
		$VAL_TYPE = & $PARAMS[$i]['type'];
		$VALID_PREG = & $PARAMS[$i]['valid_preg'];
		$DEF_VAL = & $PARAMS[$i]['def_val'];
		$COMMENT = & $PARAMS[$i]['desc'];
		$FLAGS = & $PARAMS[$i]['flags'];
		$PAR_CATEGORY = & $PARAMS[$i]['cats'];
		$SUBCAT_SELECTOR = & $PARAMS[$i]['subcats'];
		$MASTER_STATUS = & $PARAMS[$i]['mstatus'];
		if ($PAR_CATEGORY != $categories) {
			continue;
		}
		if ($user_status > $MASTER_STATUS) {
			continue;
		}

		if ($cam_nr === 0) {
			if (!($FLAGS & $F_IN_DEF)) {
				continue;
			}
		} else {
			if (!($FLAGS & $F_IN_CAM)) {
				continue;
			}
		}


		if ($par_filter === 0 && !($FLAGS & $F_BASEPAR)) {
			continue;
		}

		if ($cam_nr > 0 && array_key_exists($parname1, $def_params)) {
			list($DEF_VALUE, $DEF_CHANGE_HOST, $DEF_CHANGE_USER, $DEF_CHANGE_TIME) =
				explode('~', $def_params[$parname1]);
		} else {
			$DEF_VALUE = null;
			$DEF_CHANGE_HOST = null;
			$DEF_CHANGE_USER = null;
			$DEF_CHANGE_TIME = null;
		}
		if (array_key_exists($parname1, $cam_params)) {
			list($VALUE, $CHANGE_HOST, $CHANGE_USER, $CHANGE_TIME) =
				explode('~', $cam_params[$parname1]);
			//print $cam_params[$parname1]."\n";
		} else {
			$VALUE = null;
			$CHANGE_HOST = null;
			$CHANGE_USER = null;
			$CHANGE_TIME = null;
		}
		print '<tr><td valign="middle" nowrap><div>' . "\n";


		if ($FLAGS & $F_RELOADED) {
			print '<img src="' . $conf['prefix'] . '/img/hotsync.gif" alt="Reloaded" border="0">&nbsp;';
		} else {
			print '<img src="' . $conf['prefix'] . '/img/hotsync_busy.gif" alt="Restarted" border="0">&nbsp;';
		}

		print '<span>' . "\n";
		$def_val = ($DEF_VALUE === '' || is_null($DEF_VALUE)) ? null : $DEF_VALUE;
		$val = null;
		if ($VALUE === '' || is_null($VALUE)) {
			// не установленное поле
			if ($VALUE != $def_val) {
				print '<font color="' . $ParDefColor . '">' . $parname1 . '<sup>**</sup></font>';
				$val = $def_val;
			} else {
				print '<font color="' . $NotSetParColor . '">' . $parname1 . '<sup>*</sup></font>';
				$val = null;
			}
		} else {
			if ($cam_nr === 0) {
				print '<font color="' . $ParDefColor . '">' . $parname1 . '<sup>**</sup></font>';
				$val = $VALUE;
			} else {
				if ($VALUE != $DEF_VALUE) {
					print '<font color="' . $ParSetColor . '">' . $parname1 . '<sup>***</sup></font>';
					$val = $VALUE;
				} else {
					print '<font color="' . $ParDefColor . '">' . $parname1 . '<sup>**</sup></font>';
					$val = $def_val;
				}
			}
		}
		print '</span><br /><br /><div>' . "\n";
		$max_len = (isset($PARAMS[$i]['max_len'])) ? $PARAMS[$i]['max_len'] : 0;
		$str_f_len = ($max_len > 40) ? 40 : $max_len;

		switch ($VAL_TYPE) {
			case $INT_VAL:
				$a = ($val === '' || is_null($val)) ? '' : (integer)$val;
				$b = $max_len ? $max_len : 6;
				print '<input type="text" name="fields[' . $parname1 . ']" value="' . $a . '" size=6 maxlength=' . $b . '>';
				break;
			case $INTPROC_VAL:
				$a = ($val === '' || is_null($val)) ? '' : $val;
				$b = $max_len ? $max_len : 6;
				print '<input type="text" name="fields[' . $parname1 . ']" value="' . $a . '" size=6 maxlength=' . $b . '>';
				break;
			case $STRING_VAL:
				$a = getBinString($val);
				$b = $max_len ? $max_len : 60;
				if (!empty($a) && !empty($VALID_PREG) && !preg_match($VALID_PREG, $a)) {
					printf('<p style="color: ' . $GLOBALS['error_color'] . ';">' . $fmtEINVAL . '</p>', $a);
				}
				print '<input type="text" name="fields[' . $parname1 . ']" value="' . $a . '" size=' . $str_f_len . ' maxlength=' . $b . '>';
				break;
			case $STRING200_VAL:
				$a = getBinString($val);
				$b = $max_len ? $max_len : 200;
				print '<input type="text" name="fields[' . $parname1 . ']" value="' . $a . '" size=' . $str_f_len . ' maxlength=' . $b . '>';
				break;

			case $PASSWORD_VAL:
				$a = getBinString($val);
				$b = $max_len ? $max_len : 60;
				print '<input type="password" name="fields[' . $parname1 . ']" value="' . $a . '" size=' . $str_f_len . ' maxlength=' . $b . '>';
				break;
			case $CHECK_VAL:
				print checkParam($parname1, $val, $DEF_VAL);
				break;

			default: /* BOOL*/
				if ($val === '' || is_null($val)) {
					print getSelectHtml('fields[' . $parname1 . ']', $flags, false, 1, 0, null, true, false);
				} else {
					print getSelectHtml(
						'fields[' . $parname1 . ']',
						$flags,
						false,
						1,
						0,
						$flags[(integer)$val],
						true,
						false
					);
				}
		}
		print '</div></div></td>' . "\n";
		print '<td>' . $COMMENT . '</td>' . "\n";
		if (empty($CHANGE_TIME)) {
			print "<td align=\"center\">-</td>\n";
		} else {
			print '<td align="center" nowrap>' . $CHANGE_USER . '@' . $CHANGE_HOST . '<br>' . (empty($CHANGE_TIME) ? '-' : $CHANGE_TIME) . "\n";
		}
		print '<input type="hidden" name="types[' . $parname1 . ']" value="' . $VAL_TYPE . '">' . "\n";
		print '<input type="hidden" name="olds[' . $parname1 . ']" value="' . $val . '">' . "\n";
		print '</td>' . "\n";
		print '</tr>' . "\n";
	}
	print "</table>\n";
	print '<input type="hidden" name="cmd" value="UPDATE_PARAM">' . "\n";
	print '<input type="hidden" name="cam_nr" value="' . $cam_nr . '">' . "\n";
	if (isset($cam_name)) {
		print '<input type="hidden" name="cam_name" value="' . $cam_name . '">' . "\n";
	}
	print '<input type="hidden" name="categories" value="' . $categories . '">' . "\n";
	print '<input type="submit" name="submit_btn" value="' . $strSave . '">' . "\n";
	print '<input type="reset" name="reset_btn" value="' . $strRevoke . '">' . "\n";
	print '</form>' . "\n";
	print "<br>\n";
}

// phpinfo ();
require('../foot.inc.php');
?>
