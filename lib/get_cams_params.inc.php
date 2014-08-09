<?php

/**
 *
 * @file lib/get_cams_params.inc.php
 * @brief Установка параметров настроек камер
 *
 * Формирует список камер, доступных пользователю, параметры этих камер и настройки по умолчанию
 *
 */

if (isset($GCP_cams_list) && (string)$GCP_cams_list === '') {
    die('not set cam list');
}
if (!isset($GCP_query_param_list) || !is_array($GCP_query_param_list)) {
    die('not set params list');
}
/// Параметры по умолчанию
$GCP_def_pars = array();
/// Номер камеры где используються настройки по умолчанию
$GCP_def_pars_nr = 0;
/// Параметры камер
$GCP_cams_params = array();
/// Количество камер в списке
$GCP_cams_nr = 0;

/// Список параметров
$GCP_sql_in_par = null;

/// ip пользователя
$_sip = ip2long($sip);
require_once($params_module_name);

for ($GCP_i = 0; $GCP_i < $PARAMS_NR; $GCP_i++) {
    $GCP_parname = $PARAMS[$GCP_i]['name'];
    if (!in_array($GCP_parname, $GCP_query_param_list)) {
        continue;
    }
    $GCP_def_pars[$GCP_parname] = $PARAMS[$GCP_i]['def_val'];
    if ($GCP_sql_in_par === null) {
        $GCP_sql_in_par = '\'' . $GCP_parname . '\'';
    } else {
        $GCP_sql_in_par .= ', \'' . $GCP_parname . '\'';
    }
}

/// получить данные из БД
$result = $adb->getCamParams($GCP_cams_list, $GCP_sql_in_par);
foreach ($result as $row) {
    $__cam_nr = intval($row['CAM_NR']);
    if ($__cam_nr > 0) {
        $GCP_cams_params[$__cam_nr][$row['PARAM']] = $row['VALUE'];
    } else {
        if (!is_empty_var($row['VALUE'])) {
            $GCP_def_pars[$row['PARAM']] = $row['VALUE'];
        }
    }
}

$result = null;

/*
echo "</script> <pre style='text-align: left;'>\n";
var_dump($GCP_sql_in_par);
var_dump($GCP_def_pars);
var_dump($GCP_cams_params);
echo "</pre>\n";
exit();
*/

/// Список камер с параметрами
$GCP_cams_list = array_keys($GCP_cams_params);
$GCP_cams_nr = count($GCP_cams_list);
if ($GCP_cams_nr) {
    reset($GCP_cams_list);
    foreach ($GCP_cams_list as $__cam_nr) {
        reset($GCP_def_pars);
        while (list($GCP_parname, $GCP_defval) = each($GCP_def_pars)) {
            if (!array_key_exists($GCP_parname, $GCP_cams_params[$__cam_nr]) ||
                is_empty_var($GCP_cams_params[$__cam_nr][$GCP_parname])) {
                $GCP_cams_params[$__cam_nr][$GCP_parname] = $GCP_defval;
            }
        }
    }
}
/*
echo "<pre style='text-align: left;'>\n";
var_dump($GCP_def_pars);
var_dump($GCP_cams_params);
echo "</pre>\n";
 */
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
