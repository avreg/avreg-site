<?php
require_once('../lib/config.inc.php');

clearstatcache();

if (empty($conf['report-bug']) || !is_executable($conf['report-bug'])) {
	die(sprintf(
		'invalid \'report-bug\' value, file \'%s\' not exists or not executable',
		$conf['report-bug']
	));
}

$bugreportcmd = escapeshellcmd($conf['report-bug']);

if (isset($howmatch) && !empty($howmatch) && settype($howmatch, 'int')) {
	$bugreportcmd .= ' ' . escapeshellarg($howmatch);
}

$fname = strftime('avreglog_%g%m%d%H%M.tgz');
$now = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: ' . $now);
header('Last-Modified: ' . $now);
header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0
header('Content-type: application/x-tar-gz');
header('Content-Disposition: attachment; filename="' . $fname . '"');
passthru($bugreportcmd);

?>
