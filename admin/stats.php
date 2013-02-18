<?php
/**
 * @file
 * @brief Статистика используемых ресурсов
 */
require ('../head.inc.php');

$upstart_used = file_exists('/etc/init/avreg.conf');
require('_vidserv_status.inc.php');


/**
 * 
 * Функция определяющая критичность загрузки, возвращая цвет критичности
 * @param int $val текущее значение
 * @param int $warn_val предупреждающее значение
 * @param int $max_val максимальное критическое значение
 * @return string color
 */
function color_level ($val, $warn_val, $max_val)
{
  if ($val >= $max_val ) 
    return 'color:Red; font-weight: bold;';
  else if ( $val >= $warn_val ) 
   return 'color:#CC6600; font-weight: bold;';
  else 
   return 'color:#009900;';
}

echo '<h1>' . $r_stats . '</h1>' ."\n";

print_daemons_status($upstart_used, NULL);

echo '<h2>' . $r_cpu_stat . '</h2>' ."\n";
exec($conf['iostat'].' -c 2>/dev/null', $lines, $retval);
if ( $retval === 0 ) {
   $tmp = strtr(trim($lines[3]), array(','=>'.'));
   preg_match('/^([0-9\.]+) +([0-9\.]+) +([0-9\.]+) +([0-9\.]+) +([0-9\.]+) +([0-9\.]+)/',
         $tmp, $matches);
   // echo '<pre>'; var_dump($matches); echo '</pre>';
   $allcpu_user   = round((float)$matches[1]);
   $allcpu_nice   = round((float)$matches[2]);
   $allcpu_system = round((float)$matches[3]);
   $allcpu_iowait = round((float)$matches[4]);
   $allcpu_steal = round((float)$matches[5]);
   $allcpu_idle = round((float)$matches[6]);
   $allcpu_total = $allcpu_user + $allcpu_system;
	print '<table cellspacing="0" border="1" cellpadding="5">'."\n";
	print '<tr bgcolor="'.$header_color.'">'."\n";
    print '<th>&nbsp;</th>'."\n";
	print '<th>user</th>'."\n";
	print '<th>system</th>'."\n";
    print '<th>total</th>'."\n";
	print '</tr>'."\n";
	print '<tr>'."\n";
    print '<th>Total CPU usage avg, %</th>'."\n";
	print '<td align="center" style="'.color_level($allcpu_user,40,60).'">'.
         $allcpu_user.'</td>'."\n";
	print '<td align="center" style="'.color_level($allcpu_system,10,30).'">'.
          $allcpu_system.'</td>'."\n";
    print '<td align="center" style="'.color_level($allcpu_total,50,70).'">'.
         $allcpu_total.'</td>'."\n";
	print '</tr>'."\n";
 	print '</table><br />'."\n";
}
/**
 * 
 * Функция выводящая информацию о процессе: cpu, mem... 
 * @param string $proc_name имя процесса
 * @param int $pid ид процесса
 */
function pr_proc_stat($proc_name, $pid=NULL) 
{
   $p_info = proc_info($proc_name, $pid);
   if ($p_info === false) {
      return false;
   } else {
      $cpu = $p_info[0];
      $mem = $p_info[1];
      $vsz = $p_info[2];
      $rss = $p_info[3];
      print '<tr>'."\n";
      print '<td>'.$proc_name.'</td>'."\n";
      print '<td align="center">'.$cpu.'</td>'."\n";
      print '<td align="center">'.$mem.'</td>'."\n";
      print '<td align="center">'. $vsz.'</td>'."\n";
      print '<td align="center">'.$rss.'</td>'."\n";
      print '</tr>'."\n";
      return true;
   }
}
/// ид процессов
$server_pids = @glob('/var/run/avreg/'.$conf['daemon-name'].'*\.pid');
if ( $server_pids ) 	{
   print '<table cellspacing="0" border="1" cellpadding="5">'."\n";
   print '<tr bgcolor="'.$header_color.'">'."\n";
   print '<th>&nbsp;</th>'."\n";
   print '<th>'.$strCPU.'</th>'."\n";
   print '<th>'.$strMEM.'</th>'."\n";
   print '<th>'.$strVSIZE.'</th>'."\n";
   print '<th>'.$strRSS.'</th>'."\n";
   print '</tr>'."\n";
   foreach ($server_pids as $filename) {
      if ( is_file($filename) && is_readable($filename)) {
         $pid_a = @file($filename);
         $pid = chop($pid_a[0]);
         if (settype($pid, 'int')) {
            pr_proc_stat(basename($filename, '.pid'), $pid);
         }
      }
   }
   pr_proc_stat('avreg-mon');
   print '</table>'."\n";
}

echo '<h2>' . $r_mem_stat . '</h2>' ."\n";
/// Получение информации о памяти
$lines = file('/proc/meminfo');
if ( count($lines) > 0 ) {
/* kernel 2.6 */
  preg_match('/^[^\d]+(\d+)\s+kB$/', $lines[0], $matches);
  $mem_phyz_total=round($matches[1]/1000,0);
  preg_match('/^[^\d]+(\d+)\s+kB$/', $lines[1], $matches);
  $mem_phyz_free=round($matches[1]/1000,0);
  $mem_phyz_used = $mem_phyz_total - $mem_phyz_free;

  preg_match('/^[^\d]+(\d+)\s+kB$/', $lines[11], $matches);
  $swap_total=round($matches[1]/1000,0);
  preg_match('/^[^\d]+(\d+)\s+kB$/', $lines[12], $matches);
  $swap_free=round($matches[1]/1000,0);
  $swap_used=$swap_total-$swap_free;
} else {
/* kernel 2.4 */
  $mems = preg_split('/[\s]+/', $lines[1]);
  $mem_phyz_total = ((int)$mems[1]) >> 20;
  $mem_phyz_used = ((int)$mems[2]) >> 20;
  $mem_phyz_free = ((int)$mems[3]) >> 20;
  $swaps = preg_split('/[\s]+/', $lines[2]);
  $swap_total = ((int)$swaps[1]) >> 20;
  $swap_used = ((int)$swaps[2]) >> 20;
  $swap_free = ((int)$swaps[3]) >> 20;
}
print '<table cellspacing="0" border="1" cellpadding="5">'."\n";
print '<tr bgcolor="'.$header_color.'">'."\n";
print '<th>&nbsp;</th>'."\n";
print '<th>'.$total_space.', '.$byteUnits[2].'</th>'."\n";
print '<th>'.$used_space.', '.$byteUnits[2].'</th>'."\n";
print '<th>'.$total_free.', '.$byteUnits[2].'</th>'."\n";
print '</tr>'."\n";
print '<tr>'."\n";
print '<td align="center">mem</td>'."\n";
print '<td align="center">'.$mem_phyz_total.'</td>'."\n";
print '<td align="center">'. $mem_phyz_used.'</td>'."\n";
print '<td align="center">'.$mem_phyz_free.'</td>'."\n";
print '</tr>'."\n";
print '<tr>'."\n";
print '<td align="center">swap</td>'."\n";
print '<td align="center">'.$swap_total.'</td>'."\n";
print '<td align="center">'. $swap_used.'</td>'."\n";
print '<td align="center">'.$swap_free.'</td>'."\n";
print '</tr>'."\n";
print '</table>'."\n";

echo '<h2>' . sprintf($r_stats_df,$conf['storage-dir'])  . '</h2>' ."\n";
//$dts = round( disk_total_space($conf['storage-dir']) /  (1024*1024*1024) , 1) ;
/// Размер свободного места на диске в Гб
$dfs = round( disk_free_space($conf['storage-dir']) / (1024*1024*1024) , 1);

/// команда определения использования диска
$cmd = $conf['df'].' -hT '.$conf['storage-dir'];
unset($outs);
exec('LANG='.$locale_str.' '.$cmd,$outs,$retval);
print '<div class="info">' ."\n";
if ($dfs<$conf['warn-disk-free'])
   echo '<pre style="color:Red;">';
else
   echo '<pre>';
echo '$ df -hT '.$conf['storage-dir']."\n";
foreach ($outs as $line)
   echo $line."\n";
echo '</pre>' ."\n";
print '</div>' ."\n";

/*
$fmt_proc= '%0.1f (%d%%)';
print '<table cellspacing="0" border="1" cellpadding="5">'."\n";
print '<tr bgcolor="'.$header_color.'">'."\n";
print '<th>'.$str_mnt_point.'</th>'."\n";
print '<th>'.$str_dev.'</th>'."\n";
print '<th>'.$total_space.', '.$byteUnits[3].'</th>'."\n";
print '<th>'.$used_space.', '.$byteUnits[3].'</th>'."\n";
print '<th>'.$total_free.', '.$byteUnits[3].'</th>'."\n";
print '</tr>'."\n";
print '<tr>'."\n";
print '<td align="left">'.$conf['storage-dir'].'</td>'."\n";
print '<td align="left">'.$allcpu_idle.'</td>'."\n";
print '<td align="center">'.$dts.'</td>'."\n";
print '<td align="center">'. sprintf($fmt_proc, $dts - $dfs, round((($dts - $dfs)*100/$dts),1)).'</td>'."\n";
if ($dfs<$conf['warn-disk-free'])
print '<td align="center" style="color:Red">'.sprintf($fmt_proc, $dfs, round(($dfs*100/$dts),0)).'</td>'."\n";
else
print '<td align="center">'.sprintf($fmt_proc, $dfs, round(($dfs*100/$dts),0)).'</td>'."\n";
print '</tr>'."\n";
print '</table>'."\n";

clearstatcache();
if ( is_readable('/var/log/vstat') )
{
echo '<h2>' . $r_ps_stat . '</h2>' ."\n";
print '<pre><div class="tty">'."\n";
passthru($conf['tail'] . ' -n 150 /var/log/vstat');
print '</div></pre>'."\n";
}
*/
require ('../foot.inc.php');
?>
