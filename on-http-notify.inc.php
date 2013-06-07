<?php

/**
 * @file on-http-notify.inc.php
 * @brief Конфигурирование системы отправки уведомлений
 *
 * Кусок ВАШЕГО php-кода, выполняемый по событию получения
 * HTTP уведомления (HTTP notification).
 * Если вы хотите как-то реагировать на сработку дискретных входов
 * ip-камер или ip-видеосерверов, правьте и используйте этот код.
 * Естественно, для этого нужно настроить и сами устройства.
 * В камерах Axis, например, настройка HTTP notification находится на
 * веб-странице Setup->Event Configuration->Event Settings.
 *
 * allow_url_fopen ДОЛЖЕН быть установлен в "1" в php.ini
 *
 * Некоторые "интересные" переменные php:
 * array $REQUEST - Параметры удалённого HTTP запроса (GET/POST)
 *                  например, необязательные:
 *                   - "nr" - номер камеры (уточнение для многоканальных ip-видеосерверов)
 *                   - "action" - действие "set" (по-умолч.) или "reset"
 *                   - "alarm"  - код тревоги, по-умолч. - 1
 *                   пример: "nr=3&alarm=2" или "nr=15&action=reset"
 *                   Прим.: в настройках Axis можно указывать в поле "Custom Parameters"
 * array $_SERVER - См. документацию PHP, например,
 *                  $_SERVER["REMOTE_ADDR"] - ip-адрес удалённой камеры
 *                  или видеосервера, пославших HTTP-уведомление.
 * array $AVREG_CAMS_NR  - Массив номеров активных (work=Вкл.) AVReg-овых камер,
 *                  найденных в AVReg-овой базе по IP-адресу $_SERVER["REMOTE_ADDR"]
 *                  Если переменная $AVREG_CAMS_NR не определёна, то это значит,
 *                  что запрос пришёл с "непрописанной" в базе AVReg камеры
 *                  или видеосервера, т.е. с "чужого" устройства.
 *                  Код нашего примера не обрабатывает "чужие" запросы,
 *                  однако, никто не запрещает вам изменить это поведение.
 */

/*
 * // посылаем сообщение на jabber или icq
 * if ( true ) {
 *   sendxmpp();
 * }
 *
 * // посылаем сообщение на мыло
 * if ( true ) {
 *   sendmail();
 * }
 *
 *   // посылаем сообщение на sms
 * if ( true ) {
 *   sendSMS();
 * }
 *
 */

/* to AVREG-MON */
if (true && isset($AVREG_CAMS_NR)) {
	/* Если нужно, "передаём" тревогу в локальный просмотрщик камер avreg-mon
		  делается это через http-запрос, подробности см. в описании
		  параметров группы "Настройки удалённого управления"
		  в секции avreg-mon {} конфигурационного файла /etc/avreg/avreg.conf */

	$res = confparse($conf, 'avreg-mon', '/etc/avreg/avreg.conf', array('remote-control'));

	if (!$res) {
		$err_s = 'avreg-mon does not use remote-control';
		die("<div>Received.</div><div>$err_s</div>\r\n</body></html>\r\n");
	}

	/* строим URL для avreg-mon-а, с учётом замены ANY-хоста "*" на localhost */
	$avreg_mon_url = preg_replace('/http:\/\/\*/', 'http://localhost', $res['remote-control']) . '/camera';

	/* Примечание: если одновременно используется 2 avreg-mon-а,
		  запущенные на левом $port и правом ($port + 1) дисплеях,
		  то вам придётся самим определять $avreg_mon_url в зависимости
		  от того, какая камера (по её номеру) где выводится */

	/* значения параметров запроса к avreg-mon-у по умолчанию */
	$cam_nr = $AVREG_CAMS_NR[0];
	$alarm_code = 1;
	$action_str = 'set';

	/* код тревоги по умолчанию 1 или должен передаваться параметром "alarm" */
	if (isset($_REQUEST)) {
		/* в запросе указан номер камеры, имеет смысл для ip-видеосерверов */
		if (isset($_REQUEST['nr'])) {
			$cam_nr = (int)$_REQUEST['nr'];
		}

		/* в запросе указан код тревоги */
		if (isset($_REQUEST['alarm'])) {
			$alarm_code = (int)$_REQUEST['alarm'];
		}

		if (isset($_REQUEST['action'])) {
			$action_str = escapeshellcmd((string)$_REQUEST['action']);
		}
	}

	if ($action_str == 'set') {
		$get_query = "nr=$cam_nr&param=alarm&action=$action_str&value=$alarm_code";
	} else {
		$get_query = "nr=$cam_nr&param=alarm&action=$action_str";
	}


	$file = fopen("$avreg_mon_url?$get_query", 'r');

	if (!$file) {
		$err_s = "fopen(\"$avreg_mon_url?$get_query\") failed";
		print_syslog(LOG_ERR, $err_s);
		die("<div>Received.</div><div>$err_s</div>\r\n</body></html>\r\n");
	}
	/* вычитываем ответ */
	$line = '';
	while (!feof($file)) {
		$line .= fgets($file, 1024);
	}
	fclose($file);
} /* to avreg-mon */

?>
