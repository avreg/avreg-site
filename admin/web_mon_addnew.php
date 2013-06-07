<?php
/**
 * @file admin/web_mon_addnew.php
 * @brief Создание новой раскладки для WEB
 */
if (isset($_POST['pipes_show'])) {
	$pipes_show = $_POST['pipes_show'];
}
if (isset ($pipes_show)) {
	settype($pipes_show, 'int');
	setcookie('avreg_pipes_show', $pipes_show, time() + 5184000);
} else {
	if (isset($_COOKIE['avreg_pipes_show'])) {
		$pipes_show = (Integer)$_COOKIE['avreg_pipes_show'];
	} else {
		$pipes_show = 1;
	}
}

$USE_JQUERY = true;

//определяем режим сохранения раскладки client(на клиенте)/server(на сервере)
$storage = 'server';
if (isset($_GET['storage'])) {
	$storage = $_GET['storage'];
} elseif (isset($_POST['storage'])) {
	$storage = $_POST['storage'];
}

if ($storage == 'client') {
	$link_javascripts = array(
		'lib/js/user_layouts.js',
		'lib/js/json2.js'
	);
}

require('../head.inc.php');


//DENY($admin_status);
if ($storage != 'client') {
	DENY($admin_status);
}

require('./mon-type.inc.php');

?>

<script type="text/javascript" language="javascript">
	<!--
	var user_login = "<?php echo $login_user; ?>";

	function reset_to_list() {
		window.open('<?php echo $conf['prefix']; ?>/admin/web_mon_list.php', target = '_self');
	}
	// -->
</script>

<?php

echo '<h1>' . sprintf($web_r_mons, $named, $sip) . '</h1>' . "\n";

if (!isset($mon_nr) || $mon_nr == '') {
	die('empty $mon_nr');
}

if (!settype($mon_nr, 'int')) {
	die('$mon_nr is\'t integer value');
}

if ($mon_nr < 0) {
	die('Error: $mon_nr < 0');
}

if (isset($cmd)) {
	if (empty($mon_type)) {
		print '<p class="HiLiteBigErr">' . $strMonAddErr1 . '</p>' . "\n";
		print_go_back();
		require('../foot.inc.php');
		exit;
	}
	switch ($cmd) {

		case '_ADD_NEW_MON_':
			require('web_active_pipe.inc.php');

			$wins_array = & $active_pipes;

			if (count($wins_array) > 0) {
				print '<p class="HiLiteBigWarn">' . sprintf(
						$fmtWebMonAddInfo,
						$mon_type,
						$mon_nr,
						$mon_name
					) . '</p>' . "\n";
				print '<p class="HiLiteBigWarn">' . $strMonAddInfo2 . '</p>' . "\n";

				//формирование массива альтернативных источников видео
				$cams_srcs = array();
				foreach ($GCP_cams_params as $key => $val) {
					$cams_srcs[$key] = array();
					$cams_srcs[$key]['avregd'] = 'true';
					$cams_srcs[$key]['alt_1'] = ($val['cell_url_alt_1'] != null || $val['fs_url_alt_1'] != null) ? 'true' : 'false';
					$cams_srcs[$key]['alt_2'] = ($val['cell_url_alt_2'] != null || $val['fs_url_alt_2'] != null) ? 'true' : 'false';
				}
				print '<script type="text/javascript">' . "\n";
				print 'var cams_alt =' . json_encode($cams_srcs) . ";\n";
				print '</script>' . "\n";

				//Создание эл-та селект ля ячеек раскладки
				$a = getSelectHtmlByName(
					'mon_wins[]',
					$wins_array,
					false,
					1,
					1,
					'',
					true,
					'sel_change(this); show_sub_select(this);',
					'',
					null,
					$cams_srcs
				);

				if ($storage == 'client') { //Если раскладка создается на клиенте
					print '<form action="../online/' . '" onSubmit="return user_layouts.add_new();"
					 method="POST">' . "\n";
				} else { //Если раскладка создается на сервере
					print '<form action="' . $_SERVER['PHP_SELF'] . '"  onSubmit="return validate();" method="POST">' . "\n";
				}

				layout2table($mon_type, ($mon_type == 'QUAD_25_25') ? 500 : 400, null, $a);

				print '<input type="hidden" name="cmd" value="_ADD_NEW_MON_OK_">' . "\n";
				print '<input type="hidden" name="mon_nr" value="' . $mon_nr . 'UL' . ' ">' . "\n";
				print '<input type="hidden" name="mon_name" value="' . $mon_name . '">' . "\n";
				print '<input type="hidden" name="mon_type" value="' . $mon_type . '">' . "\n";


				require_once('../lang/russian/utf-8/_online.php');
				//Селектор сохранять пропорции/ на весь экран
				$AspectRatio = 'calc';
				print '<br /><div><div style="float:left;" >' . $strAspectRatio . ":&nbsp;&nbsp;</div> \n";
				print '<div >' . getSelectByAssocAr(
						'AspectRatio',
						$AspectRatioArray,
						false,
						1,
						1,
						$AspectRatio,
						false
					) . "</div></div>\n";
				//Выводить имена камер
				$PrintCamNames = "checked";
				print '<br /><div><div style="float:left;" >' . $strPrintCamNames . ":&nbsp;&nbsp;</div>\n";
				print '<div><input type="checkbox" name="PrintCamNames" ' . $PrintCamNames . ' />' . "</div></div>\n";

				//Установить интервал попыток переподключения к камере при отсутствии соединения
				$ReconnectTimeout = 5; //по умолчанию
				print '<br /><div><div style="float:left;" >' . $strReconnectTimeout . ":&nbsp;&nbsp;</div> \n";
				print '<div >' . getSelectByAssocAr(
						'ReconnectTimeout',
						$ReconnectTimeoutArray,
						false,
						1,
						1,
						$ReconnectTimeout,
						false
					) . "</div></div>\n";

				//Кнопки сохранить раскладку и отменить
				print '<br><input type="submit" name="btn" value="' . $strSave . '">' . "\n";
				if ($storage == 'client') {
					print '<input type="reset" name="btn" value="' . $strRevoke . '" onclick="user_layouts.redirect(\'' . $conf['prefix'] . '/admin/web_mon_list.php\', true);">' . "\n";
				} else {
					print '<input type="reset" name="btn" value="' . $strRevoke . '" onclick="reset_to_list();">' . "\n";
				}
				print '</form>' . "\n";
			}
			require('../foot.inc.php');
			exit;
			break; /**/
		case '_ADD_NEW_MON_OK_':
			$i = 0;
			$j = 0;

			$mwt = $_POST['mon_wins_type'];
			$allWINS = array();

			while ($i < count($mon_wins)) {
				if (!empty($mon_wins[$i])) {
					//формирование единого объекта для всех ячеек раскладки					
					$allWINS[$i] = array();
					array_push($allWINS[$i], $mon_wins[$i], $mwt[$j]);
					$j++;
				}
				$i++;
			}

			$allWINS = json_encode($allWINS);

			if ($allWINS != '') {
				$PrintCamNames = ($PrintCamNames != null) ? 1 : 0;

				$adb->web_add_layouts(
					$mon_nr,
					$mon_type,
					$mon_name,
					$remote_addr,
					$login_user,
					$PrintCamNames,
					$AspectRatio,
					$ReconnectTimeout,
					$allWINS
				);

				print "Ok!\n'";
				print '<script type="text/javascript" language="javascript">reset_to_list();</script>' . "\n";
			} else {
				print '<p class="HiLiteBigErr">' . $strNotChoiceCam . '</p>' . "\n";
				print_go_back();
				require('../foot.inc.php');
				exit;
			}
			break;
	} // switch
} else {

	echo '<h2>' . sprintf($web_mon_addnew, $counter) . '</h2>' . "\n";

	print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
	print $strNamed . ': <input type="text" name="mon_name" size=16 maxlength=16 value="">' . "\n";
	$wins = range(1, MAX_CAMS_INTO_LAYOUT);
	$lm = count($layouts_defs);
	$mc = 5; // 5 столбцов
	$mr = $lm / $mc;
	if ($lm % $mc) {
		$mr++;
	}
	reset($layouts_defs);
	print '<table cellspacing="0" border="0" cellpadding="5">' . "\n";
	for ($r = 0; $r < $mr; $r++) {
		print '<tr>' . "\n";
		for ($c = 0; $c < $mc; $c++) {
			list($lname, $ldef) = each($layouts_defs);
			print  '<td  align="center" valign="top">' . "\n";
			if (empty($lname)) {
				print('&nbsp;');
			} else {
				printf('<input type="radio" name="mon_type" value="%s">&nbsp;%s<br />', $lname, $ldef[5]);
				layout2table($lname, 140, $wins);
			}
			print  '<br /></td>' . "\n";
		}
		print '</tr>' . "\n";
	}
	print '</table>' . "\n";

	print '<input type="hidden" name="cmd" value="_ADD_NEW_MON_">' . "\n";
	print '<input type="hidden" name="storage" value="' . $storage . '">' . "\n";
	if ($storage == 'client') {
		print '<input type="hidden" name="mon_nr" value="' . $mon_nr . 'UL' . '">' . "\n";
	} else {
		print '<input type="hidden" name="mon_nr" value="' . $mon_nr . '">' . "\n";
	}

	//Кнопки формы
	print '<input type="submit" name="btn" value="' . $l_mon_addnew . '">' . "\n";
	if ($storage == 'client') {
		print '<input type="reset" name="btn" value="' . $strRevoke . '" onclick="user_layouts.redirect(\'' . $conf['prefix'] . '/admin/web_mon_list.php\', true);">' . "\n";
	} else {
		print '<input type="reset" name="btn" value="' . $strRevoke . '" onclick="reset_to_list();">' . "\n";
	}
	print '</form>' . "\n";
}

require('../foot.inc.php');
?>
