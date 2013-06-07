<?php
/**
 * @file admin/mon-tune.php
 * @brief Редактирование раскладки
 */
if (isset($_POST['pipes_show'])) {
	$pipes_show = $_POST['pipes_show'];
}
if (isset($pipes_show)) {
	settype($pipes_show, 'int');
	setcookie('avreg_pipes_show', $pipes_show, time() + 5184000);
} else {
	if (isset($_COOKIE['avreg_pipes_show'])) {
		$pipes_show = (Integer)$_COOKIE['avreg_pipes_show'];
	} else {
		$pipes_show = 1;
	}
}
require('../head.inc.php');

DENY($admin_status);

require('./mon-type.inc.php');

?>

<script type="text/javascript" language="javascript">
	<!--
	function reset_to_list() {
		window.open('<?php echo $conf['prefix']; ?>/admin/mon-list.php', target = '_self');
	}
	// -->
</script>

<?php

echo '<h1>' . sprintf($r_mons, $named, $sip) . '</h1>' . "\n";

if (!isset($mon_nr) || $mon_nr == '' || empty($display)) {
	die('empty $mon_nr and/or $display');
}

if (!settype($mon_nr, 'int')) {
	die('$mon_nr is\'t integer value');
}

if ($mon_nr < 0 || $mon_nr > 9) {
	die('$mon_nr in\'t in [0..9]');
}

if (!($display == 'L' || $display == 'R')) {
	die('$display must be L or R char');
}

echo '<h2>' . sprintf(
		$r_mon_tune,
		$mon_nr,
		$mon_name,
		$display == 'R' ? $sRightDisplay1 : $sLeftDisplay1
	) . '</h2>' . "\n";

if (isset($cmd)) {
	switch ($cmd) {
		case '_ADD_NEW_MON_OK_':
			$i = 0;
			$fWINS = array();
			$vWINS = array();
			while ($i < count($mon_wins)) {
				if (!empty($mon_wins[$i])) {
					array_push($fWINS, 'WIN' . ($i + 1));
					array_push($vWINS, $mon_wins[$i]);
				} else {
					array_push($fWINS, 'WIN' . ($i + 1));
					array_push($vWINS, null);
				}
				$i++;
			}
			if (count($fWINS) > 0) {

				$adb->replace_layouts(
					$display,
					$mon_nr,
					$mon_type,
					$mon_name,
					$remote_addr,
					$login_user,
					$fWINS,
					$vWINS
				);

				print '<p class="HiLiteBigWarn">' . sprintf(
						$r_mon_changed,
						$mon_nr,
						empty($mon_name) ? $mon_type : $mon_name,
						$display == 'R' ? $sRightDisplay1 : $sLeftDisplay1
					) . '</p>' . "\n";
				print '<center><a href="' . $conf['prefix'] . '/admin/mon-list.php" target="_self">' . $r_mon_goto_list . '</a></center>' . "\n";
			} else {
				print '<p class="HiLiteBigErr">' . $strNotChoiceCam . '</p>' . "\n";
				print_go_back();
				require('../foot.inc.php');
				exit;
			}
			break;
	} // switch
} else {
	// cmd not set
	require('active_pipe.inc.php');
	$wins_array = & $active_pipes;
	if (count($wins_array) == 0) {
		print '<p class="HiLiteBigErr">' . $strNotViewCams . '</p>' . "\n";
		print_go_back();
		require('../foot.inc.php');
		exit;
	} else {
		$aaa = array();

		$row = $adb->get_monitor($display, $mon_nr);
		for ($i = 4; $i < intval(MAX_CAMS_INTO_LAYOUT + 6); $i++) {
			$a = getSelectHtmlByName('mon_wins[]', $wins_array, false, 1, 1, $row[$i], true, 'sel_change(this);');
			array_push($aaa, $a);
		}
		/* Free last resultset */
		$result = null;

		// print "<pre><code>".var_dump($aaa)."</code></pre>";
		print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST"  onSubmit="return validate();">' . "\n";
		print '<p class="HiLiteBigWarn">' . $strMonAddInfo2 . '</p>' . "\n";
		print '&nbsp;&nbsp;&nbsp;' . $strName . ': <input type="text" name="mon_name" size=16 maxlength=16 value="' . $mon_name . '">' . "\n";
		layout2table($mon_type, ($mon_type == 'QUAD_25_25') ? 400 : 300, $aaa);
		print '<input type="hidden" name="cmd" value="_ADD_NEW_MON_OK_">' . "\n";
		print '<input type="hidden" name="display" value="' . $display . '">' . "\n";
		print '<input type="hidden" name="mon_nr" value="' . $mon_nr . '">' . "\n";
		print '<input type="hidden" name="mon_type" value="' . $mon_type . '">' . "\n";
		print '<br><input type="submit" name="btn" value="' . $strSave . '">' . "\n";
		print '<input type="reset" name="btn" value="' . $strRevoke . '" onclick="reset_to_list();">' . "\n";
		print '</form>' . "\n";
	}
}
// phpinfo ();
require('../foot.inc.php');
?>
