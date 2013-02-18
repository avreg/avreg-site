<?php
/**
 * 
 * @file offline/_res.form.inc.php
 * @brief Форма скрытых параметров фильтрации для просмотра предыдущих/следующих записей
 * 
 */
if (isset($strLastSql) || isset($strNextSql))
{
	print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" enctype="application/x-www-form-urlencoded">'."\n";
	if ( is_array($cams) )
	{
		$ii = 0;
		while ($ii < count($cams))
		{
			print '<input type="hidden" name="cams[]" value="'.$cams[$ii].'">'."\n";
			$ii++;
		}
	} else
		print '<input type="hidden" name="cams[]" value="'.$cams.'">'."\n";
	print '<input type="hidden" name="timemode" value="'.$timemode.'">'."\n";
	print '<input type="hidden" name="year1" value="'.$year1.'">'."\n";
	print '<input type="hidden" name="year2" value="'.$year2.'">'."\n";
	print '<input type="hidden" name="month1" value="'.$month1.'">'."\n";
	print '<input type="hidden" name="month2" value="'.$month2.'">'."\n";
	print '<input type="hidden" name="day1" value="'.$day1.'">'."\n";
	print '<input type="hidden" name="day2" value="'.$day2.'">'."\n";
	if ( isset($dayofweek) && is_array($dayofweek) ) 
	{
		$ii = 0;
		while ($ii < count($dayofweek)) 
		{
			print '<input type="hidden" name="dayofweek[]" value="'.$dayofweek[$ii].'">'."\n";
			$ii++;
    	}
	}
		
	if ( is_array($filter) ) 
	{
		$ii = 0;
		while ($ii < count($filter)) 
		{
			print '<input type="hidden" name="filter[]" value="'.$filter[$ii].'">'."\n";
			$ii++;
    	}
	} else 
		print '<input type="hidden" name="filter[]" value="'.$filter.'">'."\n";
		
	print '<input type="hidden" name="hour1" value="'.$hour1.'">'."\n";
	print '<input type="hidden" name="hour2" value="'.$hour2.'">'."\n";
	print '<input type="hidden" name="minute1" value="'.$minute1.'">'."\n";
	print '<input type="hidden" name="minute2" value="'.$minute2.'">'."\n";
	print '<input type="hidden" name="scale" value="'.$scale.'">'."\n";
	print '<input type="hidden" name="row_max" value="'.$row_max.'">'."\n";
	print '<input type="hidden" name="page" value="'.$page.'">'."\n";
	print '<table width="95%" cellspacing="0" border="0" cellpadding="3" align="center"><tr>'."\n";
	print '<td width="50%" align="left">'."\n";
	if (isset($strLastSql))
		print '<input type="submit" name="btLast" value="'.$strLastSql.'">'."\n";
	else
		print '&nbsp;'."\n";
	print '</td>'."\n";
	print '<td width="50%" align="right">'."\n";
	if (isset($strNextSql))
		print '<input type="submit" name="btNext" value="'.$strNextSql.'">'."\n";
	else
		print '&nbsp;'."\n";
	print '</td>'."\n";
	print '</tr></table>'."\n";
	
	print '</form>'."\n";
}
?>
