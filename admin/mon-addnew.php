<?php
/**
 * @file admin/mon-addnew.php
 * @brief Создание новой раскладки
 */
if (isset($_POST['pipes_show']))
   $pipes_show = $_POST['pipes_show'];
if ( isset ($pipes_show) ) {
   settype($pipes_show, 'int');
   setcookie('avreg_pipes_show',  $pipes_show, time()+5184000);
} else if ( isset($_COOKIE['avreg_pipes_show']) ) {
   $pipes_show = (Integer)$_COOKIE['avreg_pipes_show'];
} else {
   $pipes_show = 1;
}
require ('../head.inc.php');

DENY($admin_status);

require ('./mon-type.inc.php');



?>

<script type="text/javascript" language="javascript">
<!--
function reset_to_list()
{
		window.open('<?php echo $conf['prefix']; ?>/admin/mon-list.php', target='_self');
}
// -->
</script>

<?php
echo '<h1>' . sprintf($r_mons,$named,$sip) . '</h1>' ."\n";

if ( !isset($mon_nr) || $mon_nr =='' || empty($display) )
	die('empty $mon_nr and/or $display');

if (!settype($mon_nr,'int'))
	die('$mon_nr is\'t integer value');
	
if ($mon_nr < 0 || $mon_nr > 9)
	die('$mon_nr in\'t in [0..9]');

if ( !($display == 'L' || $display == 'R') )
	die('$display must be L or R char');

if ( isset($cmd) ) {
	if ( empty($mon_type) ) {
		print '<p class="HiLiteBigErr">' . $strMonAddErr1 . '</p>' ."\n";
		print_go_back();
		require ('../foot.inc.php');
		exit;		
	}
	switch ( $cmd ) {
		case '_ADD_NEW_MON_':
			require('active_pipe.inc.php');
			$wins_array = &$active_pipes;
			if ( count($wins_array) > 0 ) {
				print '<p class="HiLiteBigWarn">' . sprintf ($fmtMonAddInfo,$mon_type, $mon_nr, $mon_name, $display=='R'?$sRightDisplay1:$sLeftDisplay1) . '</p>' ."\n";
				print '<p class="HiLiteBigWarn">' . $strMonAddInfo2 . '</p>' ."\n";
				$a = getSelectHtmlByName('mon_wins[]',	$wins_array, FALSE , 1, 1, '', TRUE, 'sel_change(this);');
				//print('<pre><code>');print_r($a);print('</code></pre>');
				print '<form action="'.$_SERVER['PHP_SELF'].'"  onSubmit="return validate();" method="POST">'."\n";
				layout2table ( $mon_type, ($mon_type == 'QUAD_25_25')? 500:400, NULL,  $a);
				print '<input type="hidden" name="cmd" value="_ADD_NEW_MON_OK_">'."\n";
				print '<input type="hidden" name="mon_nr" value="'.$mon_nr.'">'."\n";
				print '<input type="hidden" name="display" value="'.$display.'">'."\n";
				print '<input type="hidden" name="mon_name" value="'.$mon_name.'">'."\n";
				print '<input type="hidden" name="mon_type" value="'.$mon_type.'">'."\n";
				print '<br><input type="submit" name="btn" value="'.$strSave.'">'."\n";
				print '<input type="reset" name="btn" value="'.$strRevoke.'" onclick="reset_to_list();">'."\n";
				print '</form>'."\n";
			}
			require ('../foot.inc.php');
			exit;
			break; /**/
		case '_ADD_NEW_MON_OK_':
			$i = 0;
			$fWINS = array();
			$vWINS = array();
			while ( $i < count($mon_wins) ) {
				if ( !empty( $mon_wins[$i] ) ) {
					array_push( $fWINS, 'WIN'.($i+1) );
					array_push( $vWINS, $mon_wins[$i] );
				}
				$i++;
			}
			if ( count( $fWINS ) > 0 ) {
				$adb->add_monitors($display,$mon_nr,$mon_type,$mon_name, $remote_addr, $login_user, $fWINS, $vWINS);
				print "Ok!\n'";
				print '<script type="text/javascript" language="javascript">reset_to_list();</script>'."\n";
			} else {
				print '<p class="HiLiteBigErr">' . $strNotChoiceCam . '</p>' ."\n";
				print_go_back();
				require ('../foot.inc.php');
				exit;
			}
			break;
	} // switch
} else {

	echo '<h2>' . sprintf($r_mon_addnew, $mon_nr, $display=='R'?$sRightDisplay1:$sLeftDisplay1) . '</h2>' ."\n";

	print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
	print $strNamed.': <input type="text" name="mon_name" size=16 maxlength=16 value="">'."\n";
   $wins = range(1, MAX_CAMS_INTO_LAYOUT);
   $lm = count($layouts_defs);
   $mc = 5; // 5 столбцов
   $mr = $lm / $mc;
   if ( $lm % $mc )
      $mr++;
   reset($layouts_defs);
   print '<table cellspacing="0" border="0" cellpadding="5">'."\n";
   for ($r=0; $r<$mr; $r++) {
      print '<tr>'."\n";
      for ($c=0; $c<$mc; $c++) {
         list($lname, $ldef ) = each($layouts_defs);
         print  '<td  align="center" valign="top">'."\n";
         if (empty($lname)) {
            print('&nbsp;');
         } else {
            printf('<input type="radio" name="mon_type" value="%s">&nbsp;%s<br />',$lname, $ldef[5]);
            layout2table ( $lname, 140, $wins );
         }
         print  '<br /></td>'."\n";
      }
      print '</tr>'."\n";
   }
   print '</table>'."\n";
	print '<input type="hidden" name="cmd" value="_ADD_NEW_MON_">'."\n";
	print '<input type="hidden" name="display" value="'.$display.'">'."\n";
	print '<input type="hidden" name="mon_nr" value="'.$mon_nr.'">'."\n";
	print '<input type="submit" name="btn" value="'.$l_mon_addnew.'">'."\n";
	print '<input type="reset" name="btn" value="'.$strRevoke.'" onclick="reset_to_list();">'."\n";
	print '</form>'."\n";
}
// phpinfo ();
require ('../foot.inc.php');
?>
