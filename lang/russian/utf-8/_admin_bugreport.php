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
<li>Получить/собрать лог-файлы как можно скорее с момента возникновения ошибки.</li>
<li>Детально и точно описать ситуацию с указанием времени возникновения ошибки.</li>
<li>Отправить архивом лог-файлов (1) с сопроводительным текстом (2) на e-mail <a href="mailto:avreg-support@mail.ru?subject=bugreport" title="AVReg bug report">avreg-support@mail.ru</a></li>
</ol>
EOD;

$sMaxTailNum='Количество последних строк в &#171;'. $conf['daemon-log'].'&#187';
$sWaitLogsDownload =  'Ждите начала загрузки файла ...';
$strGetLogs='Скачать лог-файлы';
/******************************** BUG END */
?>