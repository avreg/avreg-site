<?php 
/**
* @file online/index.php
* @brief Наблюдение в реальном времени
* переадресует на страницу online просмотра online/view.php
*
* @page online Модуль наблюдения
* Модуль наблюдения в реальном времени
*
* Файлы модуля:
* - online/index.php
* - online/view.php
* - online/view.js
* 
*/
session_start();
$_SESSION['is_admin_mode'] = true;

?>
<!DOCTYPE html>
<html>
<head>
	<script src="../lib/js/jquery-1.7.1.min.js" type="text/javascript"></script>
	<script src="../lib/js/user_layouts.js" type="text/javascript"></script>
	<script type="text/javascript">
		$(function(){
			//переадресуем на онлайн просмотр 
	 		user_layouts.redirect('view.php', true);
		});
		
	</script>
</head>
<body></body>
</html>
