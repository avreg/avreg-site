<?php
$pageTitle = 'Сеансы записи';

$lang_file = '_admin_cams.php';
require ('head_pda.inc.php');
require ('../lib/my_conn.inc.php');
// phpinfo(INFO_VARIABLES);
if ( !isset($cams) || empty($cams))
   die('should use "cams" cgi param');
if (is_string($cams))
   $cams = explode(',', $cams);
foreach ($cams as &$value)
   settype($value, 'int');
$one_cam = ( count($cams) === 1 );
$cams_csv = implode(',', $cams);
$use_desc_order = empty($desc) ? '' : 'desc';

if ( isset($s) && isset($f) ) {
   $timebegin_unix = (int)$s;
   $timeend_unix   = (int)$f;
} else {
   if ( $until > 0 ) {
      /* [ dt1 .. (dt1 + until) ]*/
      $timebegin_unix = mktime($hour, $minute_array[$minute],  0, $month, $day, 2000 + $year_array[$year]);
      $_SESSION['timestamp'] = $timebegin_unix;
      $timeend_unix   = $timebegin_unix + $until*60 + 59 /*seconds*/;
   } else {
      /* [ (dt1 + until) .. dt1 ]*/
      $timeend_unix   = mktime($hour, $minute_array[$minute], 59, $month, $day, 2000 + $year_array[$year]);
      $timebegin_unix = $timeend_unix + $until*60 - 59 /*seconds*/;
      $_SESSION['timestamp'] = $timeend_unix;
   }
}
$timebegin = strftime('%Y-%m-%d %T', $timebegin_unix);
$timeend   = strftime('%Y-%m-%d %T', $timeend_unix);

$_SESSION['cams']  = $cams;
$_SESSION['until'] = (int)$until;
$_SESSION['desc']  = !empty($desc);
$_SESSION['oims']  = !empty($oims);

$recsess_sess_var_name = sprintf('offline_%s_%u_%u_%u_%u',
   implode('_', $cams),
   $use_desc_order ? 1 : 0, 
   empty($oims) ? 0 : 1, 
   $timebegin_unix, $timeend_unix);

$rec_sessions = null;
if ( isset($_SESSION[$recsess_sess_var_name]) ) {
   $rec_sessions = &$_SESSION[$recsess_sess_var_name];
} else {
   $sql_dt_range = "((E1.DT1 between '$timebegin' and '$timeend') and (E2.DT1 is null or E2.DT1 between '$timebegin' and '$timeend'))";
   $sql_join_on = "(E1.SER_NR = E2.SER_NR AND E1.CAM_NR = E2.CAM_NR AND E1.DT1 = E2.DT2 AND E1.EVT_ID = 13 AND E2.EVT_ID = 14)";
   $query = <<<_EOL_
select UNIX_TIMESTAMP(E1.DT1) as START, UNIX_TIMESTAMP(E2.DT1) as FINISH, E1.CAM_NR, E1.SER_NR
from EVENTS as E1 left join EVENTS as E2
  on $sql_join_on
where E1.CAM_NR in ($cams_csv)
  and E1.EVT_ID = 13
  and $sql_dt_range
order by E1.DT1 $use_desc_order
_EOL_;

   if ( $conf['debug'] )
      print '<div class="help"  style="font-size:85%">'.$query.'</div>'."\n";
   $result = mysql_query($query) or die('Query failed: `'.mysql_error().'`');
   while ( $row = mysql_fetch_array($result, MYSQL_NUM) )
      $rec_sessions[] = $row;
   if ( !$rec_sessions ) {
      print "<div style='padding: 10px;'>Ничего не найдено за этот период.<br>\n";
      print "<a href='javascript:window.history.back();' title='$strBack'>$strBack</a></div>\n";
      exit;
   }
   $_SESSION[$recsess_sess_var_name] = $rec_sessions;
}
session_write_close();

/* pagination */
require_once('paginator.inc.php');
$pagi = new PDA_Paginator($rec_sessions,
   isset($off) ? (int)($off) : 0,
   sprintf('offline.php?cams=%s&s=%u&f=%u%s%s',
      $cams_csv,  $timebegin_unix, $timeend_unix,
      empty($desc) ? '' : '&desc=1',
      empty($oims) ? '' : '&oims=1'),
      $conf,
      $conf['pda-links-per-page']);
$pagi->print_above();

$GCP_query_param_list=array('work', 'text_left', 'geometry', 'Hx2');
require_once('../lib/get_cams_params.inc.php');

/* print record session info into page */
print "<br>\n";
foreach($pagi as $row) { 
   $START  = (int)$row[0];
   $FINISH = (int)$row[1];
   $CAM_NR = (int)$row[2];
   $SER_NR = (int)$row[3];
   $cam_conf = &$GCP_cams_params[$CAM_NR];
   $cam_name = isset($cam_conf['text_left']) ? $cam_conf['text_left'] : '';

   print "<div style='margin: 1px 1px 10px 1px; pad: 2px 2px 2px 2px; border-bottom: 1px dotted;'>\n";
   printf('<a href="files.php?camera=%u&ser_nr=%u&s=%u&f=%u">%s</a>',
      $CAM_NR, $SER_NR,  $START,
      empty($oims) ? 0 : $FINISH,
      TimeRangeHuman($START, $FINISH));
   printf('<br>Продолжительность: %s', ETA($FINISH - $START));
   if ( !$one_cam )
      print "<br> $strCam: №$CAM_NR $cam_name";
   print "</div>\n";
}

$pagi->print_below();
print "<div><a href='./' title='$strHome'>$strHome</a></div>\n";

require ('../lib/my_close.inc.php');
require ('../foot.inc.php');
?>