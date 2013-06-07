<?php
/**
 * @file lang/russian/utf-8/_admin_bugreport.php
 * @brief Файл переводов для страницы admin/bugreport.php
 */
/* BUG START ******************************/
$r_bugs = 'Сообщить о ошибках в программе для ЭВМ AVReg<br />
на видеосервере &#171;%s&#187; [%s].';

$bugs_rules = <<<EOD
Правила и рекомендуемая последовательность действий:
<ol>
<li>Пользоваться этой страницей нужно только с клиента (хоста) с которого посредcтвом <a href="http://avreg.net/manual_tuning_avreg-site.html#sudo" target="_blanc">настроек sudo</a> позволятется управлять демоном avregd на <a href="/admin/control.php">странице Управление</a>.
<li>Получите архив с лог-файлами и настройками AVReg (кнопка ниже) как можно скорее с момента возникновения ошибки.</li>
<li>Отправьте полученный архив лог-файлов с указанием номера камеры, описанием ошибки и времени её возникновения на e-mail <a href="mailto:avreg-support@mail.ru?subject=bugreport" title="AVReg bug report">avreg-support@mail.ru</a></li>
</ol>
EOD;

$sMaxTailNum = 'Количество последних строк в &#171;' . $conf['daemon-log'] . '&#187';
$sWaitLogsDownload = 'Ждите начала загрузки файла ...';
$strGetLogs = 'Скачать лог-файлы';
/******************************** BUG END */
?>
