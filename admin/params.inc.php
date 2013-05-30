<?php

function translit_ru($rustr)
{
   if (empty($rustr))
      return $rustr;
   mb_regex_encoding('UTF-8');

   $patterns = array(
      'а','б','в','г','д','е','ё','з','и','й','к','л','м',
      'н','о','п','р','с','т','у','ф','х','ъ','ы','э',
      'А','Б','В','Г','Д','Е','Ё','З','И','Й','К','Л','М',
      'Н','О','П','Р','С','Т','У','Ф','Х','Ъ','Ы','Э',
      'ж','ц','ч','ш','щ','ь','ю','я',
      'Ж','Ц','Ч','Ш','Щ','Ь','Ю','Я');

   $replacements = array(
      'a','b','v','g','d','e','e','z','i','y','k','l','m',
      'n','o','p','r','s','t','u','f','h','`','i','e',
      'A','B','V','G','D','E','E','Z','I','Y','K','L','M',
      'N','O','P','R','S','T','U','F','H','`','I','E',
      'zh', 'ts', 'ch', 'sh', 'shch', '', 'yu', 'ya',
      'ZH', 'TS', 'CH', 'SH', 'SHCH', '', 'YU', 'YA');

   for ($i=0; $i<sizeof($patterns); $i++) {
      $rustr = mb_ereg_replace($patterns[$i], $replacements[$i], $rustr);
   }

   return preg_replace(array('/[^A-Za-z0-9\-_\.~]/u'), array('_'), $rustr);
}


function WhatViddev ( $dev_file )
{
   $ret = -1;

   if ( is_readable($dev_file) )
   {
      $handle = fopen ($dev_file, 'r');
      while (!feof ($handle)) {
         $buffer = fgets($handle, 256);
         if ( preg_match ('/^type +: VID_TYPE_CAPTURE.+/', $buffer))
         {
            $ret = 1;
            break;
         }	elseif 	( preg_match ('/^name +: Video loopback \d* input$/', $buffer)) {
            $ret = 2;
            break;
         }
      }
      fclose ($handle);
   }
   return $ret;
}

function find_param_defs($par_name) {
   $d = NULL;
   foreach ( $GLOBALS['PARAMS'] as &$d ) {
      if ( $d['name'] == $par_name)
         return $d;
   }
   return NULL;
}

function CheckParVal($_param, $_value)
{
   $ret = TRUE;
   if ( $_value === '' || is_null($_value) )
      return $ret;
   $par_defs = find_param_defs($_param);
   if ( NULL == $par_defs )
      die("Invalid param name: " . $_param);

   if ( !empty($par_defs['valid_preg']) ) {
      if ( !preg_match($par_defs['valid_preg'], $_value) )
         $ret = FALSE;
   } else {
      switch ( $par_defs['type'] )
      {
      case $GLOBALS['INT_VAL']:
         if ( !is_numeric($_value) )
            $ret = FALSE;
         break;
      case $GLOBALS['BOOL_VAL']:
         if ( !($_value == '0' || $_value == '1'))
            $ret = FALSE;
         break;
      }
   }
   if ( !$ret ) 
      print '<div class="error">'. sprintf($GLOBALS['strParInvalid'], $_value, $_param) .'</div>'."\n";

   return $ret;
}

function checkParam ( $parname, $parval, $def_val = NULL )
{
   switch ( $parname )
   {
   case 'v4l_dev':
   case 'v4l_pipe':
      if ($parname == 'v4l_dev' ) $dev_code = 1; else $dev_code = 2;
      $viddev_nums = array();
      if (is_dir('/proc/video/dev'))
      {
         /* 2.4 kernel = LinuxDVRv4.x */
         if ($dh = opendir('/proc/video/dev'))
         {
            while (($file = readdir($dh)) !== false) {
               if ( preg_match ('/^video(\d{1,2})$/', $file, $matches)) {
                  // print "A match was found.";
                  if ( $dev_code == WhatViddev('/proc/video/dev/'.$file) )
                     $viddev_nums[] = $matches[1];
               }
            }
            closedir($dh);
         }
         sort($viddev_nums, SORT_NUMERIC);
      } else {
         /* 2.6 kernel */
         $all_v4l_devs = glob('/dev/video[0-9]*');
         if ( FALSE === $all_v4l_devs ) {
            $ret = '<p style="color:'.$GLOBALS['error_color'].';">'. $GLOBALS['notVidDevs'] .'</p>'."\n";
            break; 
         }

         $all_v4l_devs_nrs = array();
         foreach ($all_v4l_devs as $file) {
            if ( preg_match ('/^\/dev\/video(\d+)$/', $file, $matches))
               $all_v4l_devs_nrs[] =  (int) $matches[1];
         }
         sort($all_v4l_devs_nrs, SORT_NUMERIC);

         if ( isset($GLOBALS['conf']['v4loop-dev-offset']) ) 
            $_v4loop_dev_offset = (int)$GLOBALS['conf']['v4loop-dev-offset'];
         else {
            $ret = '<p style="color:'.$GLOBALS['error_color'].';">not defined "v4loop-dev-offset"</p>'."\n";
            break;
         }
         $c=0;
         foreach ($all_v4l_devs_nrs as $_dev_nr) {
            if ( $dev_code === 1  /* v4l capturing dev */ ) {
               if ( $_dev_nr < $_v4loop_dev_offset)
                  $viddev_nums[] = $_dev_nr;
            } else /* v4l pipes */ {
               if ( $_dev_nr >= $_v4loop_dev_offset ) {
                  $c++;
                  if ( $c % 2 /*через одного */ )
                     $viddev_nums[] = $_dev_nr;
               }
            }
         }

      } /* 2.6 kernel */

      if ( count($viddev_nums) > 0 ) {
         $ret = getSelectHtmlByName('fields['.$parname.']', $viddev_nums, FALSE, 1, 0, $parval, TRUE, FALSE,
            '/dev/video');
      } else 
         $ret = '<p style="color:"'.$GLOBALS['error_color'].';">'. $GLOBALS['notVidDevs'] .'</p>'."\n";
      break;
   case 'norm':
      if ( $parval == '' || is_null($parval))
         $sel = '';
      else
         $sel = $GLOBALS['vid_standarts'][$parval];
      $ret = getSelectHtml('fields['.$parname.']', $GLOBALS['vid_standarts'], FALSE, 1, 0, $sel, TRUE, FALSE);
      break;
   case 'mask_file':
      if (empty($parval) )
         $ret = $GLOBALS['strEmptied'] . '<br>';
      else {
         $ret  = '<a href="'.$GLOBALS['conf']['prefix'].'/masks/'.basename($parval).'"  target="_blank">'.basename($parval).'</a><br>'."\n";
         $ret .= $GLOBALS['strDelete'].' &nbsp;&nbsp;<input type="checkbox" name="'.$parname.'_del"><br>'."\n";
      }
      $ret .= '<input type="hidden" name="MAX_FILE_SIZE" value="500000">'."\n";
      $ret .= '<input type="file" name="'.$parname.'" size=20 maxlength=200>'."\n";
      break;
   case 'video_src':
      $ret = getSelectHtmlByName('fields['.$parname.']', $GLOBALS['video_sources'], FALSE, 1, 0, $parval, TRUE, FALSE);
      break;
   case 'audio_src':
      $ret = getSelectHtmlByName('fields['.$parname.']', $GLOBALS['audio_sources'], FALSE, 1, 0, $parval, TRUE, FALSE);
      break;

   case 'rtsp_transport':
      $ret = getSelectHtmlByName('fields['.$parname.']', $GLOBALS['rtsp_transport'], FALSE, 1, 0, $parval, TRUE, FALSE);
      break;

   case 'rec_mode':
      if ( $parval == '' || is_null($parval))
         $sel = '';
      else
         $sel = $GLOBALS['recording_mode'][$parval];
      $ret = getSelectHtml('fields['.$parname.']', $GLOBALS['recording_mode'], FALSE, 1, 0, $sel, TRUE, FALSE);
      break;

   case 'rec_format':
      $ret = getSelectHtmlByName('fields['.$parname.']', $GLOBALS['recording_format'], FALSE, 1, 0, $parval, TRUE, FALSE);
      break;

   case 'rec_vcodec':
      $ret = getSelectHtmlByName('fields['.$parname.']', $GLOBALS['rec_vcodec'], FALSE, 1, 0, $parval, TRUE, FALSE);
      break;

   case 'input':
      $ret = getSelectHtml('fields['.$parname.']', array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15), FALSE, 1, 0, $parval, TRUE, FALSE);
      break;
   case 'A.force_fmt':
      $ret = getSelectHtmlByName('fields['.$parname.']', $GLOBALS['str_audio_force_fmt'], FALSE, 1, 0, $parval, TRUE, FALSE);
      break;

   case 'rec_acodec':
      $ret = getSelectHtmlByName('fields['.$parname.']', $GLOBALS['rec_acodec'], FALSE, 1, 0, $parval, TRUE, FALSE);
      break;

/*
   case 'rotate':
      if ( $parval == '' || is_null($parval) || $parval == '0' )
         $sel = '';
      else
         $sel = $GLOBALS['flip_type'][$parval-1];
      $ret = getSelectHtml('fields['.$parname.']', $GLOBALS['flip_type'], FALSE, 1, 1, $sel, TRUE, FALSE);
      break;
*/
   case 'v4l_hack':
      if ( $parval == '' || is_null($parval) || $parval == '0' )
         $sel = '';
      else
         $sel = $GLOBALS['v4l_hacks'][$parval-1];
      $ret = getSelectHtml('fields['.$parname.']', $GLOBALS['v4l_hacks'], FALSE, 1, 1, $sel, TRUE, FALSE);
      break;

   case 'events2db':
   case 'events2pipe':
      $ret = getChkbxByAssocAr('fields['.$parname.']', $GLOBALS['event_groups'],
         $parval, FALSE /* не работает select_all для имен содержащих []*/);
      break;
   default:
      $ret = '<p style="color: '.$GLOBALS['error_color'].'">'. sprintf($GLOBALS['unknownCheckParams'], $parname) .'</p>'."\n";
   } // switch

   return $ret;
}

/* CorrectParVal($parname, &$parval) */
function CorrectParVal($parname, $parval)
{
   return; /* disable function */
   switch ( $parname )
   {
   case 'text_left':
      $parval = translit_ru($parval);
      break;
   }
}
?>
