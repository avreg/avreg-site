<?php

/**
 * 
 * @file lib/get_cam_url.php
 * @brief Формирование ссылки на видео с камеры сервера avregd
 * 
 */

$cams_subconf = load_profiles_cams_confs();

$__tmp = &$conf['avregd-httpd'];
eval("\$http_cam_location = \"$__tmp\";");
unset($__tmp);





/**
 * 
 * Функция, которая возвращает ссылку на просмотр видео с камеры
 * @param array $conf масив настроек
 * @param int $cam_nr номер камеры
 * @param string $media тип медиа
 * @param bool $append_abenc аутентификация пользователя
 * @return string адрес видео с камеры
 */
function get_cam_http_url($conf, $cam_nr, $media, $append_abenc=false)
{
   $cams_subconf = &$GLOBALS['cams_subconf'];

   if ( $cams_subconf && isset($cams_subconf[$cam_nr])
        && !empty($cams_subconf[$cam_nr]['avregd-httpd'])) {
      $_a = &$cams_subconf[$cam_nr]['avregd-httpd'];
      eval("\$url = \"$_a\";");
   } else{
      $url = $GLOBALS['http_cam_location'];
   }
   $path_var = sprintf('avregd-%s-path', $media);
   if (isset($conf[$path_var]))
      $url .= sprintf("%s?camera=%d", $conf[$path_var], $cam_nr);
   if ($append_abenc && !empty($GLOBALS['user_info']['USER'])) {
   	$url .= '&ab=' . base64_encode($GLOBALS['user_info']['USER'].':'.$_SERVER['PHP_AUTH_PW']);
   }

   return $url;
}


function get_cam_alt_url($alt_src, $cam_nr, $append_abenc)
{
	if( !isset($alt_src) || $alt_src=="") return '';
	$url = $alt_src;
	$test = array();
	preg_match("/\?camera=\d*/", $alt_src, $test);
	if(sizeof($test)==0){
		$url .= sprintf("?camera=%d", $cam_nr);
	}
	if ($append_abenc && !empty($GLOBALS['user_info']['USER'])) {
		$url .= '&ab=' . base64_encode($GLOBALS['user_info']['USER'].':'.$_SERVER['PHP_AUTH_PW']);
	}

	return $url;
}


?>
