<?php
/**
 * @file pda/head_pda.inc.php
 * @brief
 */

ob_start();
$sess_name = 'avreg_pda';
session_set_cookie_params(null, dirname($_SERVER['SCRIPT_NAME']) . '/');
session_name($sess_name);
session_start();

require_once('../lib/config.inc.php');
/**
 * Send http headers
 */
// Don't use cache (required for Opera)
$now = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: ' . $now);
header('Last-Modified: ' . $now);
header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0
header('Content-Type: text/html; charset=' . $chset);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php
	print '<link rel="SHORTCUT ICON" href="' . $conf['prefix'] . '/favicon.ico">' . "\n";
	print '<title>';
	if (isset($GLOBALS['pageTitle'])) {
		print($GLOBALS['pageTitle'] . ' [' . $named . ']');
	} else {
		print('PDA версия ' . $conf['server-name'] . '[' . $named . ']');
	}
	print '</title>' . "\n";
	print '<meta http-equiv="Content-Type" content="text/html; charset=' . $chset . '">' . "\n";
	print '<meta name="author" content="Andrey Nikitin &lt;nik-a at mail dot ru&gt;">' . "\n";
	if (isset($BaseTarget)) {
		print '<base target="' . $BaseTarget . '">' . "\n";
	}
	?>
	<style type="text/css" media="screen, handheld">
		body {
			font: small Arial, sans-serif;
			margin: 0;
			padding: 0 0 2px;
		}

		:link {
			color: #42629D
		}

		* {
			font-size: 100%;
			margin: 0
		}

		h1, h2, h3, h4, h5, h6 {
			font-weight: normal
		}

		table {
			border-collapse: collapse
		}

		th, td {
			padding: 1;
			border-collapse: collapse
		}

		ol, ul {
			list-style: none
		}

		ol, ul, li {
			padding: 0
		}

		input, textarea, select {
			font: normal 100% Arial, sans-serif
		}

		a {
			text-decoration: none
		}

		a:hover {
			text-decoration: underline
		}

		img {
			border: 0
		}

		b {
			font-weight: normal
		}

		i {
			font-style: normal
		}
	</style>
	<?php
	if (isset($css_links) && is_array($css_links)) {
		foreach ($css_links as &$__css_link) {
			print '<link href="' . $conf['prefix'] . '/' . $__css_link . '" rel="stylesheet" type="text/css" />' . "\n";
		}
	}
	if (isset($USE_JQUERY)) {
		if ($conf['debug']) {
			print '<script type="text/javascript" src="' . $conf['prefix'] . '/lib/js/jquery-1.7.1.min.js"></script>' . "\n";
		} else {
			print '<script type="text/javascript" src="' . $conf['prefix'] . '/lib/js/jquery-1.7.1.min.js"></script>' . "\n";
		}
	}
	if (isset($link_javascripts) && is_array($link_javascripts)) {
		foreach ($link_javascripts as &$__js_link) {
			print '<script type="text/javascript" src="' . $conf['prefix'] . '/' . $__js_link . '"></script>' . "\n";
		}
	}
	if (isset($include_javascripts) && is_array($include_javascripts)) {
		foreach ($include_javascripts as &$__js) {
			if (eregi('.php$', $__js)) {
				require($wwwdir . $__js);
			} else {
				readfile($wwwdir . $__js);
			}
		}
	}
	print '</head>';
	ob_end_flush();

	if (!isset($body_style)) {
		$body_style = '';
	}
	if (isset($pageBgColor) && !empty($pageBgColor)) {
		$body_style .= 'background-color: ' . $pageBgColor . ';';
	} else {
		$body_style .= 'visibility:visible;';
	}

	if (isset($body_onload) && !empty($body_onload)) {
		$_onload = 'onload="' . $body_onload . '"';
	} else {
		if (isset($load) && !empty($load)) {
			$_onload = 'onload="window.open(\'' . $load . '\', target=\'content\')"';
		} else {
			$_onload = '';
		}
	}

	if (!isset($body_addons)) {
		$body_addons = '';
	}

	printf('<body style="%s" %s %s>', $body_style, $_onload, $body_addons) . "\n";

	?>
<noscript>
	<center>
		<div class="help" style="width:50%;text-align:left;">
			Ваш браузер не использует JavaScript.<br>
			Включите поддержку JavaScript и перегрузите/обновите эту страницу.
		</div>
	</center>
</noscript>


