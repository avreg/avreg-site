<?php

/**
 * @file offline/gallery/gallery.php
 * @brief класс служит для получения и обновления данных о событиях
 * инстанцируется:
 * <ol>
 * <li> в offline/gallery.php - при отображении галереи
 * <li> в cron.php - для обновления данных дерева событий
 * </ol>
 *
 * информация в таблице tree_events обновляется при открытии
 * галереи начиная с времени последнего обновления и заканчивая
 * последним событием в events.
 * Это осуществляется вызовом метода getTreeEvents($par_hash) в offline/gallery/js/main.js .
 *
 * cron.php при открытии галереи вообще не используется.
 *
 * cron.php - предоставляет возможность выполнять обновление этой
 * таблицы по некоторому расписанию, например в crontab.
 * Что это дает:
 *
 * 1. галерея открывается быстрей, поскольку для обновления надо
 * обработать меньшее кол-во данных
 * (может быть актуально при редком открытии галереи и большом кол-ве камер).
 * Выполняется вызовом метода cronUpdateTreeEvents().
 *
 * 2. после работы чистильщика, надо обновить tree_events для
 * того периода, для которого были удалены файлы.
 * (это необходимо, поскольку, как было сказано, при открытии галереи
 * таблица обновляется начиная с времени последнего обновления,
 * а не полностью с самого начала).
 * Выполняется вызовом метода updateTreeEvents($par_hash),
 * которому в качестве параметров передаются начало(start) и конец(end) временного диапазона,
 * а так же номера камер(cameras) для которых надо выполнить обновление
 *
 *
 * */

namespace Avreg;

class Gallery
{
    const UPDATE_TREE_WAIT = 10; ///< время ожидания блокировки обновления дерева

    /***
     * TreeEvents vs Events resync cases
     */
    const NO_DIFFER = 0x0;
    const HEAD_TIME_DIFFER = 0x01;
    const TAIL_TIME_DIFFER = 0x02;
    const COUNT_DIFFER = 0x04;

    public $method = ''; // метод запроса
    public $result = array(); // ответ запроса
    private $cache;
    private $db = '';
    private $limit = 0;
    private $conf = array(); // настройки галереи

    // конструктор класса
    public function __construct($par_hash)
    {
        // получение параметров запроса
        foreach ($par_hash as $k => $v) {
            if (isset($this->$k) && !in_array($k, array('db', 'conf'))) {
                $this->$k = $v;
            }
        }
        // Получение глобальных настроек сайта
        global $conf;
        $this->conf = $conf;
        $this->cache = new Cache('gallery', !@empty($conf['debug']));
        if (!$this->limit) {
            $this->limit = $this->conf['gallery-limit'];
        }
        global $adb;
        $this->db = $adb;

        // Если существует запрашиваемый метод, то его выполняем с указанными параметрами
        if (!empty($this->method) && method_exists($this, $this->method)) {
            $this->{$this->method}($par_hash);
        }
    } /* __construct() */

    // Функция получения событий
    public function getEvents($par_hash)
    {
        $events = array();
        // если есть список камер, то выполняем запрос
        if (isset($par_hash['cameras']) && !empty($par_hash['cameras'])) {
            $cameras = trim($par_hash['cameras'], ',');
            $par_hash['cameras'] = explode(",", $cameras);

            $type = explode(",", trim($par_hash['type'], ','));

            // картинки
            $EVT_ID = array();
            if (in_array('image', $type)) {
                $EVT_ID = array_merge($EVT_ID, array(15, 16, 17));
            }
            // видео
            if (in_array('video', $type)) {
                $EVT_ID = array_merge($EVT_ID, array(23));
            }
            // аудио
            if (in_array('audio', $type)) {
                $EVT_ID = array_merge($EVT_ID, array(32));
            }
            // видео+audio
            if (in_array('video', $type) || in_array('audio', $type)) {
                $EVT_ID = array_merge($EVT_ID, array(12));
            }

            $p = array(
                'cameras' => $par_hash['cameras'],
                'events' => $EVT_ID,
                'date' => $par_hash['tree'] !== 'all' ? explode('_', $par_hash['tree']) : array(),
                'limit' => $this->limit,
                'offset' => $par_hash['sp'],
                'to' => $par_hash['to']
            );
            //$events = $this->db->galleryGetEvent($p);
            if ($this->limit > 1) { // FIXME limit = 1 при навигации по скролу вроде, для всплываюшего ока
                $events = $this->db->galleryGetEvent($p);
                // Сохранение результата
                $this->result = array('events' => $events, 'to' => $par_hash['to']);
            } else {
                $date = $this->db->galleryGetEventDate($p);
                // Сохранение результата
                $this->result = $date;
            }
        }
    } /* getEvents() */

    // Функция построения дерева события
    public function getTreeEvents($params, $no_wait_lock = false)
    {
        // устанавливаем блокировку
        if (!$this->cache->lockAtomicWait('gallery_update', $no_wait_lock ? 0 : self::UPDATE_TREE_WAIT)) {
            // если древо заблокировано, то возвращаем ошибку
            $this->result = array(
                'status' => 'error',
                'code' => '3',
                'description' => 'getTreeEvents() is locked by "gallery_update" lock'
            );
            return;
        }

        try {
            $initially = isset($params['initially']);
            if (@empty($params['to'])) {
                $params['to'] = date("Y-m-d H:i:s", time() - 1);
            }

            global $cams_params; // offline/gallery.php
            global $cams_array;  // offline/gallery.php
            $cameras = implode(',', $cams_array);

            $params['cameras'] = $cams_array; // FIXME FIXME getTreeEvents() только со всеми камерами работает?

            $events_stat = $this->db->galleryEventsGetStat($params);
            $tree_events_stat = $this->db->galleryTreeEventsGetStat($params);

            if ($events_stat['files'] != $tree_events_stat['files']  ||
                $tree_events_stat['latest_update'] != $events_stat['latest'] ||
                $events_stat['oldest'] != $tree_events_stat['oldest_update']) {
                /* need update tree_events */
                $access_update_tree = in_array(
                    $GLOBALS['user_status'],
                    array($GLOBALS['install_status'], $GLOBALS['admin_status'], $GLOBALS['arch_status'])
                );
                $this->result = array(
                    'status' => 'error',
                    'code' => '4',
                    'description' => 'Not sync',
                    'count_event' => $events_stat['files'],
                    'count_tree_event' => $tree_events_stat['files'],
                    'last_event_date' => $events_stat['latest'],
                    'last_tree_date' => $tree_events_stat['latest_update'],
                    'oldest_event_date' => $events_stat['oldest'],
                    'oldest_tree_date' => $tree_events_stat['oldest_update'],
                    'access_update_tree' => $access_update_tree,
                    'to' => $params['to']
                );
                $this->cache->delete('gallery_update');
                return;
            }

            if (!$initially) {
                // возвращаем без данных и не ошибку как признак того что данные не изменились
                $this->result = array(
                    'status' => 'success',
                    'count_event' => $events_stat['files'],
                    'count_tree_event' => $tree_events_stat['files'],
                    'last_event_date' => $events_stat['latest'],
                    'last_tree_date' => $tree_events_stat['latest_update'],
                    'oldest_event_date' => $events_stat['oldest'],
                    'oldest_tree_date' => $tree_events_stat['oldest_update'],
                    'to' => $params['to']
                );
                $this->cache->delete('gallery_update');
                return;
            }
            // получаем дерево из кеша, если его нет, то из базы, результат помещаем в кеш.
            $key = md5($cameras . '-' . $tree_events_stat['latest_update']);
            $tree_events_result = $this->cache->get($key);
            if (empty($tree_events_result)) {
                $tree_events_result = $this->db->galleryGetTreeEvents($params);
                if (!$this->cache->check($key)) {
                    $this->cache->lock($key); // FIXME для чего нужен этот lock?
                    $tree_events_keys = $this->cache->get('tree_events_keys');
                    if (empty($tree_events_keys)) {
                        $tree_events_keys = array();
                    }
                    $tree_events_keys[] = $key;
                    $this->cache->set($key, $tree_events_result);
                    $this->cache->set('tree_events_keys', $tree_events_keys);
                }
            }

            // возвращаем результат
            if (empty($tree_events_result)) {
                $this->result = array(
                    'status' => 'error',
                    'code' => '0',
                    'description' => 'No events.',
                    'qtty' => 0,
                    'to' => $params['to']
                );
                $this->cache->delete('gallery_update');
                return;
            }

            $this->result = array(
                'status' => 'success',
                'count_event' => $events_stat['files'],
                'count_tree_event' => $tree_events_stat['files'],
                'last_event_date' => $events_stat['latest'],
                'last_tree_date' => $tree_events_stat['latest_update'],
                'oldest_event_date' => $events_stat['oldest'],
                'oldest_tree_date' => $tree_events_stat['oldest_update'],
                'to' => $params['to'],
                'tree_events' => $tree_events_result,
                'cameras' => $cams_params
            );
        } catch (\Exception $e) {
            $this->cache->delete('gallery_update');
            throw $e;
        }
        $this->cache->delete('gallery_update');
    } /* getTreeEvents() */

    // функция обновления дерева события
    public function updateTreeEvents($par_hash)
    {
        $ret = 2; // not sync

        // устанавливаем блокировку
        if (!$this->cache->lockAtomicWait('gallery_update', self::UPDATE_TREE_WAIT)) {
            return 1;
        }

        $start = isset($par_hash['start']) ? $par_hash['start'] : false;
        $end = isset($par_hash['end']) ? $par_hash['end'] : false;
        $cameras = isset($par_hash['cameras']) ? $par_hash['cameras'] : false;
        if (@empty($par_hash['to'])) {
            $par_hash['to'] = date("Y-m-d H:i:s", time() - 1);
        }

        $update_invoke_db_nb = 0;
        try {
            if ($start || $end) {
                // вызов из CLI || avreg-unlink, обновляем без вариантов
                $this->db->galleryUpdateTreeEvents($start, $end, $par_hash['to'], $cameras);
                $ret = 0; // FIXME без проверки?
                $update_invoke_db_nb++;
            } else {
                // вызов запросом браузера || cron
                // будем использовать умную индексацию
                // TODO остаётся один неохваченный вариант, редкий но возможный
                // когда удалили одно и тоже количество в EVENTS: не последнюю
                // (или несколько в одной самой старой секунде было в конце)
                // и добавили в ту же секунду новые
                // тогда времена и количество не изменятся, однако будет рассинхрон

                /* head = tail = 1, total = 3
                 * head(1) + 2 x tail(1)  = 3
                 * head(1) + tail(1)      = 2
                 * head(1) + tail(1) + total(3) = 5
                 * total(3) + total(3) = 6
                 */
                $max_reindex_score = 3; // допускаем только один total за раз

                $sync_done = false;
                $resync_score = 555;
                for ($i = 0; $i < $max_reindex_score; $i += $resync_score) {
                    $events_stat = $this->db->galleryEventsGetStat($par_hash);
                    $tree_events_stat = $this->db->galleryTreeEventsGetStat($par_hash);

                    $resync_cases = array();
                    if ($events_stat['latest'] != $tree_events_stat['latest_update']) {
                        $resync_cases[] = self::HEAD_TIME_DIFFER;
                    }
                    if ($events_stat['oldest'] != $tree_events_stat['oldest_update']) {
                        $resync_cases[] = self::TAIL_TIME_DIFFER;
                    }
                    if ($events_stat['files'] != $tree_events_stat['files']) {
                        $resync_cases[] = self::COUNT_DIFFER;
                    }

                    if (count($resync_cases) === 0) {
                        // синхронизировано!!!
                        $ret = 0;
                        break;
                    } elseif (in_array(self::HEAD_TIME_DIFFER, $resync_cases) && $tree_events_stat['files'] > 0) {
                        $resync_score = 1;
                        $start = $this->sqlTimestampMin($events_stat['latest'], $tree_events_stat['latest_update']);
                        $end = false;
                    } elseif (in_array(self::TAIL_TIME_DIFFER, $resync_cases) && $tree_events_stat['files'] > 0) {
                        $resync_score = 1;
                        $end = $this->sqlTimestampMax($events_stat['oldest'], $tree_events_stat['oldest_update']);
                        $start = false;
                    } else {
                        // нужна полная синхронизация
                        $resync_score = 3;
                        $start = $end = false;
                    }

                    // обновляем
                    // error_log("resync_score = $resync_score start=\"$start\"; end=\"$end\"");
                    $this->db->galleryUpdateTreeEvents($start, $end, $par_hash['to'], $cameras);
                    $update_invoke_db_nb++;
                }
            }

            if ($update_invoke_db_nb > 0) {
                $tree_events_keys = $this->cache->get('tree_events_keys');
                if (!empty($tree_events_keys)) {
                    foreach ($tree_events_keys as $key) {
                        $this->cache->delete($key);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->cache->delete('gallery_update');
            throw $e;
        }

        // снимаем блокировку
        $this->cache->delete('gallery_update');
        return $ret;
    } /* updateTreeEvents() */

    // функция запускается по крону, чтобы обновить последние события в дереве
    public function cliUpdateTreeEvents($par_hash)
    {
        $par_hash['to'] = date("Y-m-d H:i:s", time() - 1);
        return $this->updateTreeEvents($par_hash);
    } /* cronUpdateTreeEvents() */

    // функция полного обновления деоева события
    public function reindexTreeEvents($par_hash)
    {
        global $cams_array; // offline/gallery.php
        $par_hash['cameras'] = implode(',', $cams_array);
        $par_hash['to'] = date("Y-m-d H:i:s", time() - 1);
        $this->updateTreeEvents($par_hash);
        $par_hash['initially'] = 'yes'; // чтобы getTreeEvents() возвратил данные
        $this->getTreeEvents($par_hash, true /* no wait lock так как уже ждали в updateTreeEvents() выше */);
    } /* reindexTreeEvents() */

    // отдача результата клиенту
    public function printResult()
    {
        if ($this->limit > 1) {
            echo json_encode($this->result);
        } else {
            if (is_array($this->result)) {
                echo 'is array result';
            } else {
                echo $this->result;
            }
        }
    } /* printResult() */

    public function sqlTimestampMin($ts1, $ts2)
    {
        if (strtotime($ts1) < strtotime($ts2)) {
            return $ts1;
        } else {
            return $ts2;
        }
    }
    public function sqlTimestampMax($ts1, $ts2)
    {
        if (strtotime($ts1) > strtotime($ts2)) {
            return $ts1;
        } else {
            return $ts2;
        }
    }
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
