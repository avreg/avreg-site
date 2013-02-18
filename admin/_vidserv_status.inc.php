<?php

/**
 * Строит init-команду для демона
 */
function get_full_cmd($_upstart_used, $_cmd, $_profile)
{
   if ( empty($_profile) /* нет профилей, одна копия демона */ ) {
      if ( $_upstart_used && $_cmd == 'reload' )
         return $GLOBALS['conf']['daemon'] . '-worker ' . $_cmd;
      else
         return $GLOBALS['conf']['daemon'] . ' ' . $_cmd;
   } else {
      if ( $_upstart_used )
         return $GLOBALS['conf']['daemon'] . '-worker ' . $_cmd . ' PROFILE=' . $_profile;
      else
         return $GLOBALS['conf']['daemon'] . ' ' . $_cmd . ' ' . $_profile;
   }
}

/**
 * Проверяет статус демонов и выводит на страницу.
 */
function print_daemons_status($upstart_used, $profile=NULL) {
   $daemon_states = array();
   // load avail profiles
   if ( !empty($profile) )
      $profiles = array($profile);
   else
      $profiles = &$GLOBALS['EXISTS_PROFILES'];

   print '<div class="warn">' ."\n";
   print '<p>' .$GLOBALS['r_conrol_state']. ":</p>\n";
   foreach ( $profiles as $path ) {
      $_profile = basename($path);
      $cmd = get_full_cmd($upstart_used, 'status', $_profile);
      unset($outs);
      exec($GLOBALS['conf']['sudo'].' '.$cmd, $outs, $retval);
      if ( $upstart_used ) {
         // avreg-worker (cpu1) start/running, process 6208
         // avreg-worker start/running, process 6208
         $running = (count($outs) > 0 && preg_match('@start/running@', $outs[0]) ) ;
      } else
         $running = ($retval === 0)?true:false;

      $daemon_states[$_profile]=$running;

      if ( $running ) {
         print '<span class="HiLiteBig">';
         $st = &$GLOBALS['strRunned'];
      } else {
         print '<span class="HiLiteBigErr">';
         $st = &$GLOBALS['strStopped'];
      }
      if ( empty($_profile) )
         $msg = sprintf('%s - %s',  $GLOBALS['videoserv'], $st);
      else
         $msg = sprintf('%s-%s - %s', $GLOBALS['videoserv'], $_profile, $st);
      print($msg . '</span>' ."\n");
      if (isset($outs) && is_array($outs)) {
         echo '<pre>';
         echo '# '.$cmd."\n";
         foreach ($outs as $line)
            echo $line."\n";
         echo '</pre>' ."\n";
      }
   }
   print '</div><br />' ."\n";
   return $daemon_states;
}
?>
