<?php

/**
 * @file cron.php
 * @brief Обновление данных в таблице TREE_EVENTS
 *
 * @param '-s' - начало временного диапазона обновления. Если не указан - обновление будет произведено с момента
 *    первого соответстующего условиям события
 *    пример: sudo php ~/workspace/avreg-site/cron.php -s '2012-09-07 11:00:00'
 *    (будут обновлены все события и для всех камер, начиная с указанного даты и времени
 *    (минуты и секунды игнорируются, т.е. диапазон начинается с указанного часа, 0 минут, 0 секунд)).
 *
 * @param '-e' - конец временного диапазона обновления.  Если не указан - обновление будет произведено до последнего
 *    соответстующего условиям события
 *    пример: sudo php ~/workspace/avreg-site/cron.php -e '2012-09-07 12:00:00'
 *    (будут обновлены все события и для всех камер, заканчивая указанной датой, и временем до конца указанного часа
 *    (минуты и секунды игнорируются, т.е. диапазон заканчивается в указанный час, 59 минут, 59 секунд)).
 *
 * @param '-c' - номера камер для которых очсуществляется обновление.  Если не указан - обновление будет произведено
 *    для всех камер.
 *    пример:  sudo php ~/workspace/avreg-site/cron.php -c 2,3
 *    (будут обновлены все события для камер No. 2 и 3).
 *
 *    комплексный пример:    sudo php ~/workspace/avreg-site/cron.php -s '2012-09-07 09:00:00'
 *                             -e '2012-09-07 12:00:00' -c 2,3
 *    (будут обновлены все события для камер No. 2 и 3 в период времени начиная с 2012-09-07 09:00:00
 *    и заканчивая 2012-09-07 12:59:59).
 *
 * @param '-p' - название файла профиля с дополнительными настройками
 *
 * @return 0 - дерево синхронизировано
 *         1 - не смогли залочить обновление
 *         2 - дерево не синхронизировано
 */

$params = array();
if (in_array('-s', $argv) && isset($argv[array_search('-s', $argv) + 1])) {
    $params['start'] = $argv[array_search('-s', $argv) + 1];
}
if (in_array('-e', $argv) && isset($argv[array_search('-e', $argv) + 1])) {
    $params['end'] = $argv[array_search('-e', $argv) + 1];
}
if (in_array('-c', $argv) && isset($argv[array_search('-c', $argv) + 1])) {
    $params['cameras'] = $argv[array_search('-c', $argv) + 1];
}
if (in_array('-p', $argv) && isset($argv[array_search('-p', $argv) + 1])) {
    $profile = $argv[array_search('-p', $argv) + 1];
}

require(__DIR__ . '/lib/utils.php');
require('/etc/avreg/site-defaults.php');
$res = confparse($conf, 'avreg-site');
if (!$res) {
    die();
} else {
    $conf = array_merge($conf, $res);
}

if (!empty($profile) && $res = confparse($conf, 'avreg-site', $conf['profiles-dir'] . '/' . $profile)) {
    $conf = array_merge($conf, $res);
}

$link = null;
require_once($conf['site-dir'] . '/offline/gallery/memcache.php');
require_once($conf['site-dir'] . '/offline/gallery/gallery.php');
$non_config = true;
require_once($conf['site-dir'] . '/lib/adb.php');

// Инициализация класа галереи
$params['method'] = 'cliUpdateTreeEvents';
$gallery = new \Avreg\Gallery($params);
$ret = $gallery->cliUpdateTreeEvents($params);

exit($ret);
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
