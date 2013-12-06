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
 * Это осуществляется вызовом метода getTreeEvents($param) в offline/gallery/js/main.js .
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
 * Выполняется вызовом метода updateTreeEvents($param),
 * которому в качестве параметров передаются начало(start) и конец(end) временного диапазона,
 * а так же номера камер(cameras) для которых надо выполнить обновление
 *
 *
 * */

namespace Avreg;

class Gallery
{
    public $method = ''; // метод запроса
    public $result = array(); // ответ запроса
    private $cache;
    private $db = '';
    private $limit = 0;
    private $conf = array(); // настройки галереи

    // конструктор класса
    public function __construct($param)
    {
        // получение параметров запроса
        foreach ($param as $k => $v) {
            if (isset($this->$k) && !in_array($k, array('db', 'conf'))) {
                $this->$k = $v;
            }
        }
        // Получение глобальных настроек сайта
        global $conf;
        $this->conf = $conf;
        $this->cache = new Cache();
        if (!$this->limit) {
            $this->limit = $this->conf['gallery-limit'];
        }
        global $adb;
        $this->db = $adb;

        // Если существует запрашиваемый метод, то его выполняем с указанными параметрами
        if (!empty($this->method) && method_exists($this, $this->method)) {
            $this->{$this->method}($param);
        }
    }

    // Функция получения событий
    public function getEvents($param)
    {
        $events = array();
        // если есть список камер, то выполняем запрос
        if (isset($param['cameras']) && !empty($param['cameras'])) {
            $cameras = trim($param['cameras'], ',');
            $param['cameras'] = explode(",", $cameras);

            $type = explode(",", trim($param['type'], ','));

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
                'cameras' => $param['cameras'],
                'events' => $EVT_ID,
                'date' => $param['tree'] !== 'all' ? explode('_', $param['tree']) : array(),
                'limit' => $this->limit,
                'offset' => $param['sp'],
            );
            //$events = $this->db->galleryGetEvent($p);
            if ($this->limit > 1) {
                $events = $this->db->galleryGetEvent($p);
                // Сохранение результата
                $this->result = array('events' => $events);
            } else {
                $date = $this->db->galleryGetEventDate($p);
                // Сохранение результата
                $this->result = $date;
            }
        }
    }

    // Функция построения дерева события
    public function getTreeEvents($param)
    {

        $update = !isset($param['update']) || $param['update'] === true ? true : false;
        $update_last = isset($param['last']) && $param['last'] ? true : false;
        $gallery_update = $this->cache->get('gallery_update');
        //если древо заблокировано, то возвращаем ошибку
        if ($gallery_update) {
            $this->result = array('status' => 'error', 'code' => '3', 'description' => 'Дерево заблокированно');
            return;
        }


        global $GCP_cams_params;
        $cameras = implode(',', array_keys($GCP_cams_params));


        $count_event = $this->db->galleryGetCountEvent(array('cameras' => array_keys($GCP_cams_params)));
        $count_tree_event = $this->db->galleryGetCountTreeEvent(array('cameras' => array_keys($GCP_cams_params)));
        $update_tree = false;
        // сравниваем количество событий в дереве и в событиях если не равно то
        if ($count_event !== $count_tree_event) {
            $last_event_date = $this->db->galleryGetLastEventDate(array('cameras' => array_keys($GCP_cams_params)));
            $last_tree_date = $this->db->galleryGetLastTreeEventDate(array('cameras' => array_keys($GCP_cams_params)));
            //обновляем дерево последними событиями если:
            //1- пришел принудительный запрос
            //2- последний час в дереве и событиях не совпадает
            if ($update && (($update_last && $last_tree_date <= $last_event_date) ||
                    date('Y-m-d-H', strtotime($last_tree_date)) !== date('Y-m-d-H', strtotime($last_event_date)))) {
                $this->cache->set('gallery_update', true);
                $evt_updt_rst = $this->db->galleryUpdateTreeEvents(
                    $last_tree_date,
                    $last_event_date,
                    array(),
                    $param["on_dbld_evt"]
                );
                $this->cache->delete('gallery_update');
                //проверка дублей событий
                if ($evt_updt_rst['status'] == 'error') {
                    $this->result = $evt_updt_rst;
                    return;
                }

                $count_event = $this->db->galleryGetCountEvent(array('cameras' => array_keys($GCP_cams_params)));
                $count_tree_event = $this->db->galleryGetCountTreeEvent(
                    array('cameras' => array_keys($GCP_cams_params))
                );
                $last_tree_date = $last_event_date;
            }
            // если до сих пор дерево рассинхроннизированно
            if ($count_event !== $count_tree_event) {
                $count_last_event = $this->db->galleryGetCountEvent(
                    array('cameras' => array_keys($GCP_cams_params), 'date' => $last_tree_date)
                );
                $count_last_tree_event = $this->db->galleryGetCountTreeEvent(
                    array('cameras' => array_keys($GCP_cams_params), 'date' => $last_tree_date)
                );
                //если рассинхронизация из-за последнего часа, то не возвращать ошибку
                if ($count_event == $count_tree_event + (abs($count_last_tree_event - $count_last_event))) {
                    $update_tree = true;
                } else {
                    $oldest_event_date = $this->db->galleryGetOldestEventDate(
                        array('cameras' => array_keys($GCP_cams_params))
                    );
                    $oldest_tree_date = $this->db->galleryGetOldestTreeEventDate(
                        array('cameras' => array_keys($GCP_cams_params))
                    );

                    $access_update_tree = in_array(
                        $GLOBALS['user_status'],
                        array($GLOBALS['install_status'], $GLOBALS['admin_status'], $GLOBALS['arch_status'])
                    );
                    $this->result = array(
                        'status' => 'error',
                        'code' => '4',
                        'description' => 'Дерево не синхронизированно',
                        'count_event' => $count_event,
                        'count_tree_event' => $count_tree_event,
                        'last_event_date' => $last_event_date,
                        'last_tree_date' => $last_tree_date,
                        'oldest_event_date' => $oldest_event_date,
                        'oldest_tree_date' => $oldest_tree_date,
                        'access_update_tree' => $access_update_tree
                    );
                    return;
                }
            }
        } else {
            $last_tree_date = $this->db->galleryGetLastTreeEventDate(array('cameras' => array_keys($GCP_cams_params)));
        }

        // выходим если не нужно вернуть дерево
        if (!$update || ($update_last && !$update_tree)) {
            $this->result = array(
                'update_tree' => $update_tree,
                'last_tree_date' => $last_tree_date,
                'status' => 'success'
            );
            return;
        }

        // получаем дерево из кеша, если его нет, то из базы, результат помещаем в кеш.
        $key = md5($cameras . '-' . $last_tree_date);
        $tree_events_result = $this->cache->get($key);
        if ($update_tree || empty($tree_events_result)) {

            $tree_events_result = $this->db->galleryGetTreeEvents(array('cameras' => array_keys($GCP_cams_params)));
            if (!$this->cache->check($key)) {
                $this->cache->lock($key);
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
            $this->result = array('status' => 'error', 'code' => '0', 'description' => 'No events.', 'qtty' => 0);
        }

        $this->result = array(
            'tree_events' => $tree_events_result,
            'cameras' => $GCP_cams_params,
            'update_tree' => $update_tree,
            'last_tree_date' => $last_tree_date,
            'status' => 'success'
        );

    }

    // функция обновления дерева события
    public function updateTreeEvents($param)
    {
        // проверяем может идет обновление
        if ($this->cache->get('gallery_update')) {
            return 1;
        }
        $start = isset($param['start']) ? $param['start'] : false;
        $end = isset($param['end']) ? $param['end'] : false;
        $cameras = isset($param['cameras']) ? $param['cameras'] : false;
        // устанавливаем блокировку
        $this->cache->set('gallery_update', true);
        // обновляем
        $this->db->galleryUpdateTreeEvents($start, $end, $cameras);

        // удаляем сохраненный в мемкеше деревья
        $tree_events_keys = $this->cache->get('tree_events_keys');
        if (!empty($tree_events_keys)) {
            foreach ($tree_events_keys as $key) {
                $this->cache->delete($key);
            }
        }
        // снимаем блокировку
        $this->cache->delete('gallery_update');
        return 0;
    }

    // функция запускается по крону, чтобы обновить последние события в дереве
    public function cronUpdateTreeEvents()
    {
        // последенее событие
        $last_event_date = $this->db->galleryGetLastEventDate();
        // последнее событие в дереве
        $last_tree_date = $this->db->galleryGetLastTreeEventDate();
        // обновляем дерево если оно не полное
        if ($last_tree_date < $last_event_date) {
            $params = array(
                'start' => $last_tree_date,
                'end' => $last_event_date
            );
            return $this->updateTreeEvents($params);
        }
    }

    // функция полного обновления деоева события
    public function reindexTreeEvents($params)
    {
        global $GCP_cams_params;
        $param['cameras'] = implode(',', array_keys($GCP_cams_params));
        $this->updateTreeEvents($params);
        $this->getTreeEvents($params);
    }

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
    }
}
