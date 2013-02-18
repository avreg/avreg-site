<?php

$__DEF_CAM_DETAIL_COLUMNS = array(
   'ICONS'  => true,
   'CAM_NR' => true,
   'NAME'   => true,
   'SRC'    => true,
   'CAPS'   => true,
);

define('COMPACT_URL_LEN', 30);
function print_compact_url(&$url) {
   if (strlen($url) < COMPACT_URL_LEN ) {
      print($url);
      return;
   }
   printf('<span title="%s">%s...&rarr;</span>', $url, substr($url, 0, COMPACT_URL_LEN - 1));
}

function _warn_emptied_param($param, $print_warn)
{
   if ($print_warn)
      return '&nbsp;<span style="font-weight: bold;"> [«'.$param.'» '.$GLOBALS['strEmptied'].'] </span>&nbsp;';
   else
      return '${'.$param.'}';
}

/**
 * return NULL  if no video sorce
 *        TRUE  if has video and complete url
 *        FALSE if has video but not complete url 
 */
function cam_has_video($cam_detail, $print_warn, &$url)
{
   $ret = TRUE;
   $url = '';

   $vproto = &$cam_detail['video_src'];

   switch ( $vproto ) {
   case 'video4linux':
      $url = "v4l://";
      if ( !isset($cam_detail['v4l_dev']) ) {
         $url .= _warn_emptied_param('v4l_dev', $print_warn);
         $ret = FALSE;
      } else
         $url .= '/dev/video' . $cam_detail['v4l_dev'];
      $input = isset($cam_detail['input']) ? $cam_detail['input'] : 0;
      $url .= ":$input"; 
      break;
   case 'http':
      $url = 'http://';
      if ( empty($cam_detail['InetCam_IP']) ) {
         $url .= _warn_emptied_param('InetCam_IP', $print_warn);
         $ret = FALSE;
      } else
         $url .= $cam_detail['InetCam_IP'];
      if ( !empty($cam_detail['InetCam_http_port']) && $cam_detail['InetCam_http_port'] != 80 )
         $url .= ':' . $cam_detail['InetCam_http_port'];

      if ( empty($cam_detail['V.http_get']) ) {
         $url .= _warn_emptied_param('V.http_get', $print_warn);
         $ret = FALSE;
      } else
         $url .= $cam_detail['V.http_get'];
      break;
   case 'rtsp':
      $url = 'rtsp://';
      if ( empty($cam_detail['InetCam_IP']) ) {
         $url .= _warn_emptied_param('InetCam_IP', $print_warn);
         $ret = FALSE;
      } else
         $url .= $cam_detail['InetCam_IP'];
      if ( !empty($cam_detail['InetCam_rtsp_port']) && $cam_detail['InetCam_rtsp_port'] != 554 )
         $url .= ':' . $cam_detail['InetCam_rtsp_port'];
      if ( !empty($cam_detail['rtsp_play']) )
         $url .= $cam_detail['rtsp_play'];
      break;
   default:
      return NULL;
   }
   return $ret;
}
/**
 * return NULL  if no video sorce
 *        TRUE  if has video and complete url
 *        FALSE if has video but not complete url 
 */
function cam_has_audio($cam_detail, $print_warn, &$url)
{
   $ret = TRUE;
   $url = '';

   $aproto = &$cam_detail['audio_src'];

   switch ( $aproto ) {
   case 'alsa':
      $url = "ALSA://";
      if ( empty($cam_detail['alsa_dev_name']) ) {
         $url .= _warn_emptied_param('alsa_dev_name', $print_warn);
         $ret = FALSE;
      } else
         $url .= $cam_detail['alsa_dev_name'];
      break;
   case 'http':
      $url = 'http://';
      if ( empty($cam_detail['InetCam_IP']) ) {
         $url .= _warn_emptied_param('InetCam_IP', $print_warn);
         $ret = FALSE;
      } else
         $url .= $cam_detail['InetCam_IP'];
      if ( !empty($cam_detail['InetCam_http_port']) && $cam_detail['InetCam_http_port'] != 80 )
         $url .= ':' . $cam_detail['InetCam_http_port'];

      if ( empty($cam_detail['A.http_get']) ) {
         $url .= _warn_emptied_param('A.http_get');
         $ret = FALSE;
      } else
         $url .= $cam_detail['A.http_get'];
      break;
   case 'rtsp':
      $url = 'rtsp://';
      if ( empty($cam_detail['InetCam_IP']) ) {
         $url .= _warn_emptied_param('InetCam_IP', $print_warn);
         $ret = FALSE;
      } else
         $url .= $cam_detail['InetCam_IP'];
      if ( !empty($cam_detail['InetCam_rtsp_port']) && $cam_detail['InetCam_rtsp_port'] != 554 )
         $url .= ':' . $cam_detail['InetCam_rtsp_port'];
      if ( ! empty($cam_detail['rtsp_play']) )
         $url .= $cam_detail['rtsp_play'];
      break;
   default:
      return NULL;
   }
   return $ret;
}

function print_cam_detail_row($conf, $cam_nr, $cam_detail, $columns = null)
{
   if (isset($columns))
      $_cols = &$columns;
   else
      $_cols = &$GLOBALS['__DEF_CAM_DETAIL_COLUMNS'];
   $cam_active = ($cam_detail['work'] > 0);
   if ( $cam_nr > 0 )
      $cam_name = &$cam_detail['text_left'];
   else
      $cam_name = $GLOBALS['r_cam_defs2'];

   $cam_has_video = cam_has_video($cam_detail, ($cam_nr !== 0), $video_url);
   $cam_has_audio = cam_has_audio($cam_detail, ($cam_nr !== 0), $audio_url);

   /* print icons <td> */
   if ( isset($_cols['ICONS']) && $_cols['ICONS'] ) {
      print '<td>';
      if ( !is_null($cam_has_video) )
         printf('<img src="'.$conf['prefix'].'%s" title="video" alt="%s" width="35" height="32" border="0">' . "\n",
            $cam_active ? '/img/cam_on_35x32.gif' : '/img/cam_off_35x32.gif',
            $cam_active ? $GLOBALS['flags'][1] : $GLOBALS['flags'][0]
         );
      else
         print '<span style="margin-left: 32px"></span>' . "\n";

      if ( !is_null($cam_has_audio) )
         printf('<img src="'.$conf['prefix'].'%s" title="audio" alt="%s" width="32" height="32" border="0">' . "\n",
            $cam_active ? '/img/mic_on_32x32.gif' : '/img/mic_off_32x32.gif',
            $cam_active ? $GLOBALS['flags'][1] : $GLOBALS['flags'][0]
         );
      print '</td>';

   }

   /* print cameras number <td> */
   if ( isset($_cols['CAM_NR']) && $_cols['CAM_NR'] ) {
      if ( $cam_active )
         print '<td align="center" valign="center" nowrap><div style="font-weight: bold;">'.$cam_nr. '</div></td>' . "\n";
      else
         print '<td align="center" valign="center" nowrap><div>'. $cam_nr.'</div></td>' . "\n";
   }

   /* print cameras name <td> */
   if ( isset($_cols['NAME']) && $_cols['NAME'] ) {
      print '<td align="left" valign="center" nowrap>';
      if ( !empty($_cols['NAME']['href']) ) {
         $tag_a_cont = sprintf('href="%s?camera=%d" title="%s"',
                       $_cols['NAME']['href'], $cam_nr, $_cols['NAME']['title']);
         if ( $cam_active )
            print '<a '.$tag_a_cont.' style="font-weight: bold;">'.$cam_name. '</a>' . "\n";
         else
            print '<a '.$tag_a_cont.'>'. $cam_name.'</a>' . "\n";
      } else {
         if ( $cam_active )
            print '<div style="font-weight: bold;">'.$cam_name. '</div>' . "\n";
         else
            print '<div>'. $cam_name.'</div>' . "\n";
      }
      print "</div></td>\n";
   }

   /* print cameras source/type <td> */
   if ( isset($_cols['SRC']) && $_cols['SRC'] ) {
      print('<td>');
      if ( $cam_has_video === TRUE ) {
         print('V: ');
         print_compact_url($video_url);
      } else if ( $cam_has_video === FALSE )
         print('V: ' . $video_url);
      if ( $cam_has_audio === TRUE ) {
         if (  !is_null($cam_has_video) )
            print("<br />\n");
         print('A: ');
         print_compact_url($audio_url);
      } else if ( $cam_has_audio === FALSE ) {
          if ( !is_null($cam_has_video) )
            print("<br />\n");
        print('A: ');
        print($audio_url);
      }
      if ( is_null($cam_has_video) && is_null($cam_has_video) )
         print('&nbsp;');
      print('</td>');

   }

   /* print cameras short capabilities <td> */
   if ( isset($_cols['CAPS']) && $_cols['CAPS']) {
      if ( !is_null($cam_has_video) || $cam_nr == 0 )
         print '<td align="center" valign="center">'.$cam_detail['geometry']."</td>\n";
      else
         print '<td>&nbsp;</td>' . "\n";
   }
}
?>
