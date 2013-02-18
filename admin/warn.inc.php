<?php
clearstatcache();
error_reporting(E_ERROR|E_PARSE|E_CORE_ERROR|E_COMPILE_ERROR|E_USER_ERROR);
$warnStr=array();
$warnStr_cnt=0;
$dfs = round( disk_free_space($conf['storage-dir']) / (1024*1024*1024) , 1);
if ($dfs<$conf['warn-disk-free']) {
  $warnStr[$warnStr_cnt]=sprintf('На диске с видеоархивом осталось %0.1f '.$byteUnits[3], $dfs);
  $warnStr_cnt++;
}

$rmanfile = $conf['storage-dir'].'/'.$conf['removed-manually'];
$ret = filesize($rmanfile);
if ( $ret !== FALSE && $ret > 0 ) {
  $warnStr[$warnStr_cnt]='Обнаружен факт удаления файлов с диска вручную. Их список <a href="'.$conf['prefix'].$conf['media-alias'].'/'.$conf['removed-manually'].'" target="_blank">'.$rmanfile.'</a> ('.filesizeHuman($ret/1024).')';
  $warnStr_cnt++;
}

if ($warnStr_cnt>0)
{
  print '<div class="warn">Предупреждения:<ol>'."\n";
  for ($__i=0;$__i<$warnStr_cnt;$__i++)
  {
     print '<li>'.$warnStr[$__i].'</li>'."\n";
  }
  print '</ol></div>'."\n";
}
?>
