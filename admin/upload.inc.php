<?php
if (isset($_FILES) && is_array($_FILES)) {
	$paramsnames = array_keys($_FILES);
	if (!is_array($paramsnames)) {
		MYDIE('$paramsnames is not array', __FILE__, __LINE__);
	}

	$defPAR = ($cam_nr === 0) ? true : false;

	$file_cnt = count($paramsnames);
	for ($i = 0; $i < $file_cnt; $i++) {
		$clear_file = false;
		$_parname = & $paramsnames[$i];
		$uplfile = & $_FILES[$_parname];

		if (empty($uplfile['name']) && $uplfile['error'] === UPLOAD_ERR_NO_FILE) {
			if (isset($GLOBALS[$_parname . '_del'])) {
				$clear_file = true;
			} else {
				continue;
			}
		} else {
			if ($uplfile['error'] != 0) {
				die(sprintf(
					'<p class="HiLiteErr">Error upload file `%s\': %s</p>',
					$uplfile['name'],
					$upload_status[$uplfile['error']]
				));
			}
		}

		if (isset($suff)) {
			unset($suff);
		}
		if (!$clear_file) {
			$uploadfile = $conf['upload-dir'] . '/' . $uplfile['name'];
			switch ($_parname) {
				case 'mask_file':
					if (!$defPAR) {
						$_val = sprintf('%s/cam%03d_mask.pgm', $conf['masks-dir'], $cam_nr);
					} else {
						$_val = sprintf('%s/def_mask.pgm', $conf['masks-dir']);
					}
					// сохраняем файл и преобразовываем
					if (!move_uploaded_file($uplfile['tmp_name'], $uploadfile)) {
						die(sprintf(
							'<p class="HiLiteErr">Upload file `%s\' error: %s</p>',
							$uplfile['name'],
							$upload_status[$uplfile['error']]
						));
					} else {
						$djpeg = sprintf(
							'%s -grayscale -pnm -outfile \'%s\' \'%s\' 2>&1 >/dev/null',
							$conf['djpeg'],
							$_val,
							$uploadfile
						);
						// tohtml($djpeg);
						exec($djpeg, $output, $retval);
						if ($retval != 0) {
							@unlink($uploadfile);
							@unlink($_val);
							echo('<p class="HiLiteErr">MASK FILE `' . $uplfile['name'] . '\': JPEG conversion error:');
							if (is_array($output)) {
								foreach ($output as &$line) {
									echo '<br />' . $line . "\n";
								}
							}
							die('</p>');
						}
					}
					break;

				default:
					MYDIE($_parname . ' not supported', __FILE__, __LINE__);
			} // switch (parname)
		}

		// save to database
		if ($clear_file) {

			$adb->replace_camera('local', $cam_nr, $_parname, null, $remote_addr, $login_user);

			print_syslog(
				LOG_NOTICE,
				sprintf(
					'cam[%s]: set param `%s\' to NULL, old value `%s\'',
					($cam_nr) ? sprintf("%2d", $cam_nr) : 'ALL',
					$_parname,
					$olds[$_parname]
				)
			);
		} else {
			$adb->replace_camera('local', $cam_nr, $_parname, $_val, $remote_addr, $login_user);
			print_syslog(
				LOG_NOTICE,
				sprintf(
					'cam[%s]: set param `%s\' to `%s\', old value `%s\'',
					($cam_nr) ? sprintf("%2d", $cam_nr) : 'ALL',
					$_parname,
					$_val,
					$olds[$_parname]
				)
			);
			unlink($uploadfile);
		}
	} // for all uploaded files
} // if POST _FILES
?>
