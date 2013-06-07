<?php

/**
 *
 * @file foot.inc.php
 * @brief В файле реализует закрытие тегов \<HTML\> и \<BODY\> страниц сайта
 *
 */

if (!isset($NOBODY)) {
	$custom_footer = preg_replace(
		'%^' . $conf['prefix'] . '(/.+)\.php$%',
		'\1_footer.inc.php',
		$_SERVER['SCRIPT_NAME']
	);
	if (0 != strcmp($_SERVER['SCRIPT_NAME'], $custom_footer)) {
		if ($conf['debug']) {
			print '<div class="legend footer"><span class="legend">@include "' . $conf['customize-dir'] . $custom_footer . "\"</span>\n";
		}
		#tohtml($_SERVER['SCRIPT_NAME']);
		@include($conf['customize-dir'] . $custom_footer);
		if ($conf['debug']) {
			print "</div>\n";
		}
	}
	print '</body>' . "\n";
}
print '</html>' . "\n";
?>
