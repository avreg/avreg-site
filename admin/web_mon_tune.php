<?php
/**
 * @file admin/web_mon_tune.php
 * @brief Редактирование раскладки для WEB
 */
if (isset($_POST['pipes_show'])) {
    $pipes_show = $_POST['pipes_show'];
}
if (isset($pipes_show)) {
    settype($pipes_show, 'int');
    setcookie('avreg_pipes_show', $pipes_show, time() + 5184000);
} else {
    if (isset($_COOKIE['avreg_pipes_show'])) {
        $pipes_show = (Integer)$_COOKIE['avreg_pipes_show'];
    } else {
        $pipes_show = 1;
    }
}
$USE_JQUERY = true;

//определяем режим сохранения раскладки client(на клиенте)/server(на сервере)
$storage = 'server';
if (isset($_GET['storage'])) {
    $storage = $_GET['storage'];
} elseif (isset($_POST['storage'])) {
    $storage = $_POST['storage'];
}

if ($storage == 'client') {
    $link_javascripts = array(
        'lib/js/user_layouts.js',
        'lib/js/third-party/json2.js'
    );
}

$lang_file = '_online.php';
require('../head.inc.php');

//DENY($admin_status);
if ($storage != 'client') {
    DENY($admin_status);
}

require('./mon-type.inc.php');
?>

<script type="text/javascript" language="javascript">
    <!--
    function reset_to_list() {
        window.open('<?php echo $conf['prefix']; ?>/admin/web_mon_list.php', target = '_self');
    }
    // -->
</script>

<?php

echo '<h1>' . sprintf($web_r_mons, $named, $sip) . '</h1>' . "\n";

if (!isset($mon_nr) || $mon_nr == '') {
    die('empty $mon_nr');
}

if (!settype($mon_nr, 'int')) {
    die('$mon_nr is\'t integer value');
}

if ($mon_nr < 0) {
    die('$mon_nr < 0');
}

echo '<h2>' . sprintf($str_web_mon_tune, $counter, $mon_name) . '</h2>' . "\n";

if (isset($cmd)) {
    switch ($cmd) {
        case '_ADD_NEW_MON_OK_':
            $i = 0;
            $j = 0;

            $mwt = $_POST['mon_wins_type'];
            $allWINS = array();

            while ($i < count($mon_wins)) {
                if (!empty($mon_wins[$i])) {
                    //формирование единого объекта для всех ячеек раскладки
                    $allWINS[$i] = array();
                    array_push($allWINS[$i], $mon_wins[$i], $mwt[$j]);
                    $j++;
                }
                $i++;
            }

            $allWINS = json_encode($allWINS);
            if ($allWINS != '') {
                $PrintCamNames = (isset($PrintCamNames) && $PrintCamNames != null) ? 1 : 0;

                $adb->webReplaceLayouts(
                    $mon_nr,
                    $mon_type,
                    $mon_name,
                    $remote_addr,
                    $login_user,
                    $PrintCamNames,
                    $AspectRatio,
                    $ReconnectTimeout,
                    $allWINS
                );

                print '<p class="HiLiteBigWarn">' . sprintf(
                    $web_r_mon_changed,
                    $counter,
                    empty($mon_name) ? $mon_type : $mon_name
                ) . '</p>' . "\n";
                print '<center><a href="' . $conf['prefix'] . '/admin/web_mon_list.php" target="_self">'
                    . $r_mon_goto_list . '</a></center>' . "\n";
            } else {
                print '<p class="HiLiteBigErr">' . $strNotChoiceCam . '</p>' . "\n";
                print_go_back();
                require('../foot.inc.php');
                exit;
            }
            break;
    } // switch
} else {

    // cmd not set
    require('web_active_pipe.inc.php');
    $wins_array = & $active_pipes;
    if (count($wins_array) == 0) {
        print_go_back();
        require('../foot.inc.php');
        exit;
    } else {

        $aaa = array();

        if ($storage == 'client') { //если создаем клиентскую раскладку
            $clients_layouts = array();
            $clients_layouts = array();
            $layouts_cookie = $_COOKIE['layouts'];
            unset($_COOKIE['layouts']);
            if (isset($layouts_cookie)) {
                $tmp = json_decode($layouts_cookie, true);
                // Провераю корректность кодировки
                if (!$tmp) {
                    $tmp = json_decode(iconv("CP1251", "UTF8", $layouts_cookie), true);
                }
            } else {
                $tmp = array();
            }
            foreach ($tmp as $client_mon_nr => $l_val) {
                $_data = array();
                foreach ($l_val as $par_name => $par_data) {
                    $_data[$par_name] = $par_data;
                }
                $tmp_data = json_decode($_data['w']);
                $_data['wins'] = array();
                foreach ($tmp_data as $cell_nr => $cell_data) {
                    $_data['wins'][$cell_nr] = $cell_data;
                }

                $clients_layouts[(int)$client_mon_nr] = array(
                    0 => (int)$client_mon_nr, //номер клиентской раскладки
                    1 => $_data['t'], //тип
                    2 => $_data['n'], //название
                    3 => ($_data['d']) ? '1' : '0', // по умолчанию
                    4 => $_data['w'], //ячейки раскладки
                    5 => 'user_host', //CHANGE_HOST
                    6 => $_data['u'], // имя пользователя
                    7 => $_data['dd'], // дата создания
                    8 => ($_data['cn']) ? '1' : '0', //выводить названия камер
                    9 => $_data['p'], // сохранять пропорции
                    10 => $_data['rt'] // таймаут реконнекта
                );
            }

            $row = $clients_layouts[$mon_nr];
            $wins_cams = json_decode($row[4], true);
            $mon_type = $row[1];
            $mon_name = $row[2];
        } else {
            $row = $adb->webGetMonitor($mon_nr);
            $wins_cams = json_decode($row[4], true);
        }

        //формирование массива альтернативных источников видео
        $cams_srcs = array();
        foreach ($GCP_cams_params as $key => $val) {
            $cams_srcs[$key] = array();
            $cams_srcs[$key]['avregd'] = 'true';
            $cams_srcs[$key]['alt_1'] = ($val['cell_url_alt_1'] != null
                || $val['fs_url_alt_1'] != null) ? 'true' : 'false';
            $cams_srcs[$key]['alt_2'] = ($val['cell_url_alt_2'] != null
                || $val['fs_url_alt_2'] != null) ? 'true' : 'false';
        }
        print '<script type="text/javascript">' . "\n";
        print 'var cams_alt =' . json_encode($cams_srcs) . ";\n";
        print '</script>' . "\n";

        //Создание эл-та селект для ячеек раскладки
        for ($i = 0; $i < MAX_CAMS_INTO_LAYOUT; $i++) {

            if (!isset($cams_srcs)) {
                $cams_srcs = false;
            }

            $a = getSelectHtmlByName(
                'mon_wins[]',
                $wins_array,
                false,
                1,
                1,
                @$wins_cams[$i],
                true,
                'sel_change(this); show_sub_select(this);',
                '',
                null,
                $cams_srcs
            );
            array_push($aaa, $a);
        }
        /* Free last resultset */
        $result = null;

        if ($storage == 'client') { //Если раскладка создается на клиенте
            $redirect_url = $conf['prefix'] . '/admin/web_mon_list.php';
            print '<form action="' . $conf['prefix'] . '/online/' . '" onSubmit="user_layouts.tune_save(\'' . $mon_nr
                . 'UL' . '\', \'' . $redirect_url . '\');" method="POST">' . "\n";
        } else { //Если раскладка создается на сервере
            print '<form action="' . $_SERVER['PHP_SELF'] . '"  onSubmit="return validate();" method="POST">' . "\n";
        }

        // print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"  onSubmit="return validate();">'."\n";
        print '<p class="HiLiteBigWarn">' . $strMonAddInfo2 . '</p>' . "\n";
        print '&nbsp;&nbsp;&nbsp;' . $strName . ': <input type="text" name="mon_name" size=16 maxlength=16 value="'
            . $mon_name . '">' . "\n";
        layout2table($mon_type, ($mon_type == 'QUAD_25_25') ? 400 : 300, $aaa);
        print '<input type="hidden" name="cmd" value="_ADD_NEW_MON_OK_">' . "\n";
        print '<input type="hidden" name="mon_nr" value="' . $mon_nr . 'UL' . '">' . "\n";
        print '<input type="hidden" name="counter" value="' . $counter . '">' . "\n";
        print '<input type="hidden" name="mon_type" value="' . $mon_type . '">' . "\n";

        //Селектор сохранять пропорции/ на весь экран
        $AspectRatio = trim($row[9]);
        print '<br /><div><div style="float:left;" >' . $strAspectRatio . ":&nbsp;&nbsp;</div> \n";
        print '<div >' . getSelectByAssocAr(
            'AspectRatio',
            $AspectRatioArray,
            false,
            1,
            1,
            $AspectRatio,
            false
        ) . "</div></div>\n";

        //Выводить имена камер
        $PrintCamNames = ($row[8] == 1) ? 'checked' : 'unchecked';
        print '<br /><div><span>' . $strPrintCamNames . ":&nbsp;&nbsp;</span>\n";
        print '<span><input type="checkbox" name="PrintCamNames" ' . $PrintCamNames . ' />' . "</span></div>\n";

        //Установить интервал попыток переподключения к камере при отсутствии соединения
        $ReconnectTimeout = trim($row[10]);
        print '<br /><div><span>' . $strReconnectTimeout . ":&nbsp;&nbsp;</span> \n";
        print '<span>' . getSelectByAssocAr(
            'ReconnectTimeout',
            $ReconnectTimeoutArray,
            false,
            1,
            1,
            $ReconnectTimeout,
            false
        ) . "</span> $strReconnectBrowserInfo</div>\n";

        //Кнопки формы
        print '<br><input type="submit" name="btn" value="' . $strSave . '">' . "\n";
        if ($storage == 'client') { //сохраняем изменения клиентской раскладки
            print '<input type="reset" name="btn" value="' . $strRevoke . '" onclick="user_layouts.redirect(\''
                . $redirect_url . '\', true);">' . "\n";
        } else {
            print '<input type="reset" name="btn" value="' . $strRevoke . '" onclick="reset_to_list();">' . "\n";
        }

        print '</form>' . "\n";
    }
}
// phpinfo ();
require('../foot.inc.php');
