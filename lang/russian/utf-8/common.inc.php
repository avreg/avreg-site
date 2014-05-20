<?php
/**
 * @file lang/russian/utf-8/common.inc.php
 * @brief Базовый перевод
 */
// ========================================================================
$charset = 'UTF-8';

$videoserv = 'avregd';
$local_player_name = 'avreg-mon';
$remote_player_name = 'build layout';

$left_font_family = 'sans-serif';
$right_font_family = 'sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
// shortcuts for Byte, Kilo, Mega, Tera, Peta, Exa
$byteUnits = array('Байт', 'КБ', 'МБ', 'ГБ');

$strBrowser = 'веб-браузер';

$upload_status = array(
    UPLOAD_ERR_OK => 'ошибок не возникало, файл был успешно загружен на сервер',
    UPLOAD_ERR_INI_SIZE => 'размер принятого файла превысил максимально допустимый размер,' .
    'который задан директивой  upload_max_filesize конфигурационного файла php.ini',
    UPLOAD_ERR_FORM_SIZE => 'размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме',
    UPLOAD_ERR_PARTIAL => 'загружаемый файл был получен только частично',
    UPLOAD_ERR_NO_FILE => 'файл не был загружен'
);

$datefmt = '%B %d %Y г., %H:%M';
$fmtAccessDenied = 'Пользователю &#171;%s@%s&#187 доступ запрещён.';
$strAdvice = 'Совет';
$strAdvices = 'Советы';
$fmtTryOnceMore = 'Для повторной попытки входа, перезапустите браузер (закройте все окна) и попробуйте ещё раз<br />
или же обратитесь к администратору сервера %s';
$fmtServerAdmin = 'Администратор сервера: %s';
$strAction = 'Действие';
$strAddUser = 'Добавить нового пользователя';
$strAddUserMessage = 'Был добавлен новый пользователь.';
$strAfterInsertNewInsert = 'Вставить новую запись';
$strAll = 'Все';
$strAny = 'Любой';
$strAnyHost = 'Любой хост';
$strAnyTable = 'Любая таблица';
$strAnyUser = 'Любой пользователь';

$grp_ar = array(
    1 => array('grname' => 'Инсталляторы', 'grdesc' => 'Полный доступ'),
    2 => array(
        'grname' => 'Администраторы',
        'grdesc' => 'Почти полный доступ за исключением добавления/удаления камер и изменения большинства' .
        ' специфических настроек камер.'
    ),
    3 => array(
        'grname' => 'Операторы архива',
        'grdesc' => 'Mогут просматривать архив и наблюдать за камерами в реальном времени'
    ),
    4 => array(
        'grname' => 'Операторы наблюдения',
        'grdesc' => 'Mогут наблюдать за камерами в реальном времени и управлять ими (вкл./выкл. режима принудительной' .
        ' записи, PTZ).'
    ),
    5 => array('grname' => 'Только просмотр', 'grdesc' => 'Mогут только наблюдать за камерами в реальном времени')
);

$par_filter_ar = array(0 => 'только основные', 1 => 'все');

$strBack = 'Назад';
$strBackIn = 'назад в';
$strBinary = ' Двоичный ';
$strBookmarkLabel = 'Метка';
$strBookmarkView = 'Только просмотр';
$strBrowse = 'Обзор';
$strBzip = 'архивировать в bzip';

$strChange = 'Изменить';
$strChangePassword = 'Изменить пароль';
$strCheckAll = 'Отметить все';
$strComments = 'Комментарии';
$strConfirm = 'Вы действительно хотите сделать это?';
$strCreate = 'Создать';
$strClose = 'Закрыть';

$strData = 'Данные';
$strDefault = 'по умолчанию';
$strDelete = 'удалить';
$strDisabled = 'недоступно';
$strDisclose = 'Раскрыть';
$strDisplay = 'показать';
$strDisplayed = 'показывать';
$strDocu = 'документация';
$strDoYouReally = 'вы действительно желаете ';
$strDynamic = 'динамический';
$strBackOnline = "Вернуться к просмотру";
$strEdit = 'правка';
$strEditPrivileges = 'редактирование привилегий';
$strEffective = 'эффективность';
$strEmpty = 'очистить';
$strEnabled = 'доступно';
$strEnd = 'конец';
$strError = 'ошибка';
$strExport = 'экспорт';
$strExtra = 'дополнительно';

$strFormat = 'Формат';
$strFormEmpty = 'Требуется значение для формы!';
$strFunction = 'Функция';

$strGenBy = 'Созданный';
$strGenTime = 'Время создания';
$strGo = 'Пошел';
$strGrants = 'Права';
$strGzip = 'архивировать в gzip';

$strHasBeenAltered = 'была изменена.';
$strHasBeenCreated = 'была создана.';
$strHome = 'К началу';
$strHost = 'Хост';
$strHostEmpty = 'Пустое имя хоста!';

//$strScale = 'Масштаб';
$strScale = array(
    'scale' => 'Масштаб',
    'zoom_in' => 'Увеличить',
    'zoom_out' => 'Уменьшить',
    'sorting' => 'Сортировка',
    'by_width' => 'по ширине',
    'by_height' => 'по высоте'
);

$strIdxFulltext = 'ПолнТекст';
$strIgnore = 'Игнорировать';
$strInsert = 'Вставить';
$strInsertAsNewRow = 'Вставить новый ряд';
$strInsertedRows = 'Добавленные ряды:';
$strInsertNewRow = 'Вставить новый ряд';
$strInstructions = 'Инструкции';
$strInUse = 'используется';
$strInvalidFormParams = 'Ошибка в параметрах.<br />Проверьте все ли значения заполнены правильно.';

$strKeepPass = 'Не менять пароль';
$strKeyname = 'Имя ключа';
$strKill = 'Убить';

$strLength = 'Длина';
$strLengthSet = 'Длины/Значения*';
$strLimitNumRows = 'записей на страницу';
$strLines = 'Линии';
$strLinesTerminatedBy = 'Строки разделены';
$strLinkNotFound = 'Связь не найдена';
$strLinksTo = 'Связь с';
$strLogin = 'Вход в систему';
$strLoginName = 'Логин';
$strGuestMode = 'Гость';
$strPDAversion = 'PDA';
$strLogout = 'Выйти из системы';
$strLogPassword = 'Пароль:';
$strLogUsername = 'Пользователь:';

$strModifications = 'Модификации были сохранены';
$strModify = 'Изменить';

$strNext = 'Далее';
$strNo = 'Нет';
$strNoDatabases = 'БД отсутствуют';
$strNoDescription = 'нет описания';
$strNoDropDatabases = 'Команда &#171;Удалить БД&#187; отключена.';
$strNoIndex = 'Индекс не определен!';
$strNoModification = 'Нет изменений';
$strNone = 'Нет';
$strNoPassword = 'Без пароля';
$strNoPrivileges = 'Без привилегий';
$strNoRights = 'Вы не имеете достаточно прав для этого!';
$strNotNumber = 'Это не число!';
$strNotOK = 'Не готово';
$strNoUsersFound = 'Не найден пользователь.';
$strNull = 'Ноль';

$strOK = 'Готово';
$strOperations = 'Операции';
$strOptionally = 'По выбору';
$strOptions = 'Опции';
$strOr = 'Или';
$strOpenInBlank = 'Открыть в новом окне';

$strParams = 'параметры';
$strPassword = 'Пароль';
$strPasswordEmpty = 'Пустой пароль!';
$strPasswordNotSame = 'Пароли не одинаковы!';
$strPdfDbSchema = 'Структура базы &#171;%s&#187; - страница %s';
$strPos1 = 'Начало';
$strPrevious = & $strBack;
$strPrintView = 'Версия для печати';
$strPrivileges = 'Привилегии';
$strProperties = 'Свойства';

$strQBEDel = 'Удалить';
$strQBEIns = 'Вставить';

$strRecords = 'Записи';
$strReferentialIntegrity = 'Проверить целостность данных:';
$strRememberReload = 'Не забудьте перезагрузить сервер.';
$strRenameTable = 'Переименовать таблицу в';
$strReplace = 'Заместить';
$strReset = 'cбросить';
$strReType = 'Подтверждение';
$strRevoke = 'отменить';
$strRunQuery = 'Выполнить Запрос';
$strRunned = 'работает';
$strStopped = 'остановлен';

$strSave = 'Сохранить (нажать один раз)';
$strSearch = 'Искать';
$strSearchType = 'Искать:';
$strSelect = 'Выбрать';
$strSelectADb = 'Выберите БД';
$strSelectAll = 'Отметить все';
$strSelectFields = 'Выбрать поля (минимум одно):';
$strSelectNumRows = 'по запросу';
$strSelectTables = 'Выберите таблицу(ы)';
$strSend = 'послать';
$strServerChoice = 'Выбор сервера';
$strServerVersion = 'Версия сервера';
$strShow = & $strDisplay;
$strSize = 'Размер';
$strSort = 'Отсортировать';
$strSpaceUsage = 'Используемое пространство';
$strSplitWordsWithSpace = 'Слова, разделенные пробелом (&#171; &#187;).';
$strStructure = 'Структура';
$strSubmit = 'Выполнить';
$strSum = 'Всего';

$strTable = 'таблица ';
$strTotal = 'всего';
$strType = 'Тип';

$strUnique = 'Уникальное';
$strUsage = 'Использование';
$strUser = 'Пользователь';
$strUserEmpty = 'Пустое имя пользователя!';
$strUserName = 'Имя пользователя';
$strUsers = 'Пользователи';

$strValue = 'Значение';

$strWithChecked = 'С отмеченными:';
$strWait = 'Ждите';

$strYes = 'Да';
$strYou = 'Вы';

$strZip = 'архивировать в zip';
$srtUndef = 'не определено';

$strAspectRatio = 'Соотношение сторон';
$sUnavailableReason = 'почему недоступно';

/* ************************************************************************* */

$docs_prefix = 'http://www.linuxdvr.ru/rus/docs/';

$access_denided = 'Доступ на эту страницу закрыт для пользователей группы &#171;%s&#187;.';

$DevelopersSite = 'Сайт разработчика';
$DownloadUrl = 'Проверить наличие обновлений/новых версий';
$DocUrl = 'Документация';
$ForumUrl = 'Форум службы поддержки';
$SubscribeUrl = 'Майл-лист: баги, новости, обсуждение';

$MainPage = 'В начало';

$PrName = 'Видеосервер';
$VidServ = 'Видеосервер';
$DbConf = 'Работа с видеосерверами,<br>' .
    'использующими единую конфигурационную базу данных,<br>' .
    'физически находящуюся на хосте &#171;%s&#187; [%s].';
$fmtVidServ = 'Видеосервер &#171;' . $conf['server-name'] . '&#187; на %s';
$strInvalidParams = 'Ошибка в параметрах.';

$a_adminv = 'Настройки и управление';
$a_webcam = 'Наблюдение в реальном времени';
$a_archive = 'Архив&nbsp;::&nbsp;поиск';
$a_archive_playlist = 'Архив&nbsp;::&nbsp;плейлист';

$a_archive_gallery = 'Архив&nbsp;::&nbsp;галерея';

/* _INDEX START ******************************/
$r_menu = 'Предложены следующие пункты меню (см. слева):';
/******************************** _INDEX END */

$left_logo = 'Админ';
$left_logo_desc = 'Переход к группе страниц Управление и Статистика.';

$left_status = 'Состояние';

$left_control = 'Управление';
$left_control_desc = 'Управление основной программой &#171;' . $videoserv . '&#187; и контроль её состояния.' .
    '<br><br>Команды' .
    '<ul><li>запуск - start;</li>' .
    '<li>перезапуск - restart;</li>' .
    '<li>перечитывать новые настройки - reload;</li>' .
    '<li>останов - stop</li></ul>';
$r_conrol_state = 'Текущее состояние демона(ов) &#171;' . $videoserv . '&#187; на &#171;' . $named . '&#187;';
$left_control_title = 'запуск/останов демона &#171;' . $videoserv . '&#187;';

$left_statistics = 'Статистика';
$left_statistics_title = 'cpu%, диск, память';
$left_statistics_desc = 'Просмотр: <ul><li>загруженности процессора,</li> <li>использования памяти,</li>
<li>заполнения жесткого диска.</li></ul>';

$left_utils = 'Утилиты';
$left_utils_desc = 'Полезные утилиты - ещё не предумано какие';

$tune_logo = 'Настройки';
$tune_title = 'камеры, пользователи, ...';
$tune_logo_desc = 'Переход к группе страниц для настройки параметров видеорегистратора.';

$left_system = 'Система';
$left_system_desc = 'Настройка системных модулей программного обеспечения, таких как дата, почта...';

$left_archive = 'Архив';
$left_archive_desc = 'Настройка параметров очистки кольцевого видеоархива (удаление &#171;старых&#187; видеозаписей).';

$left_users = 'Пользователи';
$left_users_desc = 'Определение пользователей для работы с системой. Изменение своего пароля.';

$left_tune = 'Видеокамеры';
$left_tune_desc = 'Настройка параметров видеокамер и других основных параметров системы.';

$left_layouts = 'Раскладки';
$left_layouts_local_title = 'для локального просмотрщика ' . $local_player_name;
$left_layouts_desc = 'Планы c расположениями окон камер (раскладками) для локального (на сервере) наблюдения в
 реальном времени просмотрщиком &#171;' . $local_player_name . '&#187;';

$left_indextune = 'Настройки';

$left_offline = 'Архив';

$left_key = 'Ключ защиты';
$license = 'Лицензия';
$left_key_desc = 'Ключ защиты определяет разрешённые (оплаченные) возможности видеорегистратора.';

$left_bug = 'Сообщить об ошибке';
$left_bug_desc = 'Сообщить разработчику об ошибке.';

$left_servers = 'Сервера';

$strDescription = 'Описание';
$strUpdateControl = 'Изменено';

$l_cam_defaults = 'параметры <font color="red"><strong>для всех</strong></font>';

$r_cam_defs2 = 'Общие настройки<br />для ВСЕХ видеокамер';
$r_cam_defs3 = 'ПО УМОЛЧАНИЮ ДЛЯ ВСЕХ';
$l_cam_addnew = 'добавить новую';
$l_cam_list = 'список камер';

$notVidDevs = 'Нет устройств видеозахвата.';
$notV4loop = 'Нет загружен модуль v4loop.';
$strNotTextLeft = 'Не определено, см.  параметр &#171;text_left&#187;';
$unknownCheckParams = 'Ошибка: неизвестный параметр &#171;%s&#187;.<br>Обратитесь к инсталлятору системы.';

$strCam = 'Камера';
$strTime = 'Время';

$strYear = 'год';
$strMonth = 'месяц';
$strDay = 'день';
$strDayOfWeek = 'д.нед.';
$strHour = 'час';
$strMinute = 'мин.';
$charNull = '-';

$fmtNext = 'след. %d';
$fmtLast = 'пред. %d';
$strGeo = 'Разрешение';

$strEmptied = 'не задано';

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
$r_mon_list = 'Список раскладок, определенных администратором.';
$client_mon_list = 'Список раскладок, определенных польльзователем.';
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

$r_moncam_list = ' Видеокамеры,  разрешённые <span class="HiLite">для локального просмотра</span> в программе &#171;' .
    $local_player_name . '&#187;.';
$web_r_moncam_list = ' Видеокамеры, доступные для просмотра в браузере.';

$strNotViewCams = 'Нет ни одной камеры, правильно сконфигурированной для локального (на сервере)  ' .
    'просмотра  в программе &#171;' . $local_player_name . '&#187;<br><br>' .
    'Если локальный просмотр не нужен, то лучше так и оставить для экономии ресурсов компьютера.<br/>' .
    'Если нужен - смотрите настройки камер, разделы &#171;Наблюдение&#187; -&#062; &#171;локальное&#187;';
$MonCamListShow = array(
    'не показывать',
    'правильно сконфигурированные для локального просмотра',
    'все'
);

$strViewCamsChange = 'Кто-то изменил расладку видеокамер для локального (с этого компьютера)' .
    ' просмотра программой &#171;' . $local_player_name . '&#187;. <br>' .
    'Обратитесь к администратору или инсталлятору системы.';
$strNotChoiceCam = 'Вы должны выбрать хотя бы одну камеру, иначе зачем определять пустую раскладку.';

$fmtLayoutDelConfirm = 'Вы уверены что хотите удалить раскладку #%d [%s]';
$fmtDeleteMonConfirm = $fmtLayoutDelConfirm . ' для %s монитора?' . "\n";
$strDeleteMon = 'Раскладка #%d [%s] для %s монитора удалена из конфигурации.' . "\n";

$strUpdateHint = 'Подсказка:<ul>' .
    '<li>в локальной программе наблюдения &#171;' . $local_player_name . '&#187; переключение между определенными' .
    ' раскладками осуществляется простым нажатием клавиш 0-9, по номерам раскладок;</li>' .
    '<li>если вы хотите изменить тип некоторой раскладки (квадратор, мультиэкран и т.п.) - удалите её и создайте ' .
    'под старым номером  но с новыми свойствами.</li>' .
    '<li>настройки расладок предназначены только для программы &#171;' . $local_player_name .
    '&#187; и поэтому, после их изменения, демон &#171;' . $videoserv . '&#187;перегружать не нужно.</li></ul>';
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

/* USERS */
$l_user_list = 'список пользователей';
$l_user_addnew = 'добавить нового';
$l_user_passwd = 'сменить свой пароль';

$r_cam_tune = 'Параметры видеокамеры <font color="Red">N %d %s</font> на сервере &#171;
<font color="Red">%s</font>&#187;.';
$strParInvalid = 'Ошибка: &#171;%s&#187; - недопустимое значение параметра &#171;%s&#187;.';

$r_cam_param_cat = 'Разделы параметров';
$strAllCat = 'все разделы';
$r_help_page = 'Страница помощи';
$strCatHelp = 'справка по разделу';

$strInfo1 = 'Чтобы изменить или удалить период нажмите на ссылку поля в столбце - "НАЗВАНИЕ"';
$strNotNull = 'должно быть заполнено';
$strOrderRange = 'должно быть в диапазоне от 0 до 1024';
$strDayRange = 'должно быть в диапазоне от 1 до 31';
$strHourRange = 'должно быть в диапазоне от 0 до 23';
$strMinuteRange = 'должно быть в диапазоне от 0 до 59';

$not_defined = 'не определено, см. описание параметра - &#171;По умолчанию&#187;';
$eval_with_def = 'установлено для всех видеокамер в &#171;параметрах для всех&#187;';
$not_eval_with_def = 'конкретно определено для этой видеокамеры';
$strReloadDesc = 'если изменён, то нужно перечитать конфигурацию ( <a href="' . $conf['prefix'] .
    '/admin/control.php?cmd=Reload#038;from=tune" title="если закончили менять параметры - подать команду ' .
    $videoserv . '">Reload</a> )';
$strRestartDesc = 'если изменён, то нужно перезапустить ' . $videoserv . ' ( <a href="' . $conf['prefix'] .
    '/admin/control.php?cmd=Restart#038;from=tune" title="если закончили менять параметры - подать команду ' .
    $videoserv . '">Restart</a> )';
$strName = 'название';
$strOrder = 'номер';
$sInformation = 'информация';
$_b_date = 'дата начала';
$_e_date = 'дата окончания';
$_b_time = 'время начала';
$_e_time = 'время окончания';
$strComment = 'комментарий';
$strWeekdays = 'дни недели';
$strFilter = 'фильтры';
$strAdmin = 'кто менял';
$strChTime = 'когда менял';
$strSubscribe = 'Подписаться';

$strLang = 'Язык';

$strDeleteAll = 'Удалить все';
$strFileUpload = 'Загрузить файл или архив файлов (.ZIP, .TAR.GZ, .TGZ, .TAR.BZ2)';
$strUpload = 'Загрузить';
$strUploadBig = 'размер файла больше 2Мб.';
$strUploadPart = 'файл загружен не полностью.';
$strUploadNotFile = 'не задано или неправильное имя файла.';
$strNotUnzipAndSoxFmt = 'Архив "%s" не имеет звуковых файлов или повреждён.';
$strNotSoundFileFmt = 'Файл "%s" не является звуковым файлом.';

$env_id_ar = array(
    23 => 'видео-фильм',
    32 => 'аудио-файл',
    16 => 'спанпшоты_1(jpg)',
    13 => 'начало движения',
    14 => 'окончание движения',
    5 => 'вкл./выкл. записи',
    3 => 'запуск/останов захвата',
    1 => 'запуск/останов сервера',
    4 => 'подкл./откл. клиентов',
    17 => 'спанпшоты_2(jpg)',
    15 => 'спапшоты_paranoid',
    /*  12=>'аудио+видео фильм', */

);

$strSetComment = 'Кадр или файл помечен. Чтобы прямо сейчас увидеть изменения в таблице списка кадров справа -
 повторите запрос к базе данных (кнопка "Показать")';
$strUnSetComment = 'Пометка удалена. Чтобы прямо сейчас увидеть изменения в таблице кадров справа - повторите запрос
 к базе данных (кнопка "Показать")';

$pofig = 'не важно';
$minute_array = array('00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55', '59');
$hour_array = array(
    '00',
    '01',
    '02',
    '03',
    '04',
    '05',
    '06',
    '07',
    '08',
    '09',
    '10',
    '11',
    '12',
    '13',
    '14',
    '15',
    '16',
    '17',
    '18',
    '19',
    '20',
    '21',
    '22',
    '23'
);
$day_array = array(
    1,
    2,
    3,
    4,
    5,
    6,
    7,
    8,
    9,
    10,
    11,
    12,
    13,
    14,
    15,
    16,
    17,
    18,
    19,
    20,
    21,
    22,
    23,
    24,
    25,
    26,
    27,
    28,
    29,
    30,
    31
);
$day_of_week = array('Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс');
$month_array = array('Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек');
$year_array = array('07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20');
$flags = array('Выкл.', 'Вкл.');
$scale_array = array(65, 75, 85, 95, 100);
// $state3_array = array($pofig,'Вкл.');
$play_tio_ar = array('0.05 сек', '0.1 сек', '0.2 сек', '0.4 сек', '0.6 сек', '0.8 сек', '1.2 сек', '1.6 сек');
$row_max_ar = array(17, 50, 100, 300);
$strFrom = 'с';
$strTo = 'по';
$strDescOrder = 'обратная сортировка';

$strCam_nr_Range = 'Недопустимый порядковый номер видеокамеры';
$strTune = 'настроить';

$strWcListShow = 'Показать список камер: ';

$DEV_FIRMS = '&copy; 2007-2013 ООО &#171;Сетевые информационные системы&#187;';
