<?php
/**
 * @file /admin/update.php
 * @brief Информация по обновлению системы
 */
require ('../head.inc.php');

echo '<h1>' . sprintf($r_update,$named) . '</h1>' ."	n";

if ( isset($updateSW) )
{
	DENY($admin_status);
	$uploadfile = $conf['upload-dir'] . $_FILES['updates_file']['name'];
    if ( $_FILES['updates_file']['size'] > 0 &&
       move_uploaded_file($_FILES['updates_file']['tmp_name'], $uploadfile))
	{
		print '<pre>'."\n";
		passthru("{$conf['sudo']} $UPDATEPROC " .$_FILES['updates_file']['name'], $retval);
		print '</pre>'."\n";
        if ( $retval == 0 )
        	print '<p class="HiLiteBigWarn">' . $update_success . '</p>' ."\n";
        else {
            print '<p class="HiLiteBigErr">' . $update_errors . '</p>' ."\n";
            unset($updateSW);
        }
	} else {
        print '<p class="HiLiteBigErr">' . $get_file_errors . '</p>' ."\n";
		unset($updateSW);     
	}
    /*
    print('<pre>');
    print_r($_FILES);
    print('</pre>');
    */
}

if ( $admin_user && ! isset($updateSW))
{

echo $attention;

if (file_exists('/etc/linuxdvr-release')) {
print '<p>'.$ver_str.'</p>'."\n";
print '<p style="font-weight:bold;">'."\n";
$LDVR_VER=@file('/etc/linuxdvr-release');
if ($LDVR_VER !== FALSE && is_array($LDVR_VER)) {
  print($LDVR_VER[0]);
  if ( preg_match('/^LinuxDVR (v[.\d]+) .+/',$LDVR_VER[0],$matches))
  {
    $patch_ver_f = $conf['patch-dir'].'/'.$matches[1].'_patch_ver.txt';
    if (is_readable($patch_ver_f)) 
    {
       $patch_ver = @file($patch_ver_f);
       if ($patch_ver !== FALSE && is_array($patch_ver)) {
          printf('<br>With patch '.$patch_ver[0]);
       }
    }
  }
}
}
print '<hr size="1" noshade>'."\n";
print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" enctype="multipart/form-data">'."\n";
print '<input type="hidden" name="MAX_FILE_SIZE" value="2000000">'."\n";
print '<p class="HiLite">' . $strLoadNewUpdate.'</p>' ."\n";
print $strGetUpdateFile.'<input type="file" name="updates_file" size="30">'."\n";
print '<br><br><input type="submit" name="updateSW" value="'.$strDownloadUpdate.'">'."\n";
print '</form>'."\n";
}


// phpinfo();
require ('../foot.inc.php');
?>
