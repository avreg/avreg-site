<?php
session_start();
/**
 * @file admin/web_mon_list.php
 * @brief Список определённых раскладок для web
 */

/**
 *
 * Функция выводит информацию о раскладке монитора в таблицу создания раскладок
 * @param int $l_nr Номер монитора
 * @param array $l_def Настройки монитора
 * @param bool $is_admin Админ
 * @param $layout_word
 * @param $counter
 * @param $AspectRatio
 * @param $ReconnectTimeout
 * @param $PrintCamNames
 * @param $isDefault
 */
function prt_l(
    $l_nr,
    $l_def,
    $is_admin,
    $layout_word,
    $counter,
    $AspectRatio,
    $ReconnectTimeout,
    $PrintCamNames,
    $isDefault
) {
    global $strAspectRatio, $strPrintCamNames, $strReconnectTimeout, $AspectRatioArray,
        $ReconnectTimeout, $ReconnectTimeoutArray, $strCamName_Yes, $strCamName_No,
        $strSetByDefault, $strDelete, $strTune;

    $is_client_layout = (false != strpos($l_nr, "UL"));

    if ($PrintCamNames == 1 || $PrintCamNames == "t") {
        $PCN = true;
    } else {
        $PCN = false;
    }

    if (!empty($l_def['layout_name'])) {
        $l_name =& $l_def['layout_name'];
    } else {
        $l_name =& $l_def['layout_type'];
    }

    print "<div>\n";
    print "<div>\n";
    print "<span class='HiLite'>[$l_nr]&nbsp;$l_name";

    print "</span>\n";

    if ($is_client_layout || $is_admin) {
        print '<span style="float: right; vertical-align: top;">';
        $req_array = array(
            'mon_nr' => $l_nr,
            'mon_type' => $l_def['layout_type'],
            'counter' => $counter,
            'mon_name' => $l_name
        );
        if ($is_client_layout) {
            $req_array['storage'] = 'client';
            $url = './web_mon_tune.php?' . http_build_query($req_array);
            print '<a onclick="return user_layouts.to_tune_mode(\'' . $l_nr. '\', \'' . $url
                . '\')" href="#" >' . "\n";
        } else {
            $url =  './web_mon_tune.php?' . http_build_query($req_array);
            print "<a href='$url' >\n";
        }
    
        print '<img src="../img/gearwheel.png" width=24 height=24 title="' . $strTune
            . '" class="vertical-align: middle;" alt="' . $strTune . '" />'
            . "</a>\n";

        if ($is_client_layout) {
            $url = './web_mon_list.php?' . http_build_query($req_array);
            print '<a onclick="return user_layouts.remove(\'' . $l_nr. '\', \'' . $url
                . '\')" href="#" >' . "\n";
        } else {
            $req_array['cmd'] = 'DEL';
            $url =  './web_mon_list.php?' . http_build_query($req_array);
            print "<a href='$url' >\n";
        }

        print '<img src="../img/trash_24x24.png" title="' . $strDelete
            . '" class="vertical-align: middle;" alt="' . $strDelete . '" />'
            . "</a>\n";

        print "</span>\n";
    }
    print "</div>\n";

    if (!empty($l_def['CHANGE_TIME'])) {
        print '<div>' . $l_def['CHANGE_USER'] . '@' . $l_def['CHANGE_HOST'] .
            '<br>' . $l_def['CHANGE_TIME'] . "</div>\n";
    }

    //сохранять пропорции/ на весь экран ?
    print '<br /><div style="float:left;" >' . $strAspectRatio . ':&nbsp;'
        . $AspectRatioArray[$AspectRatio] . "</div>\n";
    //Выводить имена камер ?
    print '<br /><div style="float:left;" >' . $strPrintCamNames . ":&nbsp;"
        . ($PCN ? $strCamName_Yes : $strCamName_No) . "</div>\n";
    //реконнект таймаут
    print '<br /><div style="float:left;" >' . $strReconnectTimeout . ":&nbsp;"
        . intval(
            @$ReconnectTimeoutArray[$ReconnectTimeout]
        ) . "</div>\n";
    //$radio_disable = ($admin_user)? '':'disabled'; //доступно толко администратору
    print '<br /><div style="float:left;" >' . $strSetByDefault . ":";
    if ($is_admin) {
        $onchange = "SetByDefault($l_nr);";
    } else {
        $onchange = "user_layouts.setUserLayoutsDefault('$l_nr');";
    }
    $isDefault = ($isDefault == 1) ? true : false;
    print "<input type=\"radio\" name=\"ByDefault\" " . ($isDefault ? 'checked="checked"' : '')
        . " onchange=\"$onchange\" noLay=\"$l_nr\"/></div>\n";
    print '</div><br /><br />' . "\n";
}

//Генерация страницы

$USE_JQUERY = true;

$link_javascripts = array(
    'lib/js/user_layouts.js',
    'lib/js/third-party/json2.js'
);

$lang_file = '_online.php';

/* Если не установлено ни одной раскладки (как клиентом, так и администратором),
перенаправляем на создание пользовательской раскладки*/
require('../head.inc.php');
require('../admin/mon-type.inc.php');
$user_l_cook = "user_layouts.setCookie('layouts',
                                       JSON.stringify(user_layouts.client_layouts),
                                       new Date() + 1,
                                       '/',
                                       window.location.hostname,
                                       ''
                                      );";
$user_redirect = "user_layouts.redirect('" . $conf['prefix'] . "/online/');";
if (!isset($_SESSION['is_admin_mode'])) {
    echo '<a href="#" onclick="' . $user_l_cook . ' ' . $user_redirect . ';">' . $strBackOnline . '</a>';
}
if ($admin_user) {
    ?>
    <script type="text/javascript">
        //JS-для установки раскладки по умолчанию
        function SetByDefault(layoutNum) {
            user_layouts.setUserLayoutsDefault('____0UL');
            $(document).find('[checked^=checked]').each(function () {
                console.log(this);
                if ($(this).attr('noLay') !== layoutNum || typeof($(this).attr('noLay')) === 'undefined') {
                    $(this).removeAttr('checked');
                }
            });
            $.ajax({"url": "web_set_def.php?layout=" + layoutNum + " "})
                .done(function (data) {
                    if (data != 'NULL') {
                        $("<div style=\"position:absolute; top:300px; left:300px; z-index:100; color:Yellow; \
                                background-color:DarkRed; border:3px solid black; cursor:default; \">\
		                        <div style=\"font-weight:bold; color:Yellow; border:2px solid black; padding:2px; \
		                        float:right;\">X</div>\
		                        <span style=\" font-size:14pt;  \">ERROR</span><br /> " + data + "</div>")
                            .appendTo("body")
                            .click(function () {
                                $(this).remove();
                            });
                    }
                });
        }
    </script>
    <?php
}

//Заголовок окна ($named,$sip - название и IP сервера; $r_mons - в lang/russian/utf-8/common.inc.php)
echo '<h1>' . sprintf($web_r_mons, $named, $sip) . '</h1>' . "\n";

$counter = @$GLOBALS['counter'];
//загружается если нажато удаление раскладки
if (isset($cmd)) {
    DENY($admin_status);
    switch ($cmd) {
        case 'DEL':
            echo '<p class="HiLiteBigWarn">' . sprintf($fmtLayoutDelConfirm, $counter, $mon_name) . '</p>' . "\n";
            print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
            print '<input type="hidden" name="cmd" value="DEL_OK">' . "\n";
            print '<input type="hidden" name="mon_nr" value="' . $mon_nr . '">' . "\n";
            print '<input type="hidden" name="counter" value="' . $counter . '">' . "\n";
            print '<input type="hidden" name="mon_name" value="' . $mon_name . '">' . "\n";
            print '<input type="submit" name="mult_btn" value="' . $strYes . '">' . "\n";
            print '<input type="submit" name="mult_btn" value="' . $strNo . '">' . "\n";
            print '</form>' . "\n";
            require('../foot.inc.php');
            exit;
            break;
        case 'DEL_OK': //Удаление раскладки
            if (($mult_btn == $strYes) && isset($mon_nr)) {
                $adb->webDeleteLayouts($mon_nr);
                echo '<p><font color="' . $warn_color . '">' . sprintf(
                    $fmtLayoutDeleted,
                    $counter,
                    $mon_name
                ) . '</font></p>' . "\n";
                exit();
            }
            unset($mon_nr);
            break;
    }
}

if (!isset($_SESSION['is_admin_mode'])) {
    //готовые раскладки
    //раскладки определенные клиентом
    echo '<h2>' . $client_mon_list . '</h2>' . "\n";

    ////->
    $clients_layouts = array();
    if (isset($_COOKIE['layouts'])) {
        $layouts_cookie = $_COOKIE['layouts'];
    }

    if (isset($layouts_cookie)) {
        $tmp = json_decode($layouts_cookie, true);
        // Провераю корректность кодировки
        if (!$tmp) {
            $tmp = json_decode(iconv("CP1251", "UTF8", $layouts_cookie), true);
        }
    }

    if (isset($tmp)) {
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
            $clients_layouts[$client_mon_nr] = array(
                'layout_type' => $_data['t'],
                'layout_name' => $_data['n'],
                'CHANGE_TIME' => $_data['dd'],
                'CHANGE_USER' => $_data['u'],
                'CHANGE_HOST' => '',
                'PrintCamNames' => $_data['cn'],
                'AspectRatio' => $_data['p'],
                'ReconnectTimeout' => $_data['rt'],
                'isDefault' => $_data['d'],
                'wins' => json_decode($_data['w'], true)
            );
        }
    }

    ////->
    //Создание перечня готовых раскладок
    $client_mon_nr = 0;
    $client_counter = 1;
    print '<form onsubmit="return false;">';
    print "<div id='client_layouts'>";
    $is_clients_layout_default = false;
    //Если нет ни одной готовой раскладки
    if (!count($clients_layouts)) {
        $client_mon_nr = -1;
        print '<div> &nbsp;' . $no_any_layout . "</div>\n";
    }
    //Вывод готовых раскладок
    foreach ($clients_layouts as $client_mon_nr => $res_val) {
        print "<div style=\"border: 1px solid black; padding: 5px; height:310px; width: 290px; text-align:center;
                float:left; margin:10px; \">\n";
        if (array_key_exists($client_mon_nr, $clients_layouts)) {
            //левый монитор (правый вообще не используем)
            $def = ($clients_layouts[$client_mon_nr]['isDefault'] == 'true') ? 1 : 0;
            if ($def == 1) {
                $is_clients_layout_default = true;
            }
            prt_l(
                $client_mon_nr,
                $clients_layouts[$client_mon_nr],
                false,
                $layout_word,
                $client_counter,
                $clients_layouts[$client_mon_nr]['AspectRatio'],
                $clients_layouts[$client_mon_nr]['ReconnectTimeout'],
                $clients_layouts[$client_mon_nr]['PrintCamNames'],
                $def
            );
            print '<div class="camlayout">';

            //преобразование массива камер
            $cams_array = array();
            foreach ($clients_layouts[$client_mon_nr]['wins'] as $key => $val) {
                $cams_array[$key] = $val[0];
            }

            layout2table($clients_layouts[$client_mon_nr]['layout_type'], 160, $cams_array);

            print '</div>' . "\n";
        }
        print "</div>\n";
        $client_counter++;
    }
    print "</div>\n";
    //Выравниваем таблицы раскладок по центру элемента
    print '<script type="text/javascript"> $(".camlayout table").attr("align", "center");  </script>';

    $client_mon_nr = (int)$client_mon_nr;
    $client_mon_nr++;

    //Создать новую раскладку
    print "<div style='clear:left;'><br>\n";

    print '<a href="' . $conf['prefix'] . '/admin/web_mon_addnew.php?storage=client&mon_nr=' . $client_mon_nr
        . '&counter=' . $client_counter . '">' . $l_mon_addnew . '</a>' . "\n";

    print "</div>\n";
    print '<hr style="height:2px;border-width:0;color:gray;background-color:gray">'. "\n";
}
///////////////////////////////////////////////////////////////////////////////////////

if ($admin_user && (!isset($mon_nr) || $mon_nr == '')) {
    //раскладки определенные администратором
    echo '</form><form oncubmit="return false;"><h2>' . $r_mon_list . '</h2>' . "\n";
    /* Performing new SQL query */
    //Загрузка установленных раскладок
    $result = $adb->webGetLayouts();

    $LD = array();
    foreach ($result as $row) {
        $LD[(int)$row['MON_NR']] = array(
            'layout_type' => $row['MON_TYPE'],
            'layout_name' => $row['SHORT_NAME'],
            'CHANGE_TIME' => $row['CHANGE_TIME'],
            'CHANGE_USER' => $row['CHANGE_USER'],
            'CHANGE_HOST' => $row['CHANGE_HOST'],
            'PrintCamNames' => $row['PRINT_CAM_NAME'],
            'AspectRatio' => $row['PROPORTION'],
            'ReconnectTimeout' => $row['RECONNECT_TOUT'],
            'isDefault' => $row['IS_DEFAULT'],
            'wins' => json_decode($row['WINS'], true),
            'MON_NR' => $row['MON_NR'],
            'count_cells' => count($layouts_defs[$row['MON_TYPE']][3])
        );
        //print_r

    }
    function cmp($val1, $val2)
    {
        if ($val1['count_cells'] == $val2['count_cells']) {
            return 0;
        }
        return ($val1['count_cells'] < $val2['count_cells']) ? -1 : 1;
    }

    $sort = usort($LD, 'cmp');
    //Создание перечня готовых раскладок
    $mon_nr = 0;
    $counter = 1;
    print "<div id='admin_layouts'>";

    //Если нет ни одной готовой раскладки
    if (!count($LD)) {
        $mon_nr = -1;
        print '<div> &nbsp;' . $no_any_layout . "</div>\n";
    }
    //Вывод готовых раскладок
    foreach ($LD as $mon_nr => $res_val) {
        print "<div style=\"border: 1px solid black; padding: 5px; height:310px; width: 290px; text-align:center;
        float:left; margin:10px; \">\n";
        if (array_key_exists($mon_nr, $LD)) {
            //левый монитор (правый вообще не используем)
            $def = $LD[$mon_nr]['isDefault'];
            prt_l(
                $LD[$mon_nr]['MON_NR'],
                $LD[$mon_nr],
                $admin_user,
                $layout_word,
                $counter,
                $LD[$mon_nr]['AspectRatio'],
                $LD[$mon_nr]['ReconnectTimeout'],
                $LD[$mon_nr]['PrintCamNames'],
                $def
            );
            print '<div class=\'camlayout\' >';

            //преобразование массива камер
            $cams_array = array();
            foreach ($LD[$mon_nr]['wins'] as $key => $val) {
                $cams_array[$key] = $val[0];
            }

            layout2table($LD[$mon_nr]['layout_type'], 160, $cams_array);

            print '</div>' . "\n";
        }
        print "</div></form>\n";
        $counter++;
    }
    print "</div>\n";
    //Выравниваем таблицы раскладок по центру элемента
    print '<script type="text/javascript"> $(".camlayout table").attr("align", "center");  </script>';

    // Выбираю максимальный индекс раскладки в базе данных
    $max_mon_nr = 0;
    foreach ($LD as $key => $value) {
        $max_mon_nr = ($LD[$key]['MON_NR'] > $max_mon_nr) ? $LD[$key]['MON_NR'] : $max_mon_nr;
    }
    $mon_nr = $max_mon_nr + 1;

    //Создать новую раскладку
    print "<div style='clear:left;'><br>\n";
    if ($admin_user) {
        print '<a href="' . $conf['prefix'] . '/admin/web_mon_addnew.php?storage=server&mon_nr=' . $mon_nr
            . '&counter=' . $counter . '">' . $l_mon_addnew . '</a>' . "\n";
    } else {
        print '&nbsp;' . $l_mon_admin_only . "\n";
    }
    print "</div>\n";
}
require('../foot.inc.php');
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
