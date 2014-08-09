<?php
/**
 * @file lang/russian/utf-8/_online.php
 * @brief Файл переводов для страниц
 * - online/index.php
 * - online/build_mon.php
 * - online/view.php
 * - online/active_wc.inc.php
 */
/* ONLINE START ******************************/

$r_webcam_list = ' Видеокамеры,  разрешённые для просмотра<br>' .
    'по протоколу HTTP (в интернет-браузерах).';
$strNotWebcamDef = 'Нет ни одной разрешённой для просмотра по протоколу HTTP (через интернет-браузер) видеокамеры. ' .
    '<br>Если просмотр необходим - обратитесь к администратору системы.';
$strShowCam = 'Показать изображение с выбранных видеокамер';
$WebCam = 'Веб-камеры';
$fmtActiveWEBCAMS = 'Всего сконфигурировано для просмотра по сети - %d вебкамер(ы).';
$srtNoActiveWEBCAMS = 'Инсталлятор или администратор системы<br> не настроил ни одной камеры для просмотра
 через браузер.';
$strWcMons = 'Доступные раскладки(планы) просмотра.';
$strWcMons2 = 'Переход к выбору камер для конкретной раскладки по соответствующей ссылке.';
$strFitToScreen = 'Масштабировать на весь экран';
$strPrintCamNames = 'Выводить названия камер';
$strEnableReconnect = 'Пытаться востановить соединение при потере связи';
$WcListShow = array(
    'не показывать',
    'правильно сконфигурированные для просмотра по сети',
    'все'
);

$strToolbarControls = array(
    'on' => 'Вывести панель управления', //'Зафиксировать панель управления',
    'off' => 'Скрыть панель управления', //'Снять фиксацию с панели управления',
    'stop' => 'Стоп',
    'play' => 'Старт',
    'zoom_in' => 'Увеличить',
    'zoom_out' => 'Уменьшить',
    'orig_size' => 'Оригинальный размер',
    'cell_size' => 'Вписать в ячейку',
    'to_cam_interface' => 'Перейти в веб интерфейс IP-камеры',
    'max' => 'Развернуть',
    'min' => 'К раскладке',
    'select_view' => 'Выбрать раскладку',
    'ptz'=>'Панорамирование/наклон',
);

$strCamName_Yes = 'Да';
$strCamName_No = 'Нет';

$strAspectRatio = 'Пропорции';
$AspectRatioArray = array(
    'calc' => 'сохранять пропорции',
    'fs' => 'на весь экран',
);

$strReconnectTimeout = 'Интервал реконнекта';
$ReconnectTimeoutArray = array(
    '0' => 'без реконнекта',
    '3' => '3сек.',
    '5' => '5сек.',
    '10' => '10сек.',
    '15' => '15сек.'
);
$strReconnectBrowserInfo = 'Прим.: стабильно работает только в браузерах IE и Firefox.';

$sWcDefLayout = 'Определите расположение камер в плане раскладки и нажмите кнопку &#171;Показать ...&#187;.<br>
Обязательно определите камеру для главного окна раскладки,<br>выделенного рамкой другого цвета.';

/* MONITORS START */
$web_left_layouts_desc = 'Раскладки (планы просмотра) для наблюдения за камерами в реальном времени в веб-браузерах c';

$no_any_layout = 'Нет сконфигурированных раскладок';
$r_mons = $left_layouts_desc . ' видеосервера  &#171;%s&#187; [%s].';
$web_r_mons = $web_left_layouts_desc . ' видеосервера  &#171;%s&#187; [%s].';

$l_mon_list = 'список';
$layout_word = 'Раскладка';
$l_mon_addnew = 'создать раскладку';
$l_mon_admin_only = 'Функция доступна только администратору';
$r_mon_addnew = 'Создание новой раскладки #%d для %s монитора.';
$web_mon_addnew = 'Создание новой раскладки #%d .';

$r_mon_tune = 'Изменить раскладку #%d [%s] для %s монитора.';
$str_web_mon_tune = 'Изменить раскладку #%d [%s].';
$r_mon_list = 'Список общих раскладок, <span class="HiLite">определенных администратором</span>.';
$client_mon_list = 'Список <span class="HiLite">только ваших</span> раскладок.';
$r_mon_goto_list = 'назад к списку раскладок';
$r_mon_changed = 'Раскладка #%d [%s] для %s монитора успешно изменена.<br />Перезапустите программу локального
 просмотра &#171;' . $local_player_name . '&#187; (сервер &#171;' . $videoserv . '&#187; перезапускать не нужно).';
$web_r_mon_changed = 'Раскладка #%d [%s] успешно изменена.<br />Обновите в браузере страницу просмотра (сервер &#171;' .
    $videoserv . '&#187; перезапускать не нужно).';
$strNamed = 'с названием';
$strONECAM = '1 камера';
$strQUAD_4_4 = '4 камеры';
$strMULTI_6_9 = '6 камер';
$strPOLY_2x3 =& $strMULTI_6_9;
$strMULTI_7_16 = '7 камер';
$strMULTI_8_16 = '8 камер';
$strPOLY_2x4 =& $strMULTI_8_16;
$strQUAD_9_9 = '9 камер';
$strMULTI_10_16 = '10 камер';
$strPOLY_3x4 = '12 камер';
$strMULTI_13_16 = '13 камер';
$strMULTI_13_25 = & $strMULTI_13_16;
$strMULTI_17_25 = '17 камер';
$strMULTI_19_25 = '19 камер';
$strMULTI_22_25 = '22 камеры';
$strQUAD_16_16 = '16 камер';
$strMULTI_16_25 = & $strQUAD_16_16;
$strQUAD_25_25 = '25 камер';

$strWideScreen = ' (широкий экран)';
$strWide_2_2 = '2 камеры' . $strWideScreen;
$strWide_3_6 = '3 камеры' . $strWideScreen;
$strWide_6_6 = '6 камер' . $strWideScreen;
$strWide_5_15 = '5 камер' . $strWideScreen;
$strWide_9_15 = '9 камер' . $strWideScreen;
$strWide_15_15 = '15 камер' . $strWideScreen;
$strWide_12_24 = '12 камер' . $strWideScreen;
$strWide_15_24 = '15 камер' . $strWideScreen;
$strWide_18_24 = '18 камер' . $strWideScreen;
$strWide_21_24 = '21 камера' . $strWideScreen;
$strWide_24_24 = '24 камеры' . $strWideScreen;
$strWide_34_40 = '34 камеры' . $strWideScreen;
$strWide_28_28 = '28 камер' . $strWideScreen;
$strWide_40_40 = '40 камер' . $strWideScreen;

$strWide_18_18 = '18 камер' . $strWideScreen;
$strWide_9_18 = '9 камер' . $strWideScreen;
$strWide_12_18 = '12 камер' . $strWideScreen;
$strWide_15_18 = '15 камер' . $strWideScreen;

$strCamPosition = 'Расположение камер';
$sLeftDisplay = 'левый или единственный монитор';
$sRightDisplay = 'правый монитор';
$sRightDisplay1 = 'правого';
$sLeftDisplay1 = 'левого или единственного';

$fmtMonAddInfo = 'Добавляем раскладку &#171;%s&#187; c номером %d [%s] для %s монитора.';
$fmtWebMonAddInfo = 'Добавляем раскладку &#171;%s&#187; c номером %d [%s].';
$strMonAddInfo2 = 'Определите расположение камер в плане раскладки и нажмите кнопку &#171;Сохранить&#187;.<br>
Обязательно определите камеру для главного окна раскладки,<br />выделенного рамкой другого цвета.';

$r_moncam_list = ' Видеокамеры, разрешённые <span class="HiLite">для локального просмотра</span> в программе &#171;' .
    $local_player_name . '&#187;.';
$web_r_moncam_list = ' Видеокамеры, доступные <span class="HiLite">для просмотра в веб-браузерах</span>.';

$strNotViewCams = 'Нет ни одной камеры, правильно сконфигурированной для локального (на сервере)  ' .
    'просмотра  в программе &#171;' . $local_player_name . '&#187;<br><br>' .
    'Если локальный просмотр не нужен, то лучше так и оставить для экономии ресурсов компьютера.<br/>' .
    'Если нужен - смотрите настройки камер, разделы &#171;Наблюдение&#187; -&#062; &#171;локальное&#187;';

$strNotViewCamsWeb = 'Нет ни одной камеры, правильно сконфигурированной для просмотра веб-браузерами.';

$CamListShowMode = array(
    'не показывать',
    'правильно сконфигурированные для просмотра',
    'все, включая недоступные с причинами'
 );

$strViewCamsChange = 'Кто-то изменил расладку видеокамер для локального (с этого компьютера)' .
    ' просмотра программой &#171;' . $local_player_name . '&#187;. <br>' .
    'Обратитесь к администратору или инсталлятору системы.';
$strNotChoiceCam = 'Вы должны выбрать хотя бы одну камеру, иначе зачем определять пустую раскладку.';

$fmtLayoutDelConfirm = 'Вы уверены что хотите удалить раскладку #%d [%s]';
$fmtDeleteMonConfirm = $fmtLayoutDelConfirm . ' для %s монитора?' . "\n";
$strDeleteMon = 'Раскладка #%d [%s] для %s монитора удалена из конфигурации.' . "\n";

$strOnRightDisplay = 'на дополнительном (правом) мониторе';
$sLayoutNumber = 'Номер раскладки';
$strMonAddErr1 = 'Вы не выбрали тип раскладки.';

$fmtLayoutDeleted = 'Раскладка #%d [%s] удалена из конфигурации.' . "\n";
/* MONITORS END */

/* WEB MONITORS  */
$strWebCamsList = 'Список установленных WEB-камер';
$strAddWebCam = 'Добавить WEB-камеру';
$strWebCamName = 'Название камеры:';
$strWebUrlFs = 'URL WEB-камеры для полноэкранного отображения:';
$strWebUrlCell = 'URL WEB-камеры для отображения в раскладке: ';
/* WEB MONITORS END */

/******************************** ONLINE END */
