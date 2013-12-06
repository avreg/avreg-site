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

?>
<!DOCTYPE html>
<html>
<head>
	<script src="../lib/js/third-party/jquery.js" type="text/javascript"></script>
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
