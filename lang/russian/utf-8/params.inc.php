<?php

$F_IN_DEF = 0x0001;
$F_IN_CAM = 0x0002;
$F_RELOADED = 0x0004;

$F_BASEPAR = 0x0100;

$vid_standarts = array('PAL (цв.в/к)', 'NTSC (цв.в/к)', 'SECAM (не для в/к)', 'PAL NC (ч/б в/к)');
$video_sources = array('video4linux', 'http', 'rtsp');
$audio_sources = array('alsa', 'http', 'rtsp');

$rtsp_transport = array('udp', 'tcp', 'udp_multicast', 'http');
$use_wallclock_as_ts_a = array('Выкл.', 'Всегда', 'Авто', 'Авто2', 'Авто3');
$rtsp_flags_group = array(
    'gen_missing_pts' => 'gen_missing_pts',
);

$str_audio_force_fmt = array(
    'pcm_mulaw',
    'pcm_alaw',
    'g726_32k',
    'g726_24k',
    'pcm_s8',
    'pcm_u8',
);

$syslog_levels = array(
    'EMERG', /* system is unusable */
    'ALERT', /* action must be taken immediately */
    'CRIT', /* critical conditions */
    'ERR', /* error conditions */
    'WARNING', /* warning conditions */
    /*
    'NOTICE'  normal but significant condition,
    'INFO'  informational,
    'DEBUG'  debug-level messages,
     */
);

// $flip_type = array('зеркально', 'вращение 180');

$text_font_sizes = array('маленький', 'большой');

$v4l_int_cntrl = '<p>Допустимые значения: 0 или &#171;пусто&#187; - не&nbsp;устанавливать или не&nbsp;подстраивать
 значение этого параметра; или установить значение  [1(мин.)..5(средн.)..9(макс.)].</p>По умолчанию:
 &#171;<b>пусто</b>&#187; (не&nbsp;подстраивать).';

$recording_mode = array('Без записи', 'Выборочно', 'Всё подряд');
$strOnlySelDet = 'Доступно <i>только при выборочном режиме записи</i> (<i>recording</i>=&laquo;Выборочно&raquo;) и
 включенном детекторе движения (<i>motion_detector</i>=&laquo;Вкл.&raquo;).';

$_rate_lim_info = 'Ограничить этим значением скорость отдаваемого видеопотока, в кадрах в секунду.<br><br>Допустимые
 значения: [1..60]; по-умолчанию: <b>60 - не ограничивать</b>.';

$decode_video_a = array(
    'Выкл.',
    'Декодировать',
    'только I-фрейм',
    'каждый 2-й I-фрейм',
    'каждый 3-й I-фрейм',
    'каждый 4-й I-фрейм',
    'каждый 5-й I-фрейм',
);

$recording_format = array('mp4', 'flv', 'mov', 'matroska', 'avi', 'wav'); // 'webm', 'ogg', 'amr'
$rec_vcodec = array('mpeg4', 'flv1', 'mjpeg'); //, 'vp8'); // 'libtheora'
$rec_acodec = array('aac'); // 'vorbis'); //  'libgsm', 'libopencore_amrnb'

$rec_avcodec_fmt = '<b>Кодек (тип/стандарт) сжатия %sпотока</b>, используемый при записи на жесткий диск.
<br><br>Если вы планируете явно выбрать кодек из списка, то знайте:
<ul>
<li>если ваш вариант окажется несовместимым с медиаконтейнером {rec_format} - запись на диск станет невозможна;</li>
<li>если вы планируете перекодирование в другой тип сжатия, отличный от кодека сжатия исходного потока - нужно включить
 декодирование потока {decode_%s} и проконтролировать значительно возросшую нагрузку на CPU (из-за декодирования с
 последующим кодированием) чтобы она не превышала 80%%.</li>
</ul>
По умолчанию: не установлено - &#171;<b>авто</b>&#187;, программа попытается самостоятельно
подобрать кодек совместимый c выбранным медиаконтейнером {rec_format} и с учётом кодека сжатия исходного потока.';

$file_limits_and_detector = 'При включенном детекторе движения, событие &#171;окончание сессии движения&#187;
 (см. {motion_session_end} в настройках детектора) закроет файл независимо от любых установленных пределов на
 размер и продолжительность.';

$source_absent_warn = 'Прим.: запись начнётся только когда будут захвачены все медиа-потоки, определённые в
 {video_src}/{audio_src}. Если какой-любо из определённых источников не может быть получен - запись не начнётся.';

$event_groups = array(
    'mediafiles' => 'медиафайлы',
    'snapshots' => 'картинки',
    'capture' => 'захват',
    'motion' => 'движение',
    'quality' => 'качество',
    'recording' => 'запись',
    'clients' => 'клиенты'
);
$ROOT_RES_DEF = 'По умолчанию: <b>&quot;/&quot;</b> - корневой ресурс.';

// $PAR_CATEGORY, $COMMENT, $VIEW_ON_DEF, $VIEW_ON_CAM, $MASTER_STATUS, $HELP_PAGE
$PAR_GROUPS = array(
    array(
        'id' => '1',
        'name' => 'Главное',
        'desc' => 'Вкл./Выкл. захвата и отладки',
        'flags' => $F_BASEPAR | $F_IN_CAM,
        'mstatus' => 2,
        'help_page' => null
    ),
    array(
        'id' => '3',
        'name' => 'Захват',
        'desc' => 'Выбор типа устройства и настройка параметров аудио/видео захвата',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => $conf['docs-prefix'] . 'apps-quick-conf.html'
    ),
    array(
        'id' => '3.1',
        'name' => 'по сети',
        'desc' => 'с IP-камер и IP-видеосерверов',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => $conf['docs-prefix'] . 'apps-ipcam-capture.html'
    ),
    array(
        'id' => '3.1.2',
        'name' => 'rtsp://',
        'desc' => 'видео/аудио захват по протоколу &#171;rtsp://&#187;',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => null,
     ),
    array(
        'id' => '3.1.1',
        'name' => 'http://',
        'desc' => 'видео/аудио захват по протоколу &#171;http://&#187;',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => null
    ),
    array(
        'id' => '3.1.1.1',
        'name' => 'видео',
        'desc' => 'захват в форматах mjpeg или jpeg по &#171;http://&#187;',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => null
    ),
    array(
        'id' => '3.1.1.2',
        'name' => 'аудио',
        'desc' => 'захват в форматах pcm,adpcm,G.72x или aac (Axis) по &#171;http://&#187;',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => null
    ),
    array(
        'id' => '3.2',
        'name' => 'video4linux',
        'desc' => 'видео с PCI-плат видеозахвата и USB-камер',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => 'http://avreg.net/howto_linux-capture-cards.html'
    ),
    array(
        'id' => '3.3',
        'name' => 'alsa',
        'desc' => 'аудио с звуковых карт, USB-камер и PCI-плат видеозахвата с поддержкой аудио',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => null
    ),
    array(
        'id' => '5',
        'name' => 'Обработка',
        'desc' => 'Алгоритмы обработки аудио/видео данных',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 2,
        'help_page' => $conf['docs-prefix'] . 'apps-quick-conf.html'
    ),
    array(
        'id' => '5.1',
        'name' => 'видео',
        'desc' => 'Алгоритмы обработки видео',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 2,
        'help_page' => null
    ),
    array(
        'id' => '5.1.1',
        'name' => 'наложение текста на кадр',
        'desc' => 'Текст, &#171;врезаемый&#187; в видеокадры',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => null
    ),
    array(
        'id' => '5.1.3',
        'name' => 'детектор движения',
        'desc' => 'Настройка встроенного программного детектора движения',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => null
    ),
    array(
        'id' => '5.1.2',
        'name' => 'контроль засветки/затемнения',
        'desc' => 'Контроль средней яркости изображения (засветка, затемнение)',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => null
    ),
    /*
     array(
        'id'=>'5.2',
        'name'=>'аудио',
        'desc'=>'Различные алгоритмы обработки аудиопотока',
        'flags'=>$F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus'=> 2,
        'help_page'=> NULL
        ),

     array(
        'id'=>'5.2.1',
        'name'=>'детектор',
        'desc'=>'Детектор звука VAD (voice audio detection)',
        'flags'=>$F_IN_DEF | $F_IN_CAM,
        'mstatus'=> 2,
        'help_page'=> NULL,
        ),
     */

    array(
        'id' => '11',
        'name' => 'Запись',
        'desc' => 'Запись на жёсткие диски (HDD)',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 2,
        'help_page' => $conf['docs-prefix'] . 'filefmt.html'
    ),
    array(
        'id' => '11.1',
        'name' => 'видео',
        'desc' => 'Только видео (без аудио)',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => null
    ),
    array(
        'id' => '11.2',
        'name' => 'аудио',
        'desc' => 'Только аудио (без видео)',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => null
    ),
    /*
    array(
        'id'=>'11.3',
        'name'=>'видео + аудио',
        'desc'=>'Совместно: видео + аудио',
        'flags'=> $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus'=> 1,
        'help_page'=> NULL
        ),
     */

    array(
        'id' => '15',
        'name' => 'Наблюдение',
        'desc' => 'Наблюдение в реальном времени (ONLINE)',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 2,
        'help_page' => null
    ),
    array(
        'id' => '15.1',
        'name' => 'локальное',
        'desc' => 'Локальный просмотр на сервере с помощью программы monitor (avreg-mon)',
        'flags' => $F_BASEPAR | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => $conf['docs-prefix'] . 'work-monitor.html'
    ),
    array(
        'id' => '15.2',
        'name' => 'по сети',
        'desc' => 'Удаленный просмотр по сети (в интернет-браузере или &quot;вышестоящим&quot; видеосервером AVReg' .
            ' или другим DVR)',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => null
    ),
    array(
        'id' => '20',
        'name' => 'События',
        'desc' => 'Внешние обработчики событий',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'mstatus' => 1,
        'help_page' => 'http://avreg.net/manual_applications_avregd-event-collector.html'
    ),
);

$PAR_GROUPS_NR = count($PAR_GROUPS);

// $VAL_TYPE, $DEF_VAL,$COMMENT, $RELOADED, $VIEW_ON_DEF, $VIEW_ON_CAM, $PAR_CATEGORY, $SUBCAT_SELECTOR, $MASTER_STATUS
$PARAMS = array(

    array(
        'name' => 'work',
        'type' => $BOOL_VAL,
        'def_val' => 0,
        'desc' => 'Вкл./Выкл. <b>видеозахват</b>а с видеокамеры (читай: <b>работать с этой камерой или нет</b>).' .
        '<br><br>По умолчанию: <b>Выкл</b>.',
        'flags' => $F_BASEPAR | $F_IN_CAM,
        'cats' => '1',
        'subcats' => null,
        'mstatus' => 2,
    ),
    array(
        'name' => 'text_left',
        'type' => $STRING_VAL,
        'max_len' => 40,
        'def_val' => null,
        'desc' => 'Название камеры или зоны наблюдения.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_CAM,
        'cats' => '1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'debug',
        'type' => $BOOL_VAL,
        'def_val' => 0,
        'desc' => 'Вкл./Выкл. <b>режим отладки</b>.<br><br>Включение режима отладки ' .
        '<b>существенно замедляет работу системы</b>, так как при этом в системный журнал пишется ' .
        'много отладочных сообщений необходимых <b>для разбора нештатных ситуаций</b>.' .
        '<br><br>По умолчанию: <b>Выкл</b>.',
        'flags' => $F_RELOADED | $F_IN_CAM,
        'cats' => '1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'video_src',
        'type' => $CHECK_VAL,
        'def_val' => null,
        'desc' => "<p><b>Источник видео</b> (способ видеозахвата).</p>
      <p>$source_absent_warn</p>
      По умолчанию: &quot;пусто&quot; - не захватывать видео.",
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3',
        'subcats' => '3.1;3.2',
        'mstatus' => 1,
    ),
    array(
        'name' => 'audio_src',
        'type' => $CHECK_VAL,
        'def_val' => null,
        'desc' => "<p><b>Источник аудио</b> (способ аудиозахвата).</p>
      <p>$source_absent_warn</p>
      По умолчанию: &quot;пусто&quot; - не захватывать аудио.",
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3',
        'subcats' => '3.1;3.2',
        'mstatus' => 1,
    ),
    array(
        'name' => 'decode_video',
        'type' => $CHECK_VAL,
        'def_val' => 1,
        'desc' => '
<p><b>Декодировать входящий видеопоток</b> (<span class="warntext">не допускайте увеличения
%CPU более 80% на каждом процессорном ядре</span>).
</p>
<p>Декодирование необходимо если вы используете:</p>
<ul>
<li>обработку видео: детектор движения и/или контроль засветки/затемнения,</li>
<li>перекодирование для записи, когда кодек записи, заданный явно (см. {rec_vcodec}) или стандартный для выбранного
 формата (см. {rec_format}), не совпадает с кодеком исходного входящего видеопотока,</li>
<li>локальное наблюдение с помощью программы avreg-mon (Наблюдение -&gt; локальное),</li>
<li>наблюдение по сети со сжатием MJPEG (прим. AVReg v5.6/6 по другому не умеет), а исходный
 входящий поток не MJPEG, а h264, например.</li>
</ul>
<p>Фреймрейт (в кадрах в сек.) производных видеопотоков (см. список выше) будет
не выше фреймрейта декодированного потока. Учитывайте это особенно если для снижения
нагрузки вы выбираете декодирование только ключевых кадров (I-frame).
</p>
<p>При использовании мультипоточных ip-камер и нехватки ресурсов сервера планируйте
 <a href="http://avreg.net/manual_applications_multi-stream.html" target="_blank">эффективные решения</a>.<p>
По умолчанию: &quot;пусто&quot; - декодировать.
',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3',
        'subcats' => '3.1;3.2',
        'mstatus' => 1,
    ),
    array(
        'name' => 'decode_audio',
        'type' => $BOOL_VAL,
        'def_val' => true,
        'desc' => '<b>Декодировать входящий аудиоопоток</b>.
      <p>Декодирование необходимо если необходимо перекодирование для записи, когда кодек записи, заданный явно ' .
        '(см. {rec_acodec}) или стандартный для выбранного формата (см. {rec_format}), не совпадает с кодеком' .
        ' исходного входящего аудиопотока.</p> По умолчанию: &quot;пусто&quot; - декодировать.',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3',
        'subcats' => '3.1;3.2',
        'mstatus' => 1,
    ),
    array(
        'name' => 'geometry',
        'type' => $STRING_VAL,
        'valid_preg' => '/\A\d+\s*[x:\/]\s*\d+\Z/',
        'def_val' => '640x480',
        'desc' => '<b>Разрешение видеозахвата</b> в пикселях (<b>ширина х высота</b>).
      <ul>
      <li>Для <b>сетевых ip-камер</b> и ip-видеосерверов установите действительное разрешение видеопотока, которые
      определены в настройках самой камеры или заданы в параметрах запроса ({rtsp_play}/{V.http_get}) к камере.</li>
      <li>Для <b>video4linux</b> устройств установите одно из поддерживаемых драйвером устройства значение. Список
      поддерживаемых разрешений можно посмотреть в выводе команды
      <span class="cmd">v4l2-ctl --list-formats-ext -d /dev/videoX</span>, где v4l2-ctl - утилита из пакета v4l-utils,
      а X - номер устройства. Если драйвер не имеет списка поддерживаемых разрешений, то выберете одно из стандартных
      разрешений: 384x288<sup>*</sup>, 480x360<sup>*</sup>, 560x420<sup>*</sup>, 640x480<sup>*</sup>,
      720x540<sup>*</sup>, 720x576 (макс.&nbsp;saa713x), 768x576<sup>*</sup> (макс.&nbsp;bt878a).
      Прим:. для ВСЕХ каналов ОДНОГО устройства видеозахвата (одного {v4l_dev}) должно быть ОДНО значение</li>
      </ul>По умолчанию: <b>640x480</b>.',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3',
        'subcats' => '3.1;3.2',
        'mstatus' => 1,
    ),
    /* настройки сетевых камер */
    array(
        'name' => 'InetCam_IP',
        'type' => $STRING_VAL,
        'def_val' => null,
        'desc' => '<b>IP-адрес</b> сетевой видеокамеры или видеосерверов (например, Axis, Planet, D-Link, Panasonic,
        Beward, Aviosys и т.п. ).<br><br>По умолчанию: <b>не установлено</b>.',
        'flags' => $F_BASEPAR | $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'InetCam_USER',
        'type' => $STRING_VAL,
        'def_val' => null,
        'desc' => '<b>Имя пользователя</b> для доступа к сетевой видеокамере (если необходимо). <br>По умолчанию:
            <b>не установлено</b>.',
        'flags' => $F_BASEPAR | $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'InetCam_PASSWD',
        'type' => $PASSWORD_VAL,
        'def_val' => null,
        'desc' => '<b>Пароль</b> пользователя для доступа к сетевой видеокамере (если необходимо).<br>По умолчанию:
            <b>не установлено</b>.',
        'flags' => $F_BASEPAR | $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'UserAgent',
        'type' => $STRING200_VAL,
        'def_val' => null,
        'desc' => 'Заголовок <b>User-Agent</b> запроса HTTP/RTSP. По умолчанию: &quot;<b>' . $conf['daemon-name']
            . '/$ver</b>&quot;.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'Referer',
        'type' => $STRING200_VAL,
        'def_val' => null,
        'desc' => 'Заголовок <b>Referer</b> запроса HTTP/RTSP. По умолчанию: <b>не передается</b>.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'nc_conn_tries_period',
        'type' => $INT_VAL,
        'def_val' => 5,
        'desc' => '<b>Интервал (в сек.) между попытками подключения</b>. Прим: первый &#034;переконнект&#034; после
            разрыва потока - в половину меньше.<br />Диапазон: [2..60], по умолчанию: &quot;<b>5 сек.</b>&quot;.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'nc_wait_conn_timeout',
        'type' => $INT_VAL,
        'def_val' => 7,
        'desc' => '<b>Таймаут (в сек.) ожидания установления соединения</b>.<br />Диапазон: [3..60], по умолчанию:
            &quot;<b>7 сек.</b>&quot;.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'nc_read_timeout',
        'type' => $INT_VAL,
        'def_val' => 5,
        'desc' => '<b>Таймаут (в сек.) ожидания ожидания данных из  соединения</b>.<br />Диапазон: [2..30], по
            умолчанию: &quot;<b>5 сек.</b>&quot;.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'InetCam_http_port',
        'type' => $INT_VAL,
        'def_val' => 80,
        'desc' => '<b>Номер порта TCP/IP</b> на котором сетевая камера или видеосервер слушают запросы HTTP.<br />
            По умолчанию: &quot;<b>80</b>&quot;.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'http/1.0',
        'type' => $BOOL_VAL,
        'def_val' => false,
        'desc' => '<b>Использовать устаревшую версию 1.0 протокола HTTP для исходящих соединений.</b> В частности,
            может быть полезно при работе с ip-камерами с некорректной реализацией протокола HTTP в режиме захвата
            одиночных кадров (snapshot mode).<br />По умолчанию: &quot;<b>Выкл.</b>&quot; - используется версия
            http/1.1 c поддержкой persistent connection.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'V.http_get',
        'type' => $STRING200_VAL,
        'def_val' => '/',
        'desc' => '<b>Строка HTTP-запроса &quot;GET&quot;</b> (завершающая часть http URL-a) на получение потокового
            видео MJPEG (live) или одиночного кадра JPEG (snapshot).<br><br>Например для Axis:<br />
         mjpg: <b>/axis-cgi/mjpg/video.cgi?resolution=640x480&amp;color=1&amp;fps=5</b>
         <br />
         jpeg: <b>/axis-cgi/jpg/image.cgi?resolution=320x240&amp;camera=1&amp;compression=25</b>
         <br><br>для удалённого AVReg:<br />
         mjpg: <b>/avreg-cgi/mjpg/video.cgi?camera=5&fps=5</b>
         <br />
         jpeg: <b>/avreg-cgi/jpg/image.cgi?camera=1</b>
         <br /><br />Не знаете запрос для вашей камеры - читайте <a href="' . $conf['docs-prefix'] .
            'apps-ipcam-capture.html" target="_blank">здесь &gt;&gt;</a> или обратитесь к нам.
         <br /><br />' . $ROOT_RES_DEF,
        'flags' => $F_BASEPAR | $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.1.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'fps',
        'type' => $STRING_VAL,
        'valid_preg' => '/\A\d+(\s*[x:\/]\s*\d+)*\Z/',
        'def_val' => null,
        'desc' => '<b>Желаемая (не фактическая!) скорость видеозахвата в кадрах в секунду при захвате одиночными
         кадрами (снапшотами)</b>.
         <br><br>Допустимые значения:
         <ul>
         <li><b>&laquo;пусто&raquo;</b> или 0 - не ограничивать.;</li>
         <li><b>[1-60] или дробь период_в_сек/кадров_за_период</b>  - попытаться ограничить скорость видеозахвата,
         если на запросы по адресу {V.http_get} камера отдаёт одиночные кадры JPEG (спапшоты).</li>
         </ul>
         Примечание:
         <ul>
         <li>потоковым способом (<b>motion jpeg</b>) - настройка скорости в кадрах в секунду (framerate) регулируется
         (ограничивается) <b>только настройками самих ip-камер/видеосерверов</b>, а в продвинутых моделях может
         указываться в параметрах запроса к устройствам (например,
         <nobr>V.http_get=/axis-cgi/mjpg/video.cgi?<b>fps=15</b></nobr>);</li>
         </ul>
         По умолчанию: &laquo;<b>пусто</b>&raquo; - <b>не ограничивать</b>.',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM | $F_RELOADED,
        'cats' => '3.1.1.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'Aviosys9100_chan',
        'type' => $INT_VAL,
        'def_val' => null,
        'desc' => '<b>Только для шлюзов Aviosys 9100 (B/RK/A) в режиме roundrobin</b>.<br><br><b>Номер камеры/канала
            [0,1,2,3]</b> на шлюзе при захвате в режиме roundrobin.<br><br>По умолчанию: <b>не установлено</b> -
            не Aviosys 9100 в roundrobin.',
        'flags' => $F_IN_CAM,
        'cats' => '3.1.1.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'nc_max_http_stream_errors',
        'type' => $INT_VAL,
        'def_val' => 5,
        'desc' => '<b>Количество логических ошибок в протоколе приводящее к принудительному разрыву соединения</b>.
            В некоторых случаях, например: на оч. медленных каналах или проблемных камерах, увеличения значения этого
            параметра позволяет всё же обеспечить непрерывный видеозахват.<br />Диапазон: [2..10],
            по умолчанию: &quot;<b>5</b>&quot;.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'A.http_get',
        'type' => $STRING200_VAL,
        'def_val' => '/',
        'desc' => '<b>Строка HTTP-запроса &quot;GET&quot;</b>  (завершающая часть http URL-a) на получение
            аудио-потока в форматах pcm G.711 64kbit/s, adpcm G.726 32kbit/s и G.723 24kbit/s или
            AAC (rtp over http, Axis).<br><br>
            Например для Axis: &quot;<b>/axis-cgi/audio/receive.cgi</b>&quot;
            <br /><br />Не знаете запрос для вашей камеры - читайте <a href="' . $conf['docs-prefix'] .
            'apps-ipcam-capture.html" target="_blank">здесь &gt;&gt;</a><br /><br />' . $ROOT_RES_DEF,
        'flags' => $F_BASEPAR | $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.1.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'A.force_fmt',
        'type' => $CHECK_VAL,
        'def_val' => null,
        'desc' => '<b>Принудительно использовать этот аудио формат для входящего аудиопотока</b> с камер, которые не
            передают информацию о формате и способе кодирования аудио или передают её неправильно.<br />' .
        '<ul>' .
        '<li>pcm_mulaw - pcm mu-law 8bit 64kbit/s (audio/basic);</li>' .
        '<li>pcm_alaw - pcm a-law 8bit 64kbit/s;</li>' .
        '<li>pcm_s8 - pcm signed linear (2`s complement) 8bit 64kbit/s;</li>' .
        '<li>pcm_u8 - pcm unsigned linear 8bit 64kbit/s;</li>' .
        '<li>g726_32k - adpcm g726 4bit 32kbit/s (audio/32ADPCM);</li>' .
        '<li>g726_24k - adpcm g726 3bit 24kbit/s (audio/G723).</li>' .
        '</ul>По умолчанию: &quot;<b>не установлено</b>&quot; - формат ожидается в заголовке ответа сервера',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.1.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    /*
    array(
      'name'    => 'http_boundary',
      'type'    => $STRING_VAL,
      'def_val' => NULL,
      'desc'    => 'Строка <b>boundary</b> для сетевых видеокамер, имеющим отклонения при передаче
            потока multipart/mixed-replace от стандарта протокола HTTP.<br>По умолчанию: <b>не установлено</b>.',
      'reloaded'=> 1,
      'in_def'  => 1,
      'in_cam'  => 1,
      'cats'    => '3.1.1',
      'subcats' => NULL,
      'mstatus' => 1,
    ),
     */
    array(
        'name' => 'rtsp_play',
        'type' => $STRING200_VAL,
        'def_val' => '/',
        'desc' => '<b>Строка RTSP-запроса &quot;PLAY&quot;</b> (завершающая часть rtsp URL-а),
        адресующая медиа-поток камеры:<br /><br />
        Например, для камер Axis с прошивками версий от 5.00 и выше:
        <br /><b>/axis-media/media.amp?resolution=640x480&amp;videocodec=h264&amp;audio=0</b>
        <br /><br />Не знаете запрос для вашей камеры?
        <ul>
        <li> 
        <div class="href" onclick="do_onvif_uri_req(cam_tune_info);">
        Спросить у камеры если она поддерживает ONVIF &gt;&gt;
        </div>
        </li>
        <li>поищите тут <a href="http://www.soleratec.com/rtsp/"
        target="_blank">[ 1 ]</a>,  <a href="http://www.ispyconnect.com/sources.aspx" target="_blank">[ 2 ]</a>;</li>
        <li>или обратитесь к нам.</li>
        </ul>
        Для проверки можете воспользоваться плеером VLC, открыв в нём URL вида
        <nobr>rtsp://{InetCam_USER}:{InetCam_PASSWD}@{InetCam_IP}:{InetCam_rtsp_port}{rtsp_play}</nobr>
        <br /><br />' . $ROOT_RES_DEF,
        'flags' => $F_BASEPAR | $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'InetCam_rtsp_port',
        'type' => $INT_VAL,
        'def_val' => 554,
        'desc' => '<b>Номер порта TCP/IP</b> RTSP-сервера сетевой камеры или видеосервера.<br />По умолчанию:
            &quot;<b>554</b>&quot;.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'rtsp_transport',
        'type' => $CHECK_VAL,
        'def_val' => 'tcp',
        'desc' => 'Транспортный протокол нижнего уровня для сеанса rtsp: udp, tcp, udp_multicast, http.<br />
            По умолчанию: &quot;<b>tcp</b>&quot;.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'max_analize_duration',
        'type' => $INT_VAL,
        'def_val' => 3,
        'desc' => 'Макс. время в секундах на анализ потока после подключения.
        В течении этого периода определяются разрешение видео, скорость (framerate) и много др. параметров.
        <br />По умолчанию: <b>3</b> сек.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'max_delay',
        'type' => $INT_VAL,
        'def_val' => 2,
        'desc' => 'Макс. время в секундах на сортировку RTP(поверх UDP) пакетов в правильный порядок.<br />
            По умолчанию: <b>2</b> сек.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'use_wallclock_as_ts',
        'type' => $CHECK_VAL,
        'def_val' => 2,
        'desc' => 'Не использовать временные отметки RTP, формируемые RTSP-сервером (камерой).
            <br><br>
            При превышении реального текущего битрейта исходного потока,
            заданного настройками на камере (CBR кодирование)
            или формируемого текущими условиями съёмки (VBR кодирование),
            текущей скорости чтения, на которую влияют пропускная способность сетевого участка камера-сервер
            и производительность(или загрузка) сервера, очень многие камеры начинают допускать следующие ошибки:
            <ul>
            <li>существенное отставание от реального времени
            (last frame timestamp is significantly in past/future),</li>
            <li>значительные отрицательные и положительные смещения времени соседних кадров
            (invalid time offset of source stream),</li>
            </ul>
            Также могут быть и другие причины, приводящие к этим ошибкам.
            <br><br>
            Реакция программы на такие ошибки - сброс rtsp-сессии и повторное подключение через
            некий интервал (половину {nc_conn_tries_period}) или, по другому, реконнект.
            Назначение времени кадра на стороне сервера в некоторых случаях позволяет избежать реконнектов,
            однако отклонение такого &quot;назначенного &quot; времени от реального времени кадров на нагружных
            системах может составлять до нескольких сек., что, например,
            делает невозможным обеспечить точную синхронизацию аудио и видео в записи.
            <br><br>
            Допустимые значения:
            <ul>
            <li>Выкл. - использовать оригинальные временные отметки, устанавливаемые камерой,</li>
            <li>Всегда - генерировать вренные отметки кадров самостоятельно,</li>
            <li>Авто - переключаться с Выкл. на Всегда как только 3 ошибки,
            связанные с временем потока (см. список выше), возникли менее чем за 1 час.</li>
            <li>Авто2 - то же что и Авто, только допустимы 3 ошибки за 2 часа,</li>
            <li>Авто3 - то же что и Авто, только допустимы 3 ошибки за 3 часа.</li>
            </ul>
            По умолчанию: &quot;<b>Авто</b>&quot;.',
        'flags' => $F_BASEPAR | $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'rtsp_flags',
        'type' => $CHECK_VAL,
        'def_val' => null,
        'desc' => 'Дополнительные опции и хаки модуля захвата:
        <ul>
           <li><i>gen_missing_pts</i> - вычислять отсутствующие PTS, даже если это требует разбора будущих кадров.</li>
        </ul>
        По умолчанию: &quot;<b>пусто</b>&quot;.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.1.2',
        'subcats' => null,
        'mstatus' => 1,
    ),

    array(
        'name' => 'v4l_dev',
        'type' => $CHECK_VAL,
        'def_val' => null,
        'desc' => 'Спец. <b>файл video4linux устройства видеозахвата</b>.
            <p>Обычно video4linux устройство указывает на свой конкретный видеокодер BT878/SAA71xx/CX2388x, которых на
            одной плате может быть и несколько. Например, большинство 16 канальных плат с 4 видеокодерами будут
            представлены в системе как 4 отдельных устройства /dev/video[0..3]. Встречаются и исключения, например,
            плата Kodikom 4400R, которая представлена драйвером как одно 16-канальное video4linux устройство.</p>
            По умолчанию: <b>не установлено</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'input',
        'type' => $CHECK_VAL,
        'def_val' => 0,
        'desc' => '<b>Номер канала</b> (начиная с 0) video4linux устройства &#171;
            <span class="param">v4l_dev</span>&#187;, к которому физически подключена камера.
            <p><b>Сочетание значений v4l_dev и input</b> фактически <b>указывают на номер разъёма</b>.
            Определить состав каналов поможет утилита<span class="cmd">v4l2-ctl --list-inputs -d /dev/videoX</span>,
            где v4l2-ctl - утилита из пакета v4l-utils, а X - номер устройства или любая ТВ-смотрелка
            (xawtv, tvtime, ...).
            </p>По умолчанию: <b>0</b>. Допустимые значения: обычно [0..3], редко [0..15].',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'norm',
        'type' => $CHECK_VAL,
        'def_val' => 0,
        'desc' => '<b>Видеостандарт</b>: <ul><li><b>PAL</b> - для большинства <b>цветных</b> камер;</li><li>NTSC -
            для оригинальных американских или японских;</li><li>SECAM - только для телевизионного сигнала;</li><li>
            <b>PAL NC</b> (no colour) - Для <b>ч/б</b> видеокамер.</li></ul>Для ВСЕХ каналов (input) одного
            конкретного video4linux устройства &#171;<span class="param">v4l_dev</span>&#187; может быть установлено
            только один стандарт, т.е. подключены камеры только одного видеостандарта цветности.<br><br>По умолчанию:
            <b>PAL (цв.)</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'pix_fmt',
        'type' => $STRING_VAL,
        'def_val' => null,
        'desc' => 'Принудительно использовать этот <b>FOURCC формат изображения</b> для видеозахвата.
           <br><br>Допустимые значения:
           <ul>
           <li>&laquo;<b>пусто</b>&raquo; - программа выберет самостоятельно из списка предпочтительных форматов.</li>
           <li><b>FOURCC тэг</b> - 4 символа, обозначаюшие формат изображения. Например, &quot;YUYV&quot; (YUV 4:2:2)
           или
           &quot;MJPG&quot;, &quot;JPEG&quot;, &quot;MPEG&quot; (если поддерживаются). Посмотреть какие FOURCC форматы
           поддерживает ваша плата видеозахата или USB-камера /dev/videoX можно в выводе команды
           <span class="cmd">v4l2-ctl --list-formats -d /dev/videoX</span>, где v4l2-ctl - утилита из пакета v4l-utils,
           а X - номер устройства.
           </li>
           <li><b>ORIG</b> - программа попытается использовать текущий формат установленный для устройства.</li>
           </ul>
           По умолчанию:  &laquo;<b>пусто</b>&raquo; - программа выберет самостоятельно',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'fps',
        'type' => $STRING_VAL,
        'valid_preg' => '/\A\d+(\s*[x:\/]\s*\d+)*\Z/',
        'def_val' => null,
        'desc' => '<b>Желаемая (не фактическая!) скорость видеозахвата в кадрах в секунду</b>.
   <br><br>Допустимые значения:
   <ul>
   <li><b>&laquo;пусто&raquo;</b> или 0 - не ограничивать, т.е. видеозахват на текущей скорости устройства;</li>
   <li><b>[1-60] или дробь период_в_сек/кадров_за_период</b>  - ограничить скорость видеозахвата. Если драйвер
   устройства поддерживает управление скоростью захвата, то необходимо выбирать значения fps поддерживаемые
   устройством/драйвером: см. вывод команды <span class="cmd">v4l2-ctl --list-formats-ext -d /dev/videoX</span>,
   где v4l2-ctl - утилита из пакета v4l-utils, а X - номер устройства.</li>
   </ul>
   Примечания:
   <ul>
   <li>при использовании PCI плат видеозахвата без режима мультиплексирования (каждая  камера подключена на свой
   отдельный видеокодер) или USB-камер, <i>максимальная скорость видеозахвата</i> ограничивается используемым
   телевизионным стандартом (25fps PAL, 30fps NTSC) и возможностями устройства (если USB-камера).</li>
   <li>скорость видеозахвата с PCI плат видеозахвата в режиме мультиплексирования регулируется только количеством
   видеокамер, подключенных к одному видеокодеру (устройству video4linux, параметр v4l_dev) и составляет [4..6] fps
   на камеру при 2-камерах на видеокодер и ещё меньше при бОльшем кол-ве камер на один видеокодер.
   </li>
   </ul>
   По умолчанию: &laquo;<b>пусто</b>&raquo; - <b>не ограничивать</b>.',
        'flags' => $F_BASEPAR | $F_IN_DEF | $F_IN_CAM | $F_RELOADED,
        'cats' => '3.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'auto_brightness',
        'type' => $BOOL_VAL,
        'def_val' => 0,
        'desc' => 'Режим <b>автоматической регулировки яркости</b>.<p>Подстройка осуществляется каждые 5 секунд,
        только при включенном &#171;<span class="param">brightness_control</span>&#187; и только когда не фиксируется
        движение.</p>По умолчанию: <b>Выкл</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'brightness',
        'type' => $INT_VAL,
        'def_val' => null,
        'desc' => '<b>Яркость</b> ' . $v4l_int_cntrl . ' <br><br>Прим.: другие многочисленные (обычно менее
            применимые) video4linux-параметры можно устанавливать до запуска демона &#171;' . $conf['daemon-name']
            . '&#187;, определяя параметры запуска модулей ядра устройств видеозахвата
            (см. MODINFO(8) и MODPROBE.CONF(5))
            или с помощью специальных video4linux утилит. Например, для просмотра всех параметров устройства выполните в
            терминале <span class="cmd">v4l2-ctl --list-ctrls --list-ctrls-menu -d /dev/videoX</span>, где v4l2-ctl -
            утилита из пакета v4l-utils, а X - номер устройства.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'contrast',
        'type' => $INT_VAL,
        'def_val' => null,
        'desc' => "<b>Контраст</b>. $v4l_int_cntrl",
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'saturation',
        'type' => $INT_VAL,
        'def_val' => null,
        'desc' => "<b>Насыщенность цвета</b>. $v4l_int_cntrl",
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    /*
    array(
      'name'    => 'frequency',
      'type'    => $INT_VAL,
      'def_val' => 0,
      'desc'    => 'Частота тюнера (для ТВ-сигнала) в кГц. По умолчанию: 0 - не использовать ТВ-тюнер.',
      'reloaded'=> 1,
      'in_def'  => 0,
      'in_cam'  => 1,
      'cats'    => '3.2',
      'subcats' => NULL,
      'mstatus' => 1,
    ),
    */

    /* ALSA */
    array(
        'name' => 'alsa_dev_name',
        'type' => $STRING_VAL,
        'def_val' => null,
        'desc' => '<b>ALSA-имя устройства аузиозахвата</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '3.3',
        'subcats' => null,
        'mstatus' => 1,
    ),
    /*
    array(
       'name'    => 'alsa_dev_channels',
       'type'    => $INT_VAL,
       'def_val' => NULL,
       'desc'    => '<b>Формат sample</b>.',
       'flags'   => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
       'cats'    => '3.3',
       'subcats' => NULL,
       'mstatus' => 1,
    ),

    array(
       'name'    => 'alsa_sample_rate',
       'type'    => $INT_VAL,
       'def_val' => NULL,
       'desc'    => '<b>sample_rate</b>.',
       'flags'   => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
       'cats'    => '3.3',
       'subcats' => NULL,
       'mstatus' => 1,
    ),
    */

    /* обработка */

    array(
        'name' => 'motion_detector',
        'type' => $BOOL_VAL,
        'def_val' => 1,
        'desc' => '<b>Обнаруживать движение в кадре</b> с помощью <b>программного детектора движения (ПДД)</b> или нет.
            <br /><br />Ключевая функция для профессиональных систем. При использовании выборочного режима записи
            ({rec_mode}) существенно облегчает поиск в архиве видеозаписей.<br /><br />Для использования ПДД
            также необходимо включить {decode_video}.<br /><br />По умолчанию: <b>Вкл</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1',
        'subcats' => '5.1.3',
        'mstatus' => 2,
    ),
    array(
        'name' => 'text2img',
        'type' => $BOOL_VAL,
        'def_val' => 1,
        'desc' => '<b>&quot;Врезать&quot; в кадр информационные строки</b> (название камеры, дата/время и др.).
           <br><br>Замечания:
           <ul>
           <li><b>Особенность по сетевым ip-камерам</b>: если типы сжатия (кодеки) входящего (исходного) видеопотока
           и исходящего (запись, просмотр по сети) совпадают, то в исходящем потоке по-возможности используются
           оригинальные видеокадры, полученные с ip-камер. В этом конкретном случае, &quot;врезка&quot; текстовой
           информации будет видна только при локальном просмотре в программе avreg-mon для задачи настройки
           детектора движения. Для ip-камер настоятельно рекомендуем включить наложение даты/времени и, желательно,
           названия камеры на кадр в настройках камеры (часто называют &quot;text overlay&quot;).</li>
           <li><b>Области &quot;врезки&quot; текста исключаются из анализа детектором движения</b>, т.к при
           &quot;врезке&quot; модифицируются оригинальные видеокадры с камеры.</li>
           </ul>По умолчанию: <b>Вкл</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1',
        'subcats' => '5.1.1',
        'mstatus' => 2,
    ),
    array(
        'name' => 'brightness_control',
        'type' => $BOOL_VAL,
        'def_val' => 1,
        'desc' => '<b>Контролировать среднее значение яркости в кадре или нет</b>.<p>Используется для получения
            событий засветки и затемнения камеры, автоподстройки яркости аналоговых плат видеозахвата, а также может
            быть использовано в алгоритмах программного детектора движения.</p>По умолчанию: <b>Вкл</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1',
        'subcats' => '5.1.2',
        'mstatus' => 2,
    ),
    /*
    array(
       'name'    => 'rotate',
       'type'    => $CHECK_VAL,
       'def_val' => 0,
       'desc'    => 'Программный <b>разворот кадра</b>.<br><br>Увеличивает нагрузку на CPU сервера, поэтому
    используйте эту возможность только в случае существенной необходимодимости. Большинство &quot;правильных&quot;
    сетевых камер могут делать поворот самостоятельно.<br><br>По умолчанию: <b>без поворота</b>.',
       'flags'   => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
       'cats'    => '5.1',
       'subcats' => NULL,
       'mstatus' => 1,
    ),
    */

    array(
        'name' => 'Hx2',
        'type' => $BOOL_VAL,
        'def_val' => 0,
        'desc' => '<b>Программное масштабирование при захвате полукадрами (обычно с PCI-плат видеозахвата)</b>.
            Увеличить значение разрешения по вертикали в 2 раза при просмотре видео в реальном времени, а также
            записывать увеличенное значение (вместо реального) в базу и передавать в event-collector скрипт.
            Установите, если для снижения нагрузки на видеосервер вы используете  <b>захват полукадрами</b>, т.е.
            обычно тогда, когда разрешение по вертикали <nobr>&lt;= 288(pal)/240(secam)</nobr>, например:
            720х288 или 640х240.<br><br>По умолчанию: <b>Выкл. - не масштабировать.</b>.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'dazzle_threshold',
        'type' => $INT_VAL,
        'def_val' => 200,
        'desc' => '<b>Максимальный порог</b> среднего значения яркости в кадре,
   <br />при котором <b>вы считаете</b>, что камера подверглась <b>засветке</b>.
   <br /><br />По умолчанию: <b>200</b>, допустимые значения [180..255].',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'darkness_threshold',
        'type' => $INT_VAL,
        'def_val' => 50,
        'desc' => '<b>Минимальный порог</b> среднего значения яркости в кадре,
   <br />при котором <b>вы считаете</b>, что камера подверглась <b>затемнению</b>.
   <br /><br />По умолчанию: <b>50</b>, допустимые значения [0..80].',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'bright_hysteresis',
        'type' => $INT_VAL,
        'def_val' => 5,
        'desc' => '<b>Гистерезиз (в секундах) принятия решения о засветке/затемнении кадра</b>.
           <br />Интервал, в течении которого, среднее значение яркости в кадре стабильно превышает или не
           достигает порогов &#171;<span class="param">dazzle_threshold</span>&#187; и &#171;
           <span class="param">darkness_threshold</span>&#187;, соответственно.
           <p>Определяя значение &#171;<span class="param">bright_hysteresis</span>&#187;, кроме всего прочего,
           следует учитывать:</p>
           <ul>
           <li>значение средней яркости измеряется и контролируется не чаще 1 раза в секунду (<= 1 fps);</li>
           <li>большинство видеокамер (и аналоговых и сетевых) пытаются самостоятельно выравнивать яркость в
           некоторых пределах.</li>
           </ul>
           По умолчанию: <b>5 сек.</b>, допустимые значения [1..60].',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'rec_target',
        'type' => $STRING_VAL,
        'def_val' => null,
        'desc' => '<b>Список камер, чья запись должна управляться детектором этой камеры</b>.
            Действует только на камеры с:
            <ul>
            <li>выборочным режимом записи <nobr>({rec_mode} = &quot;Выборочно&quot;)</nobr>;</li>
            <li>выключенным собственным детектором движения <nobr>({motion_detector} = &quot;Выкл.&quot;)</nobr>;</li>
            <li><span class="warntext">из того же
            <a href="http://avreg.net/manual_applications_smp.html" targer="_blank">
            профиля (процесса avregd)</a> что и эта камера</span>.</li>
            </ul>
            <p>Пример списка: &quot;5&quot; или &quot;2-5, 11, 20&quot; (указывать без кавычек).<br>По умолчанию:
            &quot;пусто&quot; - управлять записью только своей камеры.</p>',
        'flags' => $F_RELOADED | $F_IN_CAM,
        'cats' => '5.1.3',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'motion_session_end',
        'type' => $INT_VAL,
        'def_val' => 10,
        'desc' => '<b>Период &quot;спокойствия&quot; детектора движения в секундах, логически отделяющий один
            сеанс движения от другого</b>.
            <br><br>При выборочном режиме записи </nobr>({rec_mode} = &quot;Выборочно&quot;)</nobr>, запись файла(ов)
            на диск производится только внутри сеанса. В общем случае, увеличение значения {motion_session_end} приводит
            к увеличению объёма записи при прочих равных условиях.
            <br><br>Допустимые значения от <b>2 до 600 сек.</b>. По умолчанию: <b>10 сек.</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1.3',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'mask_file',
        'type' => $CHECK_VAL,
        'def_val' => null,
        'desc' => '
Графический JPEG файл с <b>изображением-маской</b> кадра, который <b>&quot;накладывается&quot;
на кадр</b> от камеры. <b>На областях, залитых в маске чёрным</b> цветом, <b>движение игнорируется</b>.
Обязательно <b>исключите области</b>, попадающие в поле зрения камеры из анализа детектором, подобные этим:
<ul>
<li>динамические (дата/время) текстовые врезки (text overlay), накладываемые на видео непосредственно ip-камерой,</li>
<li>&quot;<b>неинтересные</b>&quot; для вас, чужая территория, например, или большая область неба
(если конечно не боитесь диверсантов-дельтапланеристов),</li>
<li><b>сильные источники шума</b> (области засветки камеры: прямой солнечный свет или приборы освещения;
качающееся на ветру дерево; и т.п.).</li>
</ul>
<p>Размер изображения маски должен совпадать с размерами кадров, захватываемых с камеры. Для создания маски,
возьмите из архива любой сохранённый JPEG-кадр с камеры и залейте &quot;ненужные&quot; области чёрным цветом
в любом графическом редакторе (GIMP, например). Имя файла маски должно содержать только латинские символы и не
содержать пробелы и спец. символы. После установки или изменения маски потребуется подстройка параметров &#171;
<span class="param">diff_pxls_threshold</span>&#187; и, возможно, &#171;<span class="param">noise_threshold</span>
&#187; и &#171;<span class="param">adjust_noise_threshold</span>&#187; , поэтому, если планируете использовать
маску, её нужно сделать и установить в первую очередь.</p>По умолчанию: <b>не установлено</b> - не
использовать маску.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1.3',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'noise_threshold',
        'type' => $INT_VAL,
        'def_val' => null,
        'desc' => '<p><b>Порог шума</b> - допустимая разница между изменением яркости двух точек в
            одной позиции от последовательно полученных кадров, которая рассматривается как шум или помеха
            (лёгкое дрожание камеры, дождь, снег, электрический шум видеосигнала и т.п.).</p>
            Допустимые значения:
            <ul>
            <li><b>пусто или 0</b> - <b>автоматическая регулировка</b> самой программой, см. ниже
            параметр &#171;<span class="param">adjust_noise_threshold</span>&#187;;</li>
            <li><b>[10..50]</b> - <b>&quot;ручная&quot;</b> точная статическая настройка (совет: начните с 30 если не
            устраивает авто-регулировка).</li>
            </ul>
            Если автоматическая регулировка вас не устраивает, то при определении оптимального значения
            постарайтесь добиться 2 целей:
            <ul>
            <li>стабилизации diff<sup>*</sup> или %diff<sup>**</sup> на спокойных кадрах и при любой
            освещённости, например, c отклонением от среднего не более чем на 5%;</li>
            <li>существенного изменения diff<sup>*</sup> или %diff<sup>**</sup> на неспокойных кадрах
            (при движении) при самом медленном движении самого малого объекта в кадре, интересующего вас.</li>
            </ul>
            <p>Примечания:
            <br><sup>*</sup>&nbsp;&nbsp;&nbsp;diff = 1000 + количество пикселей, &quot;прошедших&quot;
            порог &#171;<span class="param">noise_threshold</span>&#187;;
            <br><sup>**</sup>&nbsp;&nbsp;%diff - относительное изменение diff в процентах.
            <br>Оба значения отображаются в правом верхнем углу кадра при просмотре в локальном вьювере
            &#171;avreg-mon&#187; (при &quot;включенных&quot; &#171;<span class="param">text2img</span>&#187; и &#171;
            <span class="param">text_changes</span>&#187;) и могут выводиться в системый журнал syslog
            (при запуске avregd с ключом -v).
            </p>
            По умолчанию: <b>не заполнено, т.е. авто-регулировка</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1.3',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'adjust_noise_threshold',
        'type' => $INT_VAL,
        'def_val' => 0,
        'desc' => '<b>Дополнительная коррекция режима автоматической порога шума</b> (при пустом или
            нулевом значении &#171;<span class="param">noise_threshold</span>&#187;).
            <p>Допустимые значения: [-5 .. +5], 0 - с нулевой (или без) коррекции, отрицательные значения -
            увеличить порог (&quot;загрубить&quot;), положительные - уменьшить.</p>
            По умолчанию: <b>0</b> - с нулевой (или без) коррекции.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1.3',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'diff_pxls_threshold',
        'type' => $INTPROC_VAL,
        'def_val' => '10%',
        'desc' => '<b>Порог срабатывания детектора</b> - число &quot;изменившихся&quot; в новом кадре пикселей,
    вычисленное с учётом вышеописанных параметров, при котором срабатывает Программный Детектор Движения (ПДД). Такой
    кадр гарантированно будет сохранён на диске (если же конечно включен режим записи на диск).
   <p>Значение &#171;<span class="param">diff_pxls_threshold</span>&#187; должно определяться в зависимости от
   <b>мин. размера и мин. скорости отслеживаемых объектов</b>. Допустимо указывать как относительное изменение
   значения diff в процентах % (со знаком % в конце числа, например 15%), так и точное абсолютное значение порога
   (без знака %, например 2000). Значения 0% или 0 отключают детекцию.</p>
   По умолчанию: <b>10%</b>, т.е. в пределах %diff [-10%..+10%] - &quot;<i>в Багдаде всё спокойно</i>&quot;.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1.3',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'shake_filter',
        'type' => $BOOL_VAL,
        'def_val' => null,
        'desc' => '<b>Фильтрация</b> эффектов <b>быстрого кратковременного дрожания</b> в кадре. Особенно
            важен при аналоговом видеозахвате с <b>мультиплексируемых каналов</b> (когда к одному видеокодеру
            BT878/SAA71xx/CX2388x подключено сразу несколько камер).
            <br>Допустимые значения:
            <ul>
            <li><b>пусто или не установлено</b> - программа принимает решение самостоятельно, включая фильтр только
            для мультиплексируемых аналоговых каналов (камер);</li>
            <li><b>Вкл.</b> или <b>Выкл.</b> - безусловное включение или отключение фильтра.</li>
            </ul>По умолчанию: <b>не установлено</b>, т.е. &#171;авто&#187;.',
        'flags' => $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1.3',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'text_font_size',
        'type' => $CHECK_VAL,
        'def_val' => null,
        'desc' => 'Размер шрифта для &#171;врезаемого&#187; в кадр текста.
            <br><br>По умолчанию: <b>не установлено</b>, т.е. &#171;авто&#187; - &#171большой&#187; если ширина кадра
            более 480 пикселей.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'text_left',
        'type' => $STRING_VAL,
        'max_len' => 30,
        'def_val' => null,
        'desc' => 'Текст в нижнем левом углу кадра. Также это и <b>название камеры</b> или зоны наблюдения.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_CAM,
        'cats' => '5.1.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'text_right',
        'type' => $STRING_VAL,
        'max_len' => 30,
        'def_val' => null,
        'desc' => 'Шаблон для <b>временной отметка кадра</b> в правом нижнем углу кадра.
   <br><br>По умолчанию: <b>%Y-%m-%d\n%H:%M:%S-%t</b>.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'text_changes',
        'type' => $BOOL_VAL,
        'def_val' => 0,
        'desc' => '
Текстовая строка в верхнем правом углу кадра, необходимая при <b>настройке параметров детектора
движения</b>. Формат строки: &#171;<b>msg&nbsp;diff(%diff)/br.avg</b>&#187;, где:
<ul>
<li><b>msg</b> - флаг &quot;сработки&quot; детектора движения на этом кадре, &quot;ALRM&quot;
(запись на диск отключена) или &quot;REC&quot; (кадр будет записан).</li>
<li><b>diff</b> и <b>%diff</b> - см. описание параметра &#171;<span class="param">noise_threshold</span>
&#187;.</li>
<li><b>br.avg</b> - среднее значение яркости (при вкл. &#171;<span class="param">brightness_control</span>&#187;).
</li>
</ul>
По умолчанию: <b>Выкл</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '5.1.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'rec_mode',
        'type' => $CHECK_VAL,
        'def_val' => 1,
        'desc' => '
<b>Режим записи на жесткий диск</b>:
<ul>
<li>&#171;<b>Без записи</b>&#187; - запись на диски заблокирована;</li>
<li>&#171;<b>Выборочный</b>&#187; (по-умолчанию) режим, при котором запись управляется событиями <i>любых</i>
из следующих подсистем:</p>
<ol>
<li><i>детектор движения</i> (если включен и настроен);</li>
<li><i>детектор звука</i> (пока не реализовано);</li>
<li><i>внешние команды</i> (<a href="http://avreg.net/manual_applications_avregd-cgi-api.html" target="_blank">
avregd HTTP CGI-интерфейс</a>).</li>
</li></ol>
</li>
<li>&#171;<b>Всё подряд</b>&#187; - &#171;сплошной&#187; (непрерывный, безусловный) режим записи, при котором
<i>всегда и абсолютно все</i> захваченные с устройств видео-кадры и аудио-фреймы записываются на диск.</li>
</ul>
<p>По умолчанию: &#171;<b>Выборочный&#187.</p>',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '11',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'rec_format',
        'type' => $CHECK_VAL,
        'def_val' => 1,
        'desc' => '<b>Медиаконтейнер - формат(тип) файла для записи</b>.
   <p>Если формат <b>не задан</b>, программа самостоятельно попытается подобрать подходящий формат с учётом:
   <ul>
   <li>кодеков сжатия исходных медиапотоков,</li>
   <li>наличия декодированных потоков (см. {decode_video}/{decode_audio}),</li>
   <li>минимизации затрат процессорных ресурсов сервера (%CPU).</li>
   </ul>
   </p>
   <div class="warn">
   Неправильный или неудачный выбор формата может повлечь:
   <ul>
   <li>блокировку процесса записи, например, при невозможности перекодирования (отсутствия встроенных
   кодеков сжатия);</li>
   <li>значительно возросшую нагрузку на CPU (из-за декодирования с последующим кодированием), следите чтобы она
   не превышала 80%.</li>
    </ul>
   </div>
   По умолчанию: не установлено - &#171;<b>авто</b>&#187;.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '11',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'pre_record',
        'type' => $INT_VAL,
        'def_val' => 3,
        'desc' => '<b>Буфер предзаписи в секундах.</b><p><b>Серьёзно увеличивает требование к объёму
            оперативной памяти</b></p>Допустимые значения: [1..5]. По умолчанию: <b>1 сек.</b>.',
        'flags' => $F_IN_DEF | $F_BASEPAR | $F_IN_CAM,
        'cats' => '11',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'file_max_megabytes',
        'type' => $INT_VAL,
        'def_val' => 10,
        'desc' => 'Ограничитель <b>максимального размера медиафайла</b> в МегаБайтах.<br><br>По достижению любого,
            этого или {file_max_minutes} (см. ниже) пределов, запись продолжится в новый файл. ' .
            $file_limits_and_detector .
            '<br><br>Если вы хотите иметь огромные фaйлы, по аналогии, например, с DVD/VIDEOCD, <b>подумайте</b>,
            удобно ли будет с ними работать в режиме доступа к видеоархиву по сети?<br><br>Допустимые значения:
            от 2 до 2000(2Гб). &nbsp;По умолчанию: <b>10 Mb</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '11',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'file_max_minutes',
        'type' => $INT_VAL,
        'def_val' => 60,
        'desc' => 'Ограничитель <b>максимальной продолжительность</b> медиафайла в <b>минутах</b>.
            <br><br>По достижению любого, этого или {file_max_megabytes} (см. выше) пределов,
            запись продолжится уже в новый файл. ' .
            $file_limits_and_detector .
            '<br><br>Допустимые значения: от 1 до 1440(24 часа) &nbsp;По умолчанию: <b>60 минут</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '11',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'rec_encoder_qscale',
        'type' => $INT_VAL,
        'def_val' => 5,
        'desc' => '<b>Степень сжатия кодируемых (если используется перекодировка) медиапотоков</b> или
            некий коэффициент, <b>обратный  качеству изображения</b>. Параметр соответствует ffmpeg-параметру
            qscale для режима VBR (переменный битрейт с целью удержания постоянного качества изображения/звука.
            <br><br>Прим.: <b>не используется при записи в оригинальном формате захвата</b>.<br><br>
            Допустимые значения: <b>2</b>(лучшее качество и макс. размер файла) - <b>30</b>(хуже, но меньше).<br/>
            По умолчанию <b>5</b>(оптимально).',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '11',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'rec_vcodec',
        'type' => $CHECK_VAL,
        'def_val' => '',
        'desc' => sprintf($rec_avcodec_fmt, 'видео', 'video'),
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '11.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'paranoid_snap',
        'type' => $INT_VAL,
        'def_val' => 0,
        'desc' => 'Дополнительно <b>записывать кадры-отметки JPEG через каждые {paranoid_snap} минут</b>
           при условии видеозахвата в MJPG и/или включенном декодировании исходного видеопотока {decode_video}.
           <div class="warn">Даже при небольших количестве видеокамер и объёме места под архив,
           использование этой опции может и, как правило, всегда приводит к существенному росту базы данных.
           Без серьёзной оптимизации системных настроек SQL-сервера операции поиска в архиве начинают выполняться
           с задержками до нескольких минут. Не используйте запись снапшотов если вы не являетесь серьёзным
           специалистом по SQL-серверам или будьте готовы обратиться к такому специалисту за помощью.
           </div>
           По умолчанию: <b>0 - не записывать</b>.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '11.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'extra_snap_period1',
        'type' => $INT_VAL,
        'def_val' => 0,
        'desc' => '<em>Длительность в секундах 1-го спапшотного периода</em> с момента <i>начала выборочной записи</i>
            (например, с начала каждого сеанса движения), в котором (периоде) на диск со скоростью не более чем
            1 fps (<em>1 кадр в секунду</em>) сохраняются кадры-отметки в формате jpeg, <em>дополнительно к записи в
            основной формат</em> (см. описание <em>rec_format</em> выше). На диск записываются любые кадры,
            как &quot;c движением&quot; так и &quot;спокойные&quot;, т.е. без учёта детектора движения.
            <p>' . $strOnlySelDet . '</p>Допустимые значения: [0..30] секунд (макс. 30 кадров).
            <br>По умолчанию: <b>0 - не записывать отметки в этом интервале</b>.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '11.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'extra_snap_period2',
        'type' => $INT_VAL,
        'def_val' => 0,
        'desc' => '<em>Длительность в секундах 2-го спапшотного периода</em> с момента окончания 1-го периода
            (см. <em>extra_snap_period1</em> выше), в котором (периоде) на диск со скоростью не более чем 0.2 fps
            (<em>1 кадр каждые 5 секунд</em>) сохраняются кадры-отметки в формате jpeg, <em>дополнительно к
            записи в основной формат</em> (см. описание <em>rec_format</em> выше). На диск записываются любые кадры,
            как &quot;c движением&quot; так и &quot;спокойные&quot;, т.е. без учёта детектора движения.
            <p>' . $strOnlySelDet . '<br>По умолчанию: <b>0 - не записывать отметки в этом интервале</b>.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '11.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'file_vtag',
        'type' => $STRING_VAL,
        'max_len' => 4,
        'def_val' => null,
        'desc' => '<b>FOURCC тег</b>.
           Например, установив fourcc тег как &#171;DIVX&#187; при записи в формате &#171;avi/mpeg4&#187;,
           записанные видеофайлы можно будет смотреть divx-кодеками и, возможно, на бытовых DVD-проигрывателях
           (примечание: по-умолчанию, для &#171;avi/mpeg4&#187; используется тег &#171;FMP4&#187;, который понимают
           только медиа-проигрыватели, использующие ffmpeg(*nix)/ffdshow(win).
           <br><br>По умолчанию: <b>не установлен</b>, т.е. будет установлен <b>автоматически</b>',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '11.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    /*
    array(
       'name'    => 'file_view_fps',
       'type'    => $INT_VAL,
       'def_val' => 7,
       'desc'    => '<b>Нормальная скорость воспроизведения видеофильма в видеопроигрывателе при просмотре</b>
    (т.е. специально  не ускоренная и не замедленная пользователем), в кадрах в секунду (<b>!!! не скорость
    записи</b>). Рекомендуется устанавливать равной или несколько более фактической скорости захвата с видеокамеры.
    <br><br>Допустимые значения: 1..30. По умолчанию: <b>7 кадров в секунду</b>.',
       'flags'   => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
       'cats'    => '11.1',
       'subcats' => NULL,
       'mstatus' => 1,
    ),
     */

    array(
        'name' => 'rec_acodec',
        'type' => $CHECK_VAL,
        'def_val' => '',
        'desc' => sprintf($rec_avcodec_fmt, 'аудио', 'audio'),
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '11.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'forced_saving_limit',
        'type' => $INT_VAL,
        'max_len' => 4,
        'def_val' => 5,
        'desc' => '
<b>Максимальная длительность принудительной записи (по команде) в минутах</b>.
Параметр служит для предотвращения бесполезного расходования дискового пространства в случае, когда оператор,
включив запись (командой), забывает отключать её.
На уровне пользователей предусмотрен одноимённый параметр, действующий подобным образом.
При определении значения ограничения принудительной записи, программа использует минимальное из двух значений
параметров forced_saving_limit: "камерного" и пользователя, подавшего команду.
<br /><br />
Если же вы планируете использовать сеансовую принудительную запись как основной режим записи
(например, <b>по расписанию или по событиям, датчикам</b>, и т.п.),
заведите специального пользователя в группе &quot;Операторы&quot;, от которого будут подаваться команды на
вкл./выкл. записи,
и <b>установите нулевое</b> (0 - не ограничивать) значение forced_saving_limit для камер(ы) и для этого
пользователя.
<br /><br />Допустимые значения: 0 - не ограничивать или [1..4320] минут, по умолчанию: &quot;<b>5 минут</b>&quot;. ',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '11',
        'subcats' => null,
        'mstatus' => 1,
    ),
    /*
     * ONLINE ONLINE ONLINE ONLINE ONLINE ONLINE ONLINE ONLINE ONLINE
     */

    array(
        'name' => 'allow_local',
        'type' => $BOOL_VAL,
        'def_val' => false,
        'desc' => 'Разрешить <b>локальное</b> (на сервере) <b>наблюдение</b> в реальном времени за этой камерой в
            программе <b>avreg-mon</b>.<br>Прим.: для включения режима также необходимо определить параметры
            вложенного подраздела &quot;локальное&quot; в персональных (не групповых) настройках камеры.
            <br><br>По умолчанию: <b>Выкл</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '15',
        'subcats' => '15.1',
        'mstatus' => 2,
    ),
    array(
        'name' => 'allow_networks',
        'type' => $BOOL_VAL,
        'def_val' => true,
        'desc' => 'Разрешить <b>наблюдение по сети</b>.<br><br>По умолчанию: <b>Вкл</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '15',
        'subcats' => '15.2',
        'mstatus' => 2,
    ),
    array(
        'name' => 'v4l_pipe',
        'type' => $CHECK_VAL,
        'def_val' => null,
        'desc' => 'Создать <b>виртуальный видеоканал</b>, на который будет <b>транслироваться видео</b> с этой камеры
            для локального вьювера <b>avreg-mon</b>.<br><br><b>Выбирать файлы</b> каналов нужно из предложенного
            списка последовательно, <b>без совпадений</b> c другими камерами. Если список пуст или не хватает
            каналов см. <a href="http://avreg.net/manual_install_avreg-mon.html" target="_blank">инструкцию
            по установке</a>.<br><br>По умолчанию: <b>не задано</b> - значит <b>локальный просмотр</b> для данной
            камеры <b>невозможен</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_CAM,
        'cats' => '15.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'v4l_pipe_maxrate',
        'type' => $INT_VAL,
        'def_val' => 60,
        'desc' => 'Ограничить этим значением (в кадрах в секунду) скорость видеопотока, отдаваемого в сквозной
            video4linux-канал (обычно для локального просмотра avreg-mon-ом)<br>Допустимые значения: [1..60];
            по-умолчанию: 60 - не ограничивать.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '15.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'v4l_pipe_nonmotion_maxrate',
        'type' => $INT_VAL,
        'def_val' => 0,
        'desc' => 'Ограничивает скорость потока &quot;cпокойных&quot; кадров в отсутствии движения до установленного
            вами значения, естественно, только при включенном детекторе движения. <br>Допустимые значения: [1..60];
            по-умолчанию: 60 - не ограничивать.',
        'flags' => $F_RELOADED | $F_IN_DEF | $F_IN_CAM,
        'cats' => '15.1',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'wc_maxrate',
        'type' => $INT_VAL,
        'def_val' => 60,
        'desc' => $_rate_lim_info,
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '15.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'wc_max_conn_per_cam',
        'type' => $INT_VAL,
        'def_val' => 2,
        'desc' => '<b>Максимальное кол-во</b> подключенных сетевых <b>клиентов</b>, одновременно просматривающих
            эту камеру в каждый момент времени.<br>Устанавливайте разумные значения с учётом реальных максимального
            количества пользователей и ресурсов сервера и сетевого оборудования. Иначе возможны перегрузка сервера и
            даже аварийный останов демона &#171;' . $conf['daemon-name'] . '&#187;.<br><br>Допустимые значения
            от 1 до 1000. По умолчанию: <b>5</b>.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '15.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'ptz',
        'type' => $CHECK_VAL,
        'def_val' => null,
        'desc' => '<div>Вариант системы команд <b>PAN/TILT/ZOOM</b>.</div>
        <br><div>Если установлено, в шапке окна камеры на странице просмотра в реальном времени при открытии
        &quot;панели управления&quot; появится кнопка &quot;PTZ&quot;.</div>
        <br>По умолчанию: не установлено.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '15.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'ipcam_interface_url',
        'type' => $STRING200_VAL,
        'def_val' => null,
        'desc' => '
<b>Прямой URL веб-интерфейса ip-камеры</b> (указывать с префиксом протокола, http:// или https://).
<br><br>Значение этого параметра будет подставленно в виде ссылки в заголовке окна камеры на странице просмотра в
реальном времени для пользователей все групп, исключая группу &#171;Только просмотр&#187;. Ссылка предназначена для
прямого перехода в &quot;родной&quot; веб-интерфейс сетевой камеры (откроется в новом окне) со страницы просмотра в
реальном времени веб-интерфейса AVReg.
<br><br>По умолчанию: не установлено - URL будет построен как &quot;<b>http://{InetCAM_IP}:{InetCam_http_port}</b>
&quot;, где параметры в фигурных скобках - параметры захвата с сетевых камер (см. раздел Захват - по сети).',
        'flags' => $F_RELOADED | $F_IN_CAM,
        'cats' => '15.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
//Алтернативные источники видео для веб-раскладок fs_url_alt_ cell_url_alt_
    array(
        'name' => 'cell_url_alt_1',
        'type' => $STRING200_VAL,
        'def_val' => null,
        'desc' => '
<b>Альтернативный &quot;ALT1&quot; URL</b> в качестве источника видео для камеры в раскладке (не в режиме fullscreen).
Определив этот и может другие альтернативные URL-ы,
в настройках веб-раскладки для этой камеры в качестве источника видео
вместо &quot;avregd&quot; нужно выбрать &quot;ALT1&quot; или &quot;ALT2&quot;.
<br /><br />
Допустимые форматы значений параметра:
<ul>
<li><span style="white-space:nowrap; font-weight: bold;">{cam_nr}[:avregd[:http[:{req_params}]]</span> - например:
<ul>
<li><span style="white-space:nowrap"><i>143</i></span>
- в окне камеры на странице просмотра в качестве источника MJPEG видео
будет использован URL вида <span style="white-space:nowrap">
http://<b>этот_сервер</b>/avreg-cgi/mjpg/video.cgi?camera=143</span>;</li>
<li><i>143:avregd:http:fps=5</i> - <span style="white-space:nowrap">
.../video.cgi?camera=143&fps=5</span>;</li>
</ul>
</li>
<li><span style="white-space:nowrap; font-weight: bold;">{cam_nr}:camera[:(rtsp|http)[:{req_params}]]</span> - например:
<ul>
<li><i>143:camera</i> - при указании ключевого слова &quot;camera&quot;
будет использован прямой URL на камеру (минуя сервер AVReg),
который сконфигурирован для захвата видео 143 камеры;</li>
<li><i>143:camera:rtsp</i> - то же что и выше, но только конкретно RTSP URL;</li>
<li><i>143:camera:http:fps=10&resolution=1920x1280</i> - то же что и выше, но только конкретно HTTP URL,
даже если {video_src} = rtsp и также в URL будут добавлены параметры запроса;</li>
</ul>
</li>
<li><span style="white-space:nowrap; font-weight: bold;">(http|rtsp)://[login:password@]host:port/path?query</span>
- полный URL на произвольный медиа-поток, например, для камер Axis:
<ul>
<li><i>rtsp://login:password@axis-camera-ip/axis-media/media.amp?resolution=320x240&audio=0</i></li>
<li><i>http://login:password@axis-camera-ip/axis-cgi/mjpg/video.cgi?resolution=640x480&fps=5</i></li>
</ul>
</li>
</ul>
<div>Примечания:
<ul>
<li>Основное назначение альтернативных URL-лов - переключение
на поток высокого разрешения при разворачивании камеры в полный экран
при использовании  <a href=" http://avreg.net/manual_applications_multi-stream.html" target="_blank">
мультипоточного захвата</a>.</li>
<li>Для RTSP url-ов на клиенте потребуется <a href="http://avreg.net/howto-install-vlc-plugin.html" target="_blank">
установить плагин VLC Browser Plugin</a>.</li>
<li>При использовании прямых URL-ов на камеру, для доступа из-вне (клиентов не из локалки)
необходимо обеспечить прямой доступ к камере (белый адрес или настройками маршрутизатора).</li>
</ul>
</div>
По умолчанию: не установлено (получать видео от avregd)',
        'flags' => $F_RELOADED | $F_IN_CAM,
        'cats' => '15.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'fs_url_alt_1',
        'type' => $STRING200_VAL,
        'def_val' => null,
        'desc' => 'Альтернативный <b>URL #1</b> для камеры развёрнутой в полный экран.',
        'flags' => $F_RELOADED | $F_IN_CAM,
        'cats' => '15.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'cell_url_alt_2',
        'type' => $STRING200_VAL,
        'def_val' => null,
        'desc' => 'Альтернативный <b>URL #2</b> для камеры в раскладке.',
        'flags' => $F_RELOADED | $F_IN_CAM,
        'cats' => '15.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'fs_url_alt_2',
        'type' => $STRING200_VAL,
        'def_val' => null,
        'desc' => 'Альтернативный <b>URL #2</b> для камеры развёрнутой в полный экран.</b>',
        'flags' => $F_RELOADED | $F_IN_CAM,
        'cats' => '15.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'onvif_profile_token',
        'type' => $STRING200_VAL,
        'def_val' => null,
        'desc' => 'ONVIF токен профиля медиа потока.',
        'flags' => $F_RELOADED | $F_IN_CAM | $F_IN_DEF,
        'cats' => '15.2',
        'subcats' => null,
        'mstatus' => 1,
    ),
    /* EVENTS */
    array(
        'name' => 'events2db',
        'type' => $CHECK_VAL,
        'def_val' => 'mediafiles,snapshots,capture,motion,quality,recording',
        'desc' => '
<b>Группы событий для записи в базу данных в таблицу
<a href="http://avreg.net/manual_applications_avreg5_db-structure.html" target="blank">EVENTS</a></b>.
<br><br>Основное назначение этого параметра - сократить размер базы данных, исключив ненужные для последующего
анализа данные:
<ul>
<li>медиафайлы - сохранение медиафайла на жесткий диск,</li>
<li>картинки - сохранение изображения (снапшота) на жесткий диск,</li>
<li>захват - запуск/остановка/сбой аудио/видео захвата с устройства,</li>
<li>движение - начало/окончание сеанса движения (при вкл. детекторе движения),</li>
<li>качество - изменение качества захватываемого изображения (засветка/затемнение, при вкл. контроле яркости),</li>
<li>запись - команды на включение или выключение режима записи детектором движения и/или командой пользователя
(не путать с событием сохранения файла в архиве),</li>
<li>клиенты - подключение/отключение сетевых клиентов.</li>
</ul>
<b>Примечание</b>:
<ol>
<li>отключение групп &#171;медиафайлы&#187; и &#171;картинки&#187; сделает соответствующие файлы невидимыми
для чистильщика архива от старых записей и на страницах веб-интерфейса.</li>
<li>см. также описание опции командой строки и конфигурационного файла &#171;db-disable-events&#187;
<span class="cmd">man avregd</span>.</li>
</ol>По умолчанию: медиафайлы, картинки, захват, движение, качество, запись',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '20',
        'subcats' => null,
        'mstatus' => 1,
    ),
    array(
        'name' => 'events2pipe',
        'type' => $CHECK_VAL,
        'def_val' => implode(',', $event_groups),
        'desc' => '
<b>Группы событий, передаваемые по каналу PIPE(7) во внешний скрипт &#171;<span class="param">
event-collector</span>&#187;</b>.
<p>
Под скриптом понимается любое исполняемое приложение, которое может быть написано вами на любом языке
программирования (shell, python, perl, ruby, php, java и даже на С). Пример на языке shell см. в каталоге
&#171;<span class="path">/usr/share/doc/avregd/examples/</span>&#187;
</p>
<p>
Скрипт должен быть скопирован в каталог &#171;<span class="path">/etc/avreg/scripts/</span>&#187; и его
абсолютный путь прописан в конфигурационном файле &#171;<span class="path">avreg.conf</span>&#187; в значении
параметра &#171;<span class="param">event-collector</span>&#187; секции параметров демона <nobr>avregd { ... }</nobr>.
</p>
По умолчанию: передаются все группы событий.',
        'flags' => $F_RELOADED | $F_BASEPAR | $F_IN_DEF | $F_IN_CAM,
        'cats' => '20',
        'subcats' => null,
        'mstatus' => 1,
    ),

);

$PARAMS_NR = count($PARAMS);

/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
