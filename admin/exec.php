<?php
require('../head.inc.php');
DENY($admin_status);

if (!isset($cmd)) {
	die('crack?');
}

if (!preg_match('/(date$|ifconfig$|netstat)/', $cmd)) {
	die('crack?');
}

print '<pre class="tty">' . "\n";
print 'avreg@root # ' . $cmd . "\n";
@passthru($cmd);
print '</pre>' . "\n";

require('../foot.inc.php');
?>
