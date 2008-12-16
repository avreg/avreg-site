<?php

function
avreg_ipv4_cmp($a1, $m1, $a2, $m2)
{
   if ( !is_int($a1) || !is_int($m1) || !is_int($a2) || !is_int($m2) )
      return FALSE;

    $i1 = 0xFFFFFFFF & $a1 & $m1 & $m2;
    $i2 = 0xFFFFFFFF & $a2 & $m1 & $m2;

    if ( 0 === ($i1 ^ $i2) )
        return TRUE;
    else
        return FALSE;
} /* avreg_ipv4_cmp() */


function
avreg_inet_network($ip_addr_or_acl=NULL)
{
   $ip_pat = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/';
   if ( is_null($ip_addr_or_acl) && empty($ip_addr_or_acl) )
      return FALSE;
   $ret = array(
      'addr'   =>  nil,
      'addr_a' =>  '',
      'mask'   =>  nil,
      'mask_a' => '255.255.255.255');

    if ( 0 === strcasecmp($ip_addr_or_acl, 'localhost') ) {
        $ret['addr_a'] = '127.0.0.1';
    }  else if ( 0 === strcasecmp($ip_addr_or_acl, 'any') || $ip_addr_or_acl === '*' ) {
        $ret['addr_a'] = '0.0.0.0.0'; // INADDR_ANY
        $ret['mask_a'] = '0.0.0.0.0';
        $ret['addr'] = 0; // INADDR_ANY
        $ret['mask'] = 0;
        return $ret;
    } else if ( preg_match('%^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$%', $ip_addr_or_acl, $matches) ) {
      $ret['addr_a'] = $matches[1];
      $ret['mask_a'] = $matches[2];
    } else if ( preg_match('%^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/(\d{2})$%',
$ip_addr_or_acl, $matches) ) {
      $ret['addr_a'] = $matches[1];
      $ret['mask_a'] = $matches[2];
      $m = $ret['mask_a'];  settype($m,'int');
      if ( $m > 32 )
            return FALSE;
      $m32 = 0;
      if ( $m == 0 ) {
            $m32 = 0;
      } else {
            for ($i=1; $i<$m; $i++) {
                $m32 |= 0x80000000;
                $m32 >>= 1;
            }
            $m32 |= 0x80000000;
      }
      $ret['mask_a'] = long2ip($m32);
    } else if ( preg_match('%^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$%',
$ip_addr_or_acl, $matches) ) {
      $ret['addr_a'] = $matches[1];
    } else
        return FALSE;

   $ret['addr'] = ip2long($ret['addr_a']);
   $ret['mask'] = ip2long($ret['mask_a']);

   if ( $ret['addr'] === FALSE || $ret['mask'] === FALSE )
      return FALSE;
   else
      return $ret;
} /* avreg_ipv4_cmp() */

?>
