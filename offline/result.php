<?php
/**
 * 
 * @file offline/result.php
 * @brief Просмотр списка отфильтованных событий
 */
if (isset($_POST)) {
   $expire=time()+5184000;
   $_pg = dirname($_SERVER['SCRIPT_NAME']) . '/' ;
   if (isset($_POST['cams']))
      setcookie('avreg_cams[]',implode('-',$_POST['cams']),$expire,$_pg);
   if (isset($_POST['filter']))
      setcookie('avreg_filter[]',implode('-',$_POST['filter']),$expire,$_pg);
   if (isset($_POST['scale']))
      setcookie('avreg_scale',$_POST['scale'],$expire,$_pg);
   if (isset($_POST['row_max']))
      setcookie('avreg_row_max',$_POST['row_max'],$expire,$_pg);
   if (isset($_POST['play_tio']))
      setcookie('avreg_play_tio',$_POST['play_tio'],$expire,$_pg);
   if (isset($_POST['embed_video']))
      setcookie('avreg_embed_video','1',$expire,$_pg);
   else
      setcookie('avreg_embed_video','0',$expire,$_pg);
}
$include_javascripts = array('offline/result.js');
$pageBgColor='#cccccc';
$body_onload='on_body_load();';
$body_style='margin: 0px 0px 0px 0px; padding: 0px 0px 0px 0px; ';
$lang_file = '_offline.php';
require ('../head.inc.php');
DENY($arch_status);

?>

<script type="text/javascript" language="javascript">
<!--
   if (ie||ns6)
      tipobj=document.all? 
      document.all['tooltip'] :
      document.getElementById? document.getElementById('tooltip') : '';

document.onmousemove=positiontip;

// -->
</script>

<?php
if (!isset ($cams) || !isset ($filter))
{
   echo '<div class="help" style="font-size:125%">'.$strNotCamsChoice.'</div>'."\n";
} else {
   /* check cams on allow_cams */
   foreach ($cams as &$value) {
      if ( !settype($value,'int') )
         die('crack or hack?');
   }
   if ( !empty($GCP_cams_list) ) {
      $a = array_intersect($allow_cams, $cams);
      $cams = array_values($a);
      if ( count($cams) === 0  )
         die('crack or hack?');
   }

   if ((isset($timemode) && $timemode > 1) && (!isset($dayofweek) || count($dayofweek)===0)) 
   {
      print '<div class="help" style="font-size:125%">'.$strNotCamsChoice.'</div>'."\n";
      require ('../foot.inc.php');
      die;
   }
   $commentSQL = '';
   $events = array();
   if (isset($filter) && is_array($filter))
   {
      reset($filter);
      while( list($key, $val) = each($filter) )
      {
         if (!settype($val,'int')) die('bad filter set');
         array_push($events, $val);
      }
   }
   if (count($events)==0) 
   {
      print '<div class="help" style="font-size:125%">'.$strNotCamsChoice.'</div>'."\n";
      require ('../foot.inc.php');
die;
   }

   if (!isset($page)) $page = 0;
   if (isset($btNext)) $page++;
   if (isset($btLast)) $page--;
   $row_start = $page * $row_max;
   /* Performing new SQL query */

  
   
   $date = array (
		'from' => array($year_array[$year1],$month1,$day1,$hour1,$minute_array[$minute1]),
   		'to' => array($year_array[$year2],$month2,$day2,$hour2,$minute_array[$minute2]),
   );
   

   //Если ищем видео - добавляем в поиск video+audio (EVT_ID==12)
   $is_video = false;
   foreach ($events as $val){
   	 if($val==23) $is_video = true;
   }
   if($is_video)array_push($events, 12);
   
   $result = $adb->events_select($cams, $timemode, $date, $events, isset($dayofweek) ? $dayofweek : array(), array('limit' => $row_max, 'offset' => $row_start));
   
   $num_rows = 0;
   $res_array=array();
   foreach ( $result as $row)
   {
      $res_array[$num_rows] = $row;
      if ($num_rows===0)
         $first_savetime = (int)$row['UDT1'];
      else
         $last_savetime = (int)$row['UDT2'];
      $num_rows++;
   }
   unset($row);

   
//   print "<pre>\n";
//   var_dump($res_array);
//   print "</pre>\n";
//    exit();
   
   if ( $num_rows == 0 && $page == 0 ) {
      print '<div class="warn"><h3>'.$strNotSavedPict.'</h3></div>'."\n";
   } else {

      print '<div class="help" style="text-align:center;">';
      printf($fmtSavedPict,
         isset($first_savetime)?strftime('%a, %d %b %H:%M',$first_savetime):'?',
         isset($last_savetime)?strftime('%a, %d %b %H:%M',$last_savetime):'?',
         $page+1,
         $num_rows);
      print '</div>'."\n";
      if ( $page > 0 )
         $strLastSql = '&lt;&lt; ' . sprintf ($fmtLast,$row_max) ;
      if ( $num_rows == $row_max )
         $strNextSql = sprintf ($fmtNext,$row_max) . ' &gt;&gt;';
   }
   /* Print Form */
   require ('./_res.form.inc.php');
   /* Print Table Data */
   if ( $num_rows > 0 )
   {
      //error_reporting(E_ERROR|E_PARSE|E_CORE_ERROR|E_COMPILE_ERROR|E_USER_ERROR);
      print $tabletag . "\n";
      print '<tr bgcolor="'.$header_color.'">'."\n";
      print '<th>&nbsp;</th>'."\n";
      print '<th nowrap>'.$strCam.'</th>'."\n";
      print '<th nowrap>'.$strTime.'</th>'."\n";
      print '<th>'.$strComment.'</th>'."\n";
      print '</tr>'."\n";

      reset($res_array);
      $good_link=0;
      for ( $p_count=0; $p_count<$num_rows; $p_count++)
      {
         $comment_q = '';
         $row=&$res_array[$p_count];
         /*  UDT1, UDT2, CAM_NR, EVT_ID, SER_NR, FILESZ_KB, FRAMES, U16_1, U16_2, EVT_CONT */
         $UDT1 = (int)$row['UDT1'];
         $UDT2 = (int)$row['UDT2'];
         $CAM_NR = (int)$row['CAM_NR'];
         $EVT_ID = (int)$row['EVT_ID'];
         $SER_NR = (int)$row['SER_NR'];
         $FILESZ_KB = (int)$row['FILESZ_KB'];
         $FRAMES = (int)$row['FRAMES'];
         $U16_1 = (int)$row['U16_1'];
         $U16_2 = (int)$row['U16_2'];
         $EVT_CONT = &$row['EVT_CONT'];

         $ftype_str = (isset($env_id_ar[$EVT_ID]))?$env_id_ar[$EVT_ID]:'unknown';
         //Для случая видео+аудио EVT_ID==12
         if($EVT_ID==12){
         	$ftype_str = (isset($env_id_ar[23]))?$env_id_ar[23]:'unknown';
         }
         
         if ($CAM_NR !==0 )
         {

            $f_duration_str=DeltaTimeHuman($UDT1-$UDT2);

            /* _cam_nr, _evt_id, _utime1, _utime2, _ser_nr, _fsize, _frames, _u16_1, _u16_2, _ftype_str, _fduration, _fname */

            $for_js=$row['CAM_NR'].'~'.
               $row['EVT_ID'].'~'.
               $row['UDT1'].'~'.
               $row['UDT2'].'~'.
               $row['SER_NR'].'~'.
               filesizeHuman($FILESZ_KB).'~'.
               $row['FRAMES'].'~'.
               $row['U16_1'].'~'.
               $row['U16_2'].'~'.
               $ftype_str.'~'.
               $f_duration_str.'~'.
               $EVT_CONT;
            /*
        print_r($for_js);
        die;
             */
         }
         if ( $p_count % 2 )
            print '<tr bgcolor="#CCCCCC">'."\n";
         else 
            print '<tr bgcolor="#C3C3C3">'."\n";

         if ( $CAM_NR !== 0 )
         {
            if ( $EVT_ID === 23/*video avi*/) {
               print '<td valign="middle"><a name="video" href="javascript:mark_row('.$good_link.');"><img id="'.$EVT_ID.'" src="'.$conf['prefix'].'/img/movie.gif" width="20" height="22" border="0" name="'.$for_js.'" onmouseover="mouse_img ('.$good_link.');" onmouseout="mouse_img();"></a></td>'."\n";
               $good_link++;
            } else if ( $EVT_ID === 32 /*audio*/ ) {
               print '<td valign="middle"><a name="audio" href="javascript:mark_row('.$good_link.');"><img id="'.$EVT_ID.'" src="'.$conf['prefix'].'/img/audio-off.gif" width="20" height="22" border="0" name="'.$for_js.'" onmouseover="mouse_img ('.$good_link.');" onmouseout="mouse_img();"></a></td>'."\n";
               $good_link++;
            } else if ( $EVT_ID >= 15 && $EVT_ID <= 17 /* snapshot jpegs */ ) {
               print '<td valign="middle"><a name="jpeg" href="javascript:mark_row('.$good_link.');"><img id="'.$EVT_ID.'" src="'.$conf['prefix'].'/img/camera.gif" width="22" height="22" border="0" name="'.$for_js.'" onmouseover="mouse_img ('.$good_link.');" onmouseout="mouse_img();"></a></td>'."\n";
               $good_link++;
            }elseif($EVT_ID === 12 /*video+audio*/){
            	print '<td valign="middle"><a name="video" href="javascript:mark_row('.$good_link.');"><img id="'.$EVT_ID.'" src="'.$conf['prefix'].'/img/movie.gif" width="20" height="22" border="0" name="'.$for_js.'" onmouseover="mouse_img ('.$good_link.');" onmouseout="mouse_img();"></a></td>'."\n";
            	$good_link++;
            } else
               print '<td valign="middle">&nbsp;</td>'."\n";
            print '<td align="center" valign="middle" nowrap>- '.$CAM_NR.' -</td>'."\n";
         } else {
            print '<td valign="middle">&nbsp;</td>'."\n";
            print '<td align="center" valign="middle">&nbsp;-&nbsp;</td>'."\n";
         }
         print '<td nowrap="nowrap" valign="middle">'.strftime ( '%d.%m.%y %H:%M:%S', $UDT1).'</td>'."\n";

         if ( $EVT_ID === 23 || $EVT_ID === 32) {
            $f_ext=substr(strrchr($EVT_CONT, '.'), 1);
            print '<td class="small_text">'.$ftype_str.'&nbsp;('.$f_ext.', '.$f_duration_str.')</td>'."\n";
         } else if ( $EVT_ID >= 15 && $EVT_ID <= 17 /* snapshot jpegs */ ){
            print '<td class="small_text">'.$ftype_str.'</td>'."\n";
         }elseif( $EVT_ID === 12 ){
         	$f_ext=substr(strrchr($EVT_CONT, '.'), 1);
         	print '<td class="small_text">'.$ftype_str.'&nbsp;('.$f_ext.', '.$f_duration_str.')</td>'."\n";
         }
         else 
            print '<td class="small_text">'.$EVT_CONT.'</td>'."\n";
         print '</tr>'."\n";
      } // for ()
      print '</table>' . "\n";
      require ('./_res.form.inc.php');
   } // if num_row > 0
}
require ('../foot.inc.php');
