<?php


/* redirect from main page */
$redir_page = null;
if ($user_status >= $operator_status /* config.inc.php */) {
	/* перенаправляем пользователей гпрупп "Операторы" и "Только просмотр"
		*  сразу к выбору раскладки просмотра */
	$redir_page = '/online/index.php';

	/* А этим грязным хаком вы можете всегда перенаправлять группу или конкретного пользователя
	 * сразу на страницу просмотра видео в реальном времени с конкретной (единственной) раскладкой.
	 * Пример для одного конкретного пользователя:
	 * if ( $login_user == 'operator' ) // например только для одного пользователя
	 *       $redir_page = '/online/view.php?mon_type=QUAD_4_4&cams_in_wins=5.6.7.8&PrintCamNames=1';
	 * Описание CGI-параметров:
	 *   mon_type - тип раскладки: ONECAM, QUAD_4_4, MULTI_6_9, MULTI_7_16, MULTI_8_16, QUAD_9_9,
	 *                             MULTI_10_16, MULTI_13_16, MULTI_13_25, QUAD_16_16, MULTI_16_25,
	 *                             MULTI_17_25, MULTI_19_25, MULTI_22_25, QUAD_25_25
	 *   cams_in_wins - номера камер в раскладке, перечисленные через точку(.),
	 *                  если в каких-то позициях нет камер, то нужно указывать номер камеры 0
	 *                  например 1.2.3.0.4 - в четвёртом окне ничего нет камеры,
	 *                  нумерацию окон в раскладках см. на online/index.php.
	 *   PrintCamNames - выводить крупно название камеры в шапке окна камеры или нет;
	 *                   [0,1], по-умолчанию - 0(нет).
	 *   EnableReconnect - автореконнект оборванного потока, только для IE; [0,1], по-умолчанию - 0(нет).
	 *   AspectRatio - cоотношение сторон окон камер;
	 *                 "calc" (по умолчанию) - как у камеры в главном окне (см. online/build_mon.php?mon_type=xxxx)
	 *                 "fs" - растянуть по размеру окна браузера.
	 */
}

if ($redir_page) {
	ob_end_clean(); // удаляем всё что /header.inc.php в хедеры и тело записал
	header(
		sprintf(
			'Location: %s://%s%s%s%s',
			!empty($_SERVER['SSL_PROTO']) ? 'https' : 'http',
			$_SERVER['SERVER_NAME'],
			(!empty($_SERVER['SSL_PROTO']) || ($_SERVER['SERVER_PORT'] != 80)) ? (':' . $_SERVER['SERVER_PORT']) : '',
			$conf['prefix'],
			$redir_page
		)
	);
	while (@ob_end_flush()) {
		;
	}
	exit();
}

echo '<h2 class="header" align="center">' . sprintf($fmtVidServ, $named) .
	'&nbsp;&nbsp;(<a class="main_links" style="font-size: 85%;" href="' . sprintf(
		$conf['prefix'] . '/admin/key.php?sip=%s&amp;named=%s',
		$sip,
		$named
	) . '">' . $license . '</a>)</h2>' . "\n";
print "<div  class='header' align='center'>$strYou: <a class='main_links' href='" . $conf['protocol'] . $conf['url_demon'] . "index.php?logout=1' title='$strLogout'>$login_user@$remote_addr</a></div>\n";

/*
if (empty($_SERVER['SSL_PROTO']))
  echo '<h2 align="center">' . sprintf($fmtVidServ, $named, $sip) . 
  '.&nbsp;&nbsp;&nbsp;<a href="https://'.$_SERVER['SERVER_NAME'].'">Войти безопасно (OpenSSL) &gt;&gt;</a></h2>' ."\n";
else
   echo '<h2 align="center">' . sprintf($fmtVidServ, $named, $sip) . 
      '&nbsp;&nbsp;'. $_SERVER['SSL_PROTO'] .'/'. $_SERVER['SSL_CHIPHER'].'</h2>' ."\n";
 */
?>
<script type="text/javascript" language="javascript">
	<!--
	if (self.screen) {
		width = screen.width;
		height = screen.height;
	} else if (self.java) {
		var jkit = java.awt.Toolkit.getDefaultToolkit();
		var scrsize = jkit.getScreenSize();
		width = scrsize.width;
		height = scrsize.height;
	} else {
		width = '1024';
		height = '768';
	}

	if ((width < 1024) || (height < 768)) {
		var res_warn = ReadCookie('avreg_resolution_warn');
		var warn_cnt = 0;
		if (res_warn != "")
			warn_cnt = parseInt(res_warn);

		if (warn_cnt < 3) {
			alert("\nВеб-интерфейс AVReg расчитан на разрешение экрана не менее 1024x768,\nтак как размер даже одного записанного или передаваемого видео обычно составляет\n640х480 или 756х568 пикселей. А помимо кадров, на страницах нужно разместить и элементы управления\n\nУ Вас разрешение экрана " + width + " x " + height + " и работать будет неудобно.\n\nЕсли у Вас современный монитор и Вы работаете в " + width + "x" + height + " из-за мелкого шрифта, просто переключитесь в 1280х1024 и увеличьте системный шрифт.\n\nНормальные операционные системы делают эту операцию очень хорошо и дополнительно умеют сглаживать шрифты.\n\n\nЭто предупреждение будет показано ещё " + (2 - warn_cnt) + " раз(а).");
			SetCookie('avreg_resolution_warn', ++warn_cnt, 30);
		}
	}
	// -->
</script>

