<?php

/**
 *
 * @file head-xhr.inc.php
 * @brief вариант head.inc.php для XHR запросов 
 * @return возвращает application/json
 */

ob_start();

require_once('lib/config.inc.php');

/**
 * Send http headers
 */
// Don't use cache (required for Opera)
$now = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: ' . $now);
header('Last-Modified: ' . $now);
header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0
header('Content-Type: text/json; charset=' . $chset);
