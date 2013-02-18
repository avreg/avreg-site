<?php
	require_once 'lib/config.inc.php';
	if(isset($_GET['user'])){
		$as_guest = trim($_GET['user']);
	}else{
		$as_guest = 'guest';
	}

    $url_redirect = $conf['protocol'].$conf['url_domen']."/index.php?user=".$as_guest;
	header("location:".$url_redirect);
	exit();
?>