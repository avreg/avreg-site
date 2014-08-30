<?php

/**
 *
 * @file lib/get_cams_params.inc.php
 * @brief Получение значений заданных настроек заданных камер
 *
 * Формирует список камер, доступных пользователю, параметры этих камер и настройки по умолчанию
 *
 */

require_once($params_module_name);

function multiexplode ($delimiters, $string)
{
   
    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return  $launch;
}

function get_cams_params($params, $cams = null)
{
    global $PARAMS, $PARAMS_NR, $adb, $allow_cams;
    $ret_a = array();

    switch (gettype($params)) {
        case 'string':
            $parlist_a = multiexplode(
                array(' ', ', ', '. ', '| ', ': ', ',', '.', '|', ':'),
                $params
            );
            break;
        case 'array':
            $parlist_a = & $params;
            break;
        default:
            $parlist_a = array();
    }

    if (count($parlist_a) === 0) {
        die(__FUNCTION__ . "(): invalid argument params=\"$params\"");
    }

    /* обязатяльно добавляем параметр work,
     * который обязательно должен быть для любой камеры с номером > 0
     * иначе некоторые камеры могут не попасть в выборку из базы и соотв. в вых. массив */
    if (!in_array('work', $parlist_a)) {
        $parlist_a[] = 'work';
    }

    switch (gettype($cams)) {
        case 'integer':
            $camlist_a = array($cams);
            break;
        case 'array':
            $camlist_a = & $cams;
            break;
        default:
            $camlist_a = array(); // all cams
    }

    if (is_array($allow_cams) && count($allow_cams) > 0) {
        if (count($camlist_a) > 0) {
            $a = array_intersect($allow_cams, $camlist_a);
            $camlist_a = array_values($a);
        } else {
            $camlist_a = & $allow_cams;
        }
    }

    $avregd_def_a = array();
    $sql_in_par = null;
    for ($i = 0; $i < $PARAMS_NR; $i++) {
        $_pn = $PARAMS[$i]['name'];
        if (in_array($_pn, $parlist_a)) {
            $avregd_def_a[$_pn] =& $PARAMS[$i];
            if ($sql_in_par === null) {
                $sql_in_par = '\'' . $_pn . '\'';
            } else {
                $sql_in_par .= ', \'' . $_pn . '\'';
            }
        }
    }

    // получить данные из БД
    $result = $adb->getCamParams(implode(',', $camlist_a), $sql_in_par);
    foreach ($result as $row) {
        $__cam_nr = intval($row['CAM_NR']);
        $__val = trim($row['VALUE']);
        if ($__val !== '' && !is_null($__val)) {
            $ret_a[$__cam_nr][$row['PARAM']] = array(
                'v' => $__val, // value
                's' => 2      // value src: 0 - avregd, 1 - template, 2 - camera
            );
        }
    }
    if (!array_key_exists(0, $ret_a)) {
        /* если нет ни одного параметра для шаблонной камеры
         * а это может быть, т.к. в шаблонной базе устанавливаемой первоначально нет параметра work для камеры 0 */
        $ret_a[0]['work'] = array(
            'v' => 0,
            's' => 0
        );
        ksort($ret_a);
    }

    /// заполняем значения тех параметров, значений которых нет в базе данных
    /// дефолтами avregd
    /*
array(1) {
  ["work"]=>
  &array(8) {
    ["name"]=>
    string(4) "work"
    ["type"]=>
    int(1)
    ["def_val"]=>
    int(0)
    ["desc"]=>
    string(204) "Вкл./Выкл. видеозахвата с видеокамеры (читай: работать с этой камерой или нет). По умолчанию: Выкл."
    ["flags"]=>
    int(258)
    ["cats"]=>
    string(1) "1"
    ["subcats"]=>
    NULL
    ["mstatus"]=>
    int(2)
  }
}
     */
    foreach ($avregd_def_a as $_pn => $da) {
        $__val = $da['def_val'];
        switch ($da['type']) {
            case 0:
                $par_type = 'boolean';
                break;
            case 1:
                $par_type = 'integer';
                break;
            default:
                $par_type = 'string';
        }
        foreach ($ret_a as $__cam_nr => $a) {
            if (!array_key_exists($_pn, $a)) {
                if ($__cam_nr > 0) {
                    if (array_key_exists($_pn, $ret_a[0])) {
                        $template_val = $ret_a[0][$_pn];
                        if ($template_val['v'] === '' || is_null($template_val['v'])) {
                            $ret_a[$__cam_nr][$_pn] = array(
                                'v' => $__val,
                                's' => 0
                            );
                        } else {
                            $ret_a[$__cam_nr][$_pn] = array(
                                'v' => $template_val['v'],
                                's' => ($template_val['s'] === 2) ? 1 : 0
                            );
                        }
                    } else {
                        $ret_a[$__cam_nr][$_pn] = array(
                            'v' => $__val,
                            's' => 0
                        );
                    }
                } else {
                    $ret_a[$__cam_nr][$_pn] = array(
                        'v' => $__val,
                        's' => 0
                    );
                }
            }
            settype($ret_a[$__cam_nr][$_pn]['v'], $par_type);
        }
    }

    return $ret_a;
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
