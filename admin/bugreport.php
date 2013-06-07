<?php
/**
 * @file admin/bugreport.php
 * @brief Страница правил и рекомендуемых последовательностей действий для сообщения об ошибке
 */
require('../head.inc.php');
?>

<script type="text/javascript" language="JavaScript">
	<!--

	var do_wait = false;

	function on_submit() {
		if (do_wait)
			return false;
		var btsubmit = ie ? document.all['btSubmit'] : document.getElementById('btSubmit');
		btsubmit.value = '<?php echo $sWaitLogsDownload; ?>';
		btsubmit.style.backgroundColor = '#DCDCDC';
		do_wait = true;
		return true;
	}

	// -->
</script>

<?php
echo '<h1>' . sprintf($r_bugs, $named, $sip) . '</h1>' . "\n";

print '<div class="Warn">' . $bugs_rules . '</div>' . "\n";
echo '<br />' . "\n";
print '<form action="' . $conf['prefix'] . '/admin/getlogs.php" method="GET" enctype="application/x-www-form-urlencoded" onsubmit="return(on_submit())">' . "\n";
print "<span>$sMaxTailNum</span>\n";
?>
<select name="howmatch" size="1">
	<option value="1000">1000</option>
	<option value="3000" selected>3000</option>
	<option value="5000">5000</option>
	<option value="10000">10000</option>
</select>
<?php
print '<br /><br /><input type="submit" id="btSubmit" name="getlog" value="' . $strGetLogs . '">' . "\n";
print '</form>' . "\n";


require('../foot.inc.php');
?>
