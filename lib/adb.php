<?php

/**
 *
 * @file lib/adb.php
 * @brief В файле реализован класс, который обеспечивает взаимодействие с БД,<br />а также инициализируется экземпляр
 * этого класса
 *
 * Все обращения к БД должны быть реализованы посредством этого класса,
 * а работа с БД в обход этого класса крайне нежелательна.
 *
 * Для инициализации экземпляра класса используется объект конфигурации $conf из /etc/avreg/site-defaults.php
 *
 * Для подключения к БД класс использует /usr/share/php/DB.php
 *
 */

namespace Avreg;

require_once('DB.php');

if (empty($non_config)) {
    require_once('config.inc.php');
}

/// Инициализируем класс по работе с БД
$adb = new Adb($conf);

/**
 * @class Adb
 * @brief Класс взаимодействия с БД
 *
 */
class Adb
{
    // Название БД
    private $database = '';
    /// Пользователь БД
    private $user = '';
    /// Пароль БД
    private $password = '';
    /// Тип БД mysql - MySql, pgsql - PostgreSql
    private $dbtype = 'mysql';
    /// Хост БД
    private $host = 'localhost';
    /// Объект для работы с БД
    private $db = false;
    ///Объект PEAR
    private $pear = false;


    /**
     *  Конструктор по умолчанию
     * Устанавливает соединение с БД
     * @params array $params масив конфигурации класса, передаётся авреговый $conf
     * @return \Avreg\Adb если соединение с базой успешно, false если произошла ошибка.
     */
    public function __construct($params)
    {
        $this->database = $params['db-name'];
        $this->user = $params['db-user'];
        $this->password = $params['db-passwd'];
        if (isset($params['db-type']) && !empty($params['db-type'])) {
            $this->dbtype = $params['db-type'];
        }
        if (isset($params['db-host']) && !empty($params['db-host'])) {
            $this->host = $params['db-host'];
        }

        //$this->_host ='localhost';

        $this->pear = new \PEAR();

        $dsn = "{$this->dbtype}://{$this->user }:{$this->password}@{$this->host}/{$this->database}";

        $this->pear = new \PEAR();

        $db = new \DB();
        $this->db = $db->connect($dsn, true);

        //$this->_db = DB::connect($dsn,true);

        $this->error($this->db);

        if ($this->dbtype == 'mysql') {
            $res = $this->db->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
        } else {
            $res = $this->db->query("SET NAMES 'utf8'");
        }
        $this->error($res);
        return true;
    }

    /**
     *  Деструктор по умолчанию
     * Закрывает соединение с БД
     */
    public function __destruct()
    {
        //if (!PEAR::isError($this->_db))
        if (!$this->pear->isError($this->db)) {
            $this->db->disconnect();
        }
    }

    /**
     *  Проверка на ошибку в запросе к БД.
     *
     * @param object $r Объект запроса
     * @param bool $die true - закончить скрипт, false - вывести ошибку
     *
     * @return true - если ошибка, false - если нет ошибок
     */
    public function error($r, $die = true)
    {
        // if (PEAR::isError($r)) {
        if ($this->pear->isError($r)) {
            $ui = $r->getUserInfo();
            @header('Content-Type: text/text; charset=' . $GLOBALS['chset']);

            echo 'DBMS/User Message: ' . $ui;

            // echo $r->getDebugInfo();

            if ($die) {
                trigger_error($ui);
                throw new \Exception($ui);
            }

            return true;
        }
        return false;
    }

    /**
     *  Метод позволяет получить события по указанным параметрам
     *
     *
     * @param array $param Параметры
     * - $param['events'] тип событий событий (изображения, аудио, видео)
     * - $param['cameras']  список камер
     * - $param['date'] дата событий c часовой градацией
     * - $param['to']   точные ограничители DT1
     * - $param['from']
     * - $param['limit']
     * - $param['offset']
     *
     * @return array масив событий
     */

    public function galleryGetEvent($param)
    {
        $events = array();
        $query = "select " . $this->dateFormat('DT1') . ", DT1, EVT_CONT, ALT2, ALT1, CAM_NR, FILESZ_KB, EVT_ID, " .
            $this->timediff('DT1', 'DT2') . ", DT2";
        $query .= ' from EVENTS';
        $query .= ' where ' . $this->whereIntColumnValue("EVT_ID", $param['events']);
        $query .= ' and ' . $this->whereIntColumnValue("CAM_NR", $param['cameras']);

        if (!@empty($param['from'])) {
            $query .= " and DT1 >= '" . $param['from'] . "'";
        }
        if (!@empty($param['to'])) {
            $query .= " and DT1 <= '" . $param['to'] . "'";
        }

        if (isset($param['date'][0])) {
            $query .= ' and ' . $this->datePart('year', 'DT1') . '= ' . $param['date'][0];
        }

        if (isset($param['date'][1])) {
            $query .= ' and ' . $this->datePart('month', 'DT1') . '= ' . $param['date'][1];
        }

        if (isset($param['date'][2])) {
            $query .= ' and ' . $this->datePart('day', 'DT1') . '= ' . $param['date'][2];
        }

        if (isset($param['date'][3])) {
            $query .= ' and ' . $this->datePart('hour', 'DT1') . '= ' . $param['date'][3];
        }

        // сортировать по дате, от текущей позиции с лимитом заданный в конфиге
        $query .= ' order by DT1 asc limit ' . $param['limit'] . ' offset ' . $param['offset'];

        $res = $this->db->query($query);

        $this->error($res);
        while ($res->fetchInto($line)) {
            $line[6] = filesizeHuman($line[6]);
            if (in_array((int)$line[7], array(15, 16, 17, 18, 19, 20, 21))) {
                $line[7] = 'image';
            } elseif ((int)$line[7] == 23) {
                $line[7] = 'video';
            } elseif ((int)$line[7] == 12) {
                $line[7] = 'video';
            } elseif ((int)$line[7] == 32) {
                $line[7] = 'audio';
            }

            // формирование уникального индекса, для работы кэша в браузере пользователя
            // TODO слишком длинный ключ
            $events[str_replace(array('/', '.'), '_', $line[5] . '_' . $line[2] . '_' . $line[0])] = $line;
        }

        return $events;
    }

    /**
     *  Метод позволяет получить дату текущего события
     *  FIXME вроде исп. для навигации по скролу (всплывающее окно)
     *
     * @param array $param Параметры
     * - $param['events'] тип событий событий (изображения, аудио, видео)
     * - $param['cameras']  список камер
     * - $param['date'] дата событий
     * - $param['limit']
     * - $param['offset']
     *
     * @return array масив событий
     */

    public function galleryGetEventDate($param)
    {
        $events = array();
        $query = "select " . $this->dateFormat('DT2') . ", DT2";
        $query .= ' from EVENTS';
        $query .= ' where ' . $this->whereIntColumnValue("EVT_ID", $param['events']);
        $query .= ' and ' . $this->whereIntColumnValue("CAM_NR", $param['cameras']);

        if (!@empty($param['from'])) {
            $query .= " and DT1 >= '" . $param['from'] . "'";
        }
        if (!@empty($param['to'])) {
            $query .= " and DT1 <= '" . $param['to'] . "'";
        }
        if (isset($param['date'][0])) {
            $query .= ' and ' . $this->datePart('year', 'DT1') . '= ' . $param['date'][0];
        }

        if (isset($param['date'][1])) {
            $query .= ' and ' . $this->datePart('month', 'DT1') . '= ' . $param['date'][1];
        }

        if (isset($param['date'][2])) {
            $query .= ' and ' . $this->datePart('day', 'DT1') . '= ' . $param['date'][2];
        }

        if (isset($param['date'][3])) {
            $query .= ' and ' . $this->datePart('hour', 'DT1') . '= ' . $param['date'][3];
        }

        // сортировать по дате, от текущей позиции с лимитом заданный в конфиге
        $query .= ' order by DT1 asc limit ' . $param['limit'] . ' offset ' . $param['offset'];

        $res = $this->db->query($query);

        $this->error($res);

        $res->fetchInto($line);

        $date_events = $line[1];

        return $date_events;
    }

    /**
     *  Метод позволяет получить статистику о файловых событиях в таблицe EVENTS
     *
     * @param array $param Параметры
     * - $params['cameras']  список камер
     * - $params['from']     дата from (from >= DT1)
     * - $params['to']       дата to (to <= DT1)
     * @return array         with fields: files, latest and oldest
     */
    public function galleryEventsGetStat($params = array())
    {
        $stat = array(
            'files' => 0,
            'latest' => '1970-01-01 00:00:00',
            'oldest' => '1970-01-01 00:00:00'
        );

        $query = 'select COUNT(*) as files, MAX(DT1) as oldest, MIN(DT1) as latest from EVENTS';
        $query .= ' where EVT_ID in (12, 15,16,17, 23, 32)';
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                switch ($key) {
                    case 'cameras':
                        $query .= ' and ' . $this->whereIntColumnValue("CAM_NR", $value);
                        break;
                    case 'from':
                        $query .= " and DT1 >= '$value'";
                        break;
                    case 'to':
                        $query .= " and DT1 <= '$value'";
                        break;
                    case 'method':
                    case 'initially':
                        break;
                    default:
                        die("galleryEventsGetStat() failed: unknown param \"$key\" = \"$value\"");
                }
            }
            unset($key, $value);
        }

        $res = $this->db->query($query);
        $this->error($res);
        if ($res->fetchInto($line) && !empty($line[0])) {
            $stat['files'] = (int)$line[0];
            $stat['latest'] = $line[1];
            $stat['oldest'] = $line[2];
        }
        unset($res, $line);
        return $stat;
    } /* galleryEventsGetStat() */

    /**
     *  Метод позволяет получить статистику о дереве событий
     *
     * @param array $param Параметры
     * - $params['cameras']  список камер
     * - $params['from']     дата from (from >= DT1_MIN)
     * - $params['to']       дата to (to <= DT1_MAX)
     * @return array         with fields: files, latest_update and oldest_update
     */
    public function galleryTreeEventsGetStat($params = array())
    {
        $stat = array(
            'files' => 0,
            'latest_update' => '1970-01-01 00:00:00',
            'oldest_update' => '1970-01-01 00:00:00'
        );

        $query = 'select SUM(IMAGE_COUNT+VIDEO_COUNT+AUDIO_COUNT) as files,';
        $query .= ' MAX(DT1_MAX) as latest_update, MIN(DT1_MIN) as oldest_update';
        $query .= ' from TREE_EVENTS';
        if (!empty($params)) {
            $query .= ' where 1=1';

            foreach ($params as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                switch ($key) {
                    case 'cameras':
                        $query .= ' and ' . $this->whereIntColumnValue("CAM_NR", $value);
                        break;
                    case 'from':
                        $query .= " and DT1_MIN >= '$value'";
                        break;
                    case 'to':
                        $query .= " and DT1_MAX <= '$value'";
                        break;
                    case 'method':
                    case 'initially':
                        break;
                    default:
                        die("galleryTreeEventGetStat() failed: unknown param \"$key\" = \"$value\"");
                }
            }
            unset($key, $value);
        }

        $res = $this->db->query($query);
        $this->error($res);
        if ($res->fetchInto($line) && !empty($line[0])) {
            $stat['files'] = (int)$line[0];
            $stat['latest_update'] = $line[1];
            $stat['oldest_update'] = $line[2];
        }
        unset($res, $line);
        return $stat;
    } /* galleryTreeEventGetStat() */


    /***
     *  Метод позволяет получить дерево событий
     *
     * @param array $param Параметры
     * - $params['cameras']  список камер
     * - $params['from']     дата from (from >= DT1_MIN)
     * - $params['to']       дата to (to <= DT1_MAX)
     * @return array масив дерева событий со статистикой
     */
    public function galleryGetTreeEvents($params)
    {
        $tree_events_result = array();

        $query = 'select * from TREE_EVENTS';
        if (!empty($params)) {
            $query .= ' where 1=1';

            foreach ($params as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                switch ($key) {
                    case 'cameras':
                        $query .= ' and ' . $this->whereIntColumnValue("CAM_NR", $value);
                        break;
                    case 'from':
                        $query .= " and DT1_MIN >= '$value'";
                        break;
                    case 'to':
                        $query .= " and DT1_MAX <= '$value'";
                        break;
                    case 'method':
                    case 'initially':
                        break;
                    default:
                        die("galleryGetTreeEvents() failed: unknown param \"$key\" = \"$value\"");
                }
            }
            unset($key, $value);
        }
        $query .= ' order by ' . $this->datePart('year', 'DT1_MAX') . ' desc, DT1_MAX asc';
        $res = $this->db->query($query);
        $this->error($res);
        while ($res->fetchInto($v, DB_FETCHMODE_ASSOC)) {
            $date = $v[$this->key('BYHOUR')];
            if (!isset($tree_events_result[$date])) {
                $tree_events_result[$date] = array(
                    'date' => $date,
                );
            }
            $tree_events_result[$date]['image_' . $v[$this->key('CAM_NR')] . '_count'] = $v[$this->key(
                'IMAGE_COUNT'
            )];
            $tree_events_result[$date]['image_' . $v[$this->key('CAM_NR')] . '_size'] = $v[$this->key('IMAGE_SIZE')];
            $tree_events_result[$date]['video_' . $v[$this->key('CAM_NR')] . '_count'] = $v[$this->key(
                'VIDEO_COUNT'
            )];
            $tree_events_result[$date]['video_' . $v[$this->key('CAM_NR')] . '_size'] = $v[$this->key('VIDEO_SIZE')];
            $tree_events_result[$date]['audio_' . $v[$this->key('CAM_NR')] . '_count'] = $v[$this->key(
                'AUDIO_COUNT'
            )];
            $tree_events_result[$date]['audio_' . $v[$this->key('CAM_NR')] . '_size'] = $v[$this->key('AUDIO_SIZE')];
        }
        return $tree_events_result;
    } /* galleryGetTreeEvents() */

    /**
     *  Метод обновляет дерево событий
     * @param string $start_hb Дата начала обновления дерева, выровненное по часу
     * @param string $end_hb Дата окончания обновления дерева, выровненное по часу
     * @param string $to Ограничитель DT1 для предотвращения зацикливания
     *                    при постоянном добавлении в EVENTS и при этом неопр. $start_hb и $end_hb
     * @param bool|string $cameras Список камер
     * @return array
     */
    public function galleryUpdateTreeEvents($start_hb, $end_hb, $to = false, $cameras = false)
    {
        $query = 'select DT1, CAM_NR, EVT_ID, FILESZ_KB from EVENTS where EVT_ID in (12, 15,16,17, 23, 32)';
        if ($start_hb) {
            $hour_start = date('Y-m-d H:00:00', strtotime($start_hb));
            $query .= " and DT1 >= '$hour_start'";
        }
        if ($end_hb) {
            $hour_end = date('Y-m-d H:59:59', strtotime($end_hb));
            $query .= " and DT1 <= '$hour_end'";
        }
        if ($to) {
            $query .= " and DT1 <= '$to'";
        }
        if ($cameras) {
            $query .= " and " . $this->whereIntColumnValue("CAM_NR", $cameras);
        }
        $query .= ' order by DT1 asc';

        $res = $this->db->query($query);
        $this->error($res);

        $tree_events = array();
        while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
            /* XXX date() + strtotime() - very slow */
            $date = strtr(substr($line[$this->key('DT1')], 0, 13), '- ', '__');
            $key = $date . '_' . $line[$this->key('CAM_NR')];

            if (!isset($tree_events[$key])) {
                $tree_events[$key] = array(
                    'DATE' => $date,
                    'CAM_NR' => $line[$this->key('CAM_NR')],
                    'DT1_MAX' => $line[$this->key('DT1')],
                    'DT1_MIN' => $line[$this->key('DT1')],
                    'IMAGE_COUNT' => 0,
                    'IMAGE_SIZE' => 0,
                    'VIDEO_COUNT' => 0,
                    'VIDEO_SIZE' => 0,
                    'AUDIO_COUNT' => 0,
                    'AUDIO_SIZE' => 0
                );
            }
            $a = &$tree_events[$key];
            $evt_id = (int)$line[$this->key('EVT_ID')];
            switch ($evt_id) {
                case 15:
                case 16:
                case 17:
                    $a['IMAGE_COUNT']++;
                    $a['IMAGE_SIZE'] += (int)$line[$this->key('FILESZ_KB')];
                    break;
                case 12:
                case 23:
                    $a['VIDEO_COUNT']++;
                    $a['VIDEO_SIZE'] += (int)$line[$this->key('FILESZ_KB')];
                    break;
                case 32:
                    $a['AUDIO_COUNT']++;
                    $a['AUDIO_SIZE'] += (int)$line[$this->key('FILESZ_KB')];
                    break;
            }
            if ($line[$this->key('DT1')] > $a['DT1_MAX']) {
                $a['DT1_MAX'] = $line[$this->key('DT1')];
            }
            if ($line[$this->key('DT1')] < $a['DT1_MIN']) {
                $a['DT1_MIN'] = $line[$this->key('DT1')];
            }
        }

        $query = 'delete from TREE_EVENTS where 1=1';
        if ($start_hb) {
            $query .= " and BYHOUR >= '" . date('Y_m_d_H', strtotime($start_hb)) . "'";
        }
        if ($end_hb) {
            $query .= " and BYHOUR <= '" . date('Y_m_d_H', strtotime($end_hb)) . "'";
        }
        if ($to) {
            $query .= " and DT1_MAX <= '$to'";
        }
        if ($cameras) {
            $query .= ' and ' . $this->whereIntColumnValue("CAM_NR", $cameras);
        }

        $res = $this->db->query($query);
        $this->error($res);

        foreach ($tree_events as $row) {
            $query = 'insert into TREE_EVENTS ';
            $query .= '(BYHOUR, CAM_NR, DT1_MAX, DT1_MIN, ';
            $query .= 'IMAGE_COUNT, IMAGE_SIZE, VIDEO_COUNT, VIDEO_SIZE, AUDIO_COUNT, AUDIO_SIZE)';
            $query .= " values('" . $row['DATE'] . "'," . $row['CAM_NR'] . ','
                . "'" . $row['DT1_MAX'] . "','" . $row['DT1_MIN'] . "',"
                . $row['IMAGE_COUNT'] . ',' . $row['IMAGE_SIZE'] . ','
                . $row['VIDEO_COUNT'] . ',' . $row['VIDEO_SIZE'] . ','
                . $row['AUDIO_COUNT'] . ',' . $row['AUDIO_SIZE'] . ")";
            $res = $this->db->query($query);
            $this->error($res);
        }
        return array('status' => 'success');
    }

    /**
     *  Метод получения событий
     * @param int $camera номер камеры
     * @param int $ser_nr
     * @param string $timebegin дата начала
     * @param bool|string $timeend дата окончания
     * @param string $order сортировка
     *
     * @return array масив событий
     */

    public function getSnapshots($camera, $ser_nr, $timebegin, $timeend = false, $order = '')
    {
        $files = array();
        $query = 'SELECT ' . $this->datePart('timestamp', 'DT1') . ' as START, '
            . $this->datePart('timestamp', 'DT1') . ' as FINISH, '
            . '  EVT_ID, FILESZ_KB, FRAMES, ALT1 as U16_1, ALT2 as U16_2, EVT_CONT' . "\n"
            . 'FROM EVENTS' . "\n"
            . "WHERE CAM_NR=$camera\n"
            . "  AND EVT_ID >= 15 AND EVT_ID <= 21\n";
        if (empty($timeend)) {
            $query .= "  AND SESS_NR = $ser_nr\n";
            $query .= "  AND DT1 >= '$timebegin'\n";
        } else {
            $query .= "  AND (DT1 between '$timebegin' and '$timeend')\n";
        }
        $query .= "ORDER BY DT1 " . $order;

        $res = $this->db->query($query);
        $this->error($res);
        while ($res->fetchInto($line)) {
            $f = array();
            foreach ($line as $k => $v) {
                $k = strtoupper($k);
                $f[$k] = trim($v);
            }
            $files[] = $f;
        }
        return $files;
    }

    /**
     *  Метод позволяет получить события для pda-версии
     * @param string $cams_csv список камер
     * @param string $timebegin дата начала
     * @param string $timeend дата окончания
     * @param string $order сортировка
     *
     * @return array масив событий
     */
    public function getSnapStatsByRecSeries($cams_csv, $timebegin, $timeend, $order = '')
    {

        $files = array();
        $query = 'SELECT ' . $this->datePart('timestamp', 'E1.DT1') . ' as START, '
            . $this->datePart('timestamp', 'E2.DT1') . ' as FINISH, '
            . '  E1.CAM_NR, E1.SESS_NR AS SESS_NR,' . "\n"
            . '  count(E3.DT1) as SHANPHOTS_NB' . "\n"
            . "FROM EVENTS AS E1\n"
            . '  JOIN EVENTS AS E2 ON (E1.SESS_NR = E2.SESS_NR AND E1.CAM_NR = E2.CAM_NR AND'
            . '    E1.DT1 = E2.DT2 AND E1.EVT_ID = 13 AND E2.EVT_ID = 14)' . "\n"
            . '  JOIN EVENTS AS E3 ON (E1.SESS_NR = E3.SESS_NR AND E1.CAM_NR = E3.CAM_NR)' . "\n"
            . "WHERE E1.CAM_NR in ($cams_csv)\n"
            . "  AND E1.EVT_ID in (13)\n"
            . "  AND ((E1.DT1 between '$timebegin' and '$timeend') and "
            . "      (E2.DT1 is null or E2.DT1 between '$timebegin' and '$timeend'))\n"
            . '  AND (E3.EVT_ID >= 15 and E3.EVT_ID <= 21 and E3.DT1 >= E1.DT1 and E3.DT1 <= E2.DT1)' . "\n"
            . 'GROUP BY E1.DT1, E2.DT1, E1.CAM_NR, E1.SESS_NR' . "\n"
            . "ORDER BY E2.DT1 " . $order;

        $res = $this->db->query($query);

        $this->error($res);
        while ($res->fetchInto($line)) {
            $f = array();
            foreach ($line as $k => $v) {
                $k = strtoupper($k);
                $f[$k] = trim($v);
            }
            $files[] = $f;
        }
        return $files;
    }

    /**
     *  Метод позволяет получить события для pda-версии
     * @param string $cams_csv список камер
     * @param string $timebegin дата начала
     * @param string $timeend дата окончания
     * @param string $order сортировка
     *
     * @return array масив событий
     */
    public function getSnapStatsByInterval($cams_csv, $timebegin, $timeend, $order = '')
    {
        $files = array();
        $query = 'select ' . "\n"
           . $this->datePart('timestamp', 'min(DT1)') . " as FIRST,\n"
           . $this->datePart('timestamp', 'max(DT1)') . " as LAST,\n"
           . "CAM_NR, '0', count(*) as SHANPHOTS_NB\n"
           . "from EVENTS\n"
           . "where\n"
           . "(DT1 between '$timebegin' and '$timeend')\n"
           . "and CAM_NR in ($cams_csv)\n"
           . "and EVT_ID >= 15 and EVT_ID <= 21\n"
           . "group by CAM_NR\n"
           . 'order by max(DT1) ' . $order;

        $res = $this->db->query($query);

        $this->error($res);
        while ($res->fetchInto($line)) {
            $f = array();
            foreach ($line as $k => $v) {
                $k = strtoupper($k);
                $f[$k] = trim($v);
            }
            $files[] = $f;
        }
        return $files;
    }

    /**
     *  Метод позволяет получить масив событий для offline модуля
     * @param array $cams список камер
     * @param array $date дата
     * @param array $evt_ids тип событий
     * @param array $dayofweek дни недели
     * @param bool|int $timemode фильтр даты
     * @param array|bool $page лимит и номер страницы
     *
     * @return array масив событий
     */
    public function eventsSelect($cams, $date, $evt_ids, $dayofweek, $timemode = false, $page = false)
    {
        global $__EVENTS_QUERY_INFO; // for _playlist.php
        $all_continuous_events = array(12, 23, 32);
        $query_continuous_events = array_intersect($all_continuous_events, $evt_ids);
        $query_noncontinuous_events = array_diff($evt_ids, $all_continuous_events);

        $events = array();
        $query = 'SELECT ' . $this->datePart('timestamp', 'DT1') . ' as UDT1, ' . $this->datePart('timestamp', 'DT2') .
            ' as UDT2,';
        $query .= ' CAM_NR, EVT_ID, SESS_NR AS SER_NR, FILESZ_KB, FRAMES, ALT1 as U16_1, ALT2 as U16_2, EVT_CONT';
        $query .= ' FROM EVENTS';
        $query .= ' WHERE';
        $query .= " CAM_NR in (0, " . implode(',', $cams) . ")";
        $query .= " AND (";

        if (!empty($timemode) && $timemode == 1) {
            $timebegin = sprintf(
                '20%02s-%02u-%02u %02u:%02u:00',
                $date['from'][0],
                $date['from'][1],
                $date['from'][2],
                $date['from'][3],
                $date['from'][4]
            );
            $timeend = sprintf(
                '20%02s-%02u-%02u %02u:%02u:59',
                $date['to'][0],
                $date['to'][1],
                $date['to'][2],
                $date['to'][3],
                $date['to'][4]
            );

            if (count($query_continuous_events) > 0) {
                $query .= " ( EVT_ID in (" . implode(',', $query_continuous_events) .
                    ") and ( (DT1 between '$timebegin' and '$timeend') or (DT2 between '$timebegin' and '$timeend') ))";
            }
            if (count($query_noncontinuous_events) > 0) {
                if (count($query_continuous_events) > 0) {
                    $query .= " OR ";
                }
                $query .= "(EVT_ID in (" . implode(',', $query_noncontinuous_events) .
                    ") and (DT1 between '$timebegin' and '$timeend'))";
            }
        } else {
            $timebegin = sprintf('20%02s-%02u-%02u 00:00:00', $date['from'][0], $date['from'][1], $date['from'][2]);
            $timeend = sprintf('20%02s-%02u-%02u 23:59:59', $date['to'][0], $date['to'][1], $date['to'][2]);
            $time_in_day_begin = sprintf('%02u:%02u:00', $date['from'][3], $date['from'][4]);
            $time_in_day_end = sprintf('%02u:%02u:59', $date['to'][3], $date['to'][4]);

            if (count($query_continuous_events) > 0) {
                $query .= "( EVT_ID in (" . implode(',', $query_continuous_events) .
                    ") and ( ( DT1 between '$timebegin' and '$timeend' )";
                $query .= " or ( DT2 between '$timebegin' and '$timeend' ) ) and ( " .
                    $this->datePart('weekday', 'DT1') . " in (" . implode(',', $dayofweek) . ") or " .
                    $this->datePart('weekday', 'DT2') . " in (" . implode(',', $dayofweek) . ") )";
                $query .= " and ( ( " . $this->datePart('time', 'DT1') .
                    " between '$time_in_day_begin' and '$time_in_day_end' ) or ( " . $this->datePart('time', 'DT2') .
                    " between '$time_in_day_begin' and '$time_in_day_end' ) ))";
            }

            if (count($query_noncontinuous_events) > 0) {
                if (count($query_continuous_events) > 0) {
                    $query .= " OR ";
                }
                $query .= "( EVT_ID in (" . implode(',', $query_noncontinuous_events) .
                    ")and ( DT1 between '$timebegin' and '$timeend' )";
                $query .= " and ( " . $this->datePart('weekday', 'DT1') . " in (" . implode(',', $dayofweek) .
                    ") ) and ( (" . $this->datePart('time', 'DT1') .
                    " between '$time_in_day_begin' and '$time_in_day_end') ))";
            }
        }

        $query .= " )";
        $query .= ' ORDER BY DT1';
        if (!empty($page)) {
            $query .= ' LIMIT ' . $page['limit'];
            $query .= ' OFFSET ' . $page['offset'];
        }

        $res = $this->db->query($query);
        $this->error($res);
        while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
            $f = array();
            foreach ($line as $k => $v) {
                $k = strtoupper($k);
                $f[$k] = trim($v);
            }
            $events[] = $f;
        }
        $__EVENTS_QUERY_INFO['query'] = $query;
        $__EVENTS_QUERY_INFO['timebegin'] = $timebegin;
        $__EVENTS_QUERY_INFO['timeend'] = $timeend;
        $__EVENTS_QUERY_INFO['dayofweek'] = $dayofweek;
        @$__EVENTS_QUERY_INFO['time_in_day_begin'] = $time_in_day_begin;
        @$__EVENTS_QUERY_INFO['time_in_day_end'] = $time_in_day_end;
        return $events;
    }

    /**
     *  Добавление параметров камеры
     * @param string $bind_mac 'local'
     * @param int $cam_nr номер камеры
     * @param string $parname название параметра
     * @param string $parval значение параметра
     * @param string $host для какого хоста
     * @param string $user для какого пользователя
     */
    public function addCamera($bind_mac, $cam_nr, $parname, $parval, $host, $user)
    {
        $parval = $parval == null ? 'NULL' : "'$parval'";
        $query = 'INSERT INTO CAMERAS ';
        $query .= '(BIND_MAC, CAM_NR, PARNAME, PARVAL, CHANGE_HOST, CHANGE_USER)';
        $query .= " VALUES ('" . $bind_mac . "'," . $cam_nr . ",'" . $parname . "'," . $parval . ",'" .
            $host . "','" . $user . "')";
        $res = $this->db->query($query);
        $this->error($res);
    }

    /**
     *
     * Обновление параметров камеры
     *
     * @param string $bind_mac 'local'
     * @param int $cam_nr номер камеры
     * @param string $parname название параметра
     * @param string $parval значение параметра
     * @param string $host для какого хоста
     * @param string $user для какого пользователя
     */
    public function updateCamera($bind_mac, $cam_nr, $parname, $parval, $host, $user)
    {
        $parval = $parval == null ? 'NULL' : "'$parval'";
        $query = 'UPDATE CAMERAS SET';
        $query .= " PARVAL = $parval";
        $query .= ", CHANGE_HOST = '$host'";
        $query .= ", CHANGE_USER = '$user'";
        $query .= ", CHANGE_TIME = NOW()";
        $query .= " WHERE BIND_MAC = '$bind_mac'";
        $query .= " AND CAM_NR = $cam_nr";
        $query .= " AND PARNAME = '$parname'";
        $res = $this->db->query($query);
        $this->error($res);
    }

    /**
     *  Добавление или изменение параметров камеры
     * @param string $bind_mac 'local'
     * @param int $cam_nr номер камеры
     * @param string $parname название параметра
     * @param string $parval значение параметра
     * @param string $host для какого хоста
     * @param string $user для какого пользователя
     */
    public function replaceCamera($bind_mac, $cam_nr, $parname, $parval, $host, $user)
    {
        $query = 'SELECT * FROM CAMERAS ';
        $query .= " WHERE BIND_MAC = '$bind_mac'";
        $query .= " AND CAM_NR = $cam_nr";
        $query .= " AND PARNAME = '$parname'";
        $res = $this->db->query($query);
        $this->error($res);
        $res->fetchInto($line);
        if (empty($line)) {
            $this->addCamera($bind_mac, $cam_nr, $parname, $parval, $host, $user);
        } else {
            $this->updateCamera($bind_mac, $cam_nr, $parname, $parval, $host, $user);
        }
    }

    /**
     *
     * Метод получает настройки камеры по умолчанию
     * @param int $cam_nr номер камеры
     * @param string $bind_mac 'local'
     * @return array параметры камеры
     */
    public function getDefCamParams($cam_nr = 0, $bind_mac = 'local')
    {
        $cams = array();
        $query = 'SELECT CAM_NR, PARNAME, PARVAL, CHANGE_HOST, CHANGE_USER, CHANGE_TIME FROM CAMERAS';
        $query .= ' WHERE BIND_MAC=\'' . $bind_mac . '\' AND (CAM_NR=0 OR CAM_NR=' . $cam_nr . ')';

        $res = $this->db->query($query);

        $this->error($res);

        while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
            $cams[] = array(
                'CAM_NR' => trim($line[$this->key('CAM_NR')]),
                'PARAM' => trim($line[$this->key('PARNAME')]),
                'VALUE' => trim($line[$this->key('PARVAL')]),
                'CHANGE_HOST' => trim($line[$this->key('CHANGE_HOST')]),
                'CHANGE_USER' => trim($line[$this->key('CHANGE_USER')]),
                'CHANGE_TIME' => trim($line[$this->key('CHANGE_TIME')]),
            );
        }
        return $cams;
    }

    /**
     *
     * Метод позволяет получить параметры камер
     * @param string $cams_list список камер
     * @param string $param_list список параметров
     * @param string $bind_mac 'local'
     * @return array параметры камер
     */
    public function getCamParams($cams_list = '', $param_list = '', $bind_mac = 'local')
    {
        $cams = array();
        $query = 'SELECT CAM_NR, PARNAME, PARVAL FROM CAMERAS';
        $query .= ' WHERE BIND_MAC=\'' . $bind_mac . '\'';
        if (!empty($cams_list)) {
            $query .= ' AND (CAM_NR=0  OR CAM_NR in(' . $cams_list . '))';
        }
        $query .= ' AND PARNAME IN (' . $param_list . ')'; // AND  PARVAL<>\'\' AND PARVAL IS NOT NULL ';
        $query .= ' ORDER BY CAM_NR';
        $res = $this->db->query($query);
        $this->error($res);
        while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
            $cams[] = array(
                'CAM_NR' => trim($line[$this->key('CAM_NR')]),
                'PARAM' => trim($line[$this->key('PARNAME')]),
                'VALUE' => trim($line[$this->key('PARVAL')]),
            );
        }
        return $cams;
    }

    /**
     *
     * Метод позволяет получить последний номер камеры
     * @param string $bind_mac 'local'
     * @return int номер камеры
     */
    public function maxCamNr($bind_mac = 'local')
    {
        $query = 'SELECT MAX(CAM_NR) AS LAST_NUM FROM CAMERAS WHERE BIND_MAC=\'' . $bind_mac . '\'';
        $res = $this->db->query($query);
        $this->error($res);
        $res->fetchInto($line);
        return isset($line[0]) ? $line[0] : false;
    }

    /**
     *
     * Метод позволяет удалить камеру
     * @param int $cam_nr номер камеры
     * @param string $bind_mac 'local'
     */

    public function deleteCamera($cam_nr, $bind_mac = 'local')
    {
        $query = sprintf('DELETE FROM CAMERAS WHERE BIND_MAC=\'' . $bind_mac . '\' AND CAM_NR=%d', $cam_nr);
        $res = $this->db->query($query);
        $this->error($res);
    }

    /**
     *
     * Метод позволяет добавить раскладку
     * @param string $display
     * @param int $mon_nr
     * @param string $mon_type
     * @param string $mon_name
     * @param string $remote_addr
     * @param string $login_user
     * @param array $fWINS
     * @param array $vWINS
     * @param string $bind_mac
     */
    public function addLayouts(
        $display,
        $mon_nr,
        $mon_type,
        $mon_name,
        $remote_addr,
        $login_user,
        $fWINS,
        $vWINS,
        $bind_mac = 'local'
    ) {
        $query = sprintf(
            'INSERT INTO LOCAL_LAYOUTS (BIND_MAC, DISPLAY, MON_NR, MON_TYPE, MON_NAME, %s, CHANGE_HOST, CHANGE_USER) ' .
            'VALUES (\'local\', \'%s\', %d, \'%s\', \'%s\', %s, \'%s\', \'%s\')',
            implode(', ', $fWINS),
            $display,
            $mon_nr,
            $mon_type,
            $mon_name,
            implode(', ', $vWINS),
            $remote_addr,
            $login_user
        );
        $res = $this->db->query($query);
        $this->error($res);
    }

    /**
     *
     * Метод позволяет добавить раскладку для WEB
     * @param int $mon_nr
     * @param string $mon_type
     * @param string $mon_name
     * @param string $remote_addr
     * @param string $login_user
     * @param $PrintCamNames
     * @param $AspectRatio
     * @param $ReconnectTimeout
     * @param $allWINS
     * @param string $bind_mac
     * @internal param array $fWINS
     * @internal param array $vWINS
     */
    public function webAddLayouts(
        $mon_nr,
        $mon_type,
        $mon_name,
        $remote_addr,
        $login_user,
        $PrintCamNames,
        $AspectRatio,
        $ReconnectTimeout,
        $allWINS,
        $bind_mac = 'local'
    ) {
        $mon_type = trim($mon_type);
        $mon_name = trim($mon_name);
        $remote_addr = trim($remote_addr);
        $login_user = trim($login_user);
        $AspectRatio = trim($AspectRatio);
        $allWINS = trim($allWINS);
        $bind_mac = trim($bind_mac);

        $query = sprintf(
            'INSERT INTO WEB_LAYOUTS (BIND_MAC, MON_NR, MON_TYPE, SHORT_NAME, PRINT_CAM_NAME , PROPORTION, '
            . 'RECONNECT_TOUT, WINS, CHANGE_HOST, CHANGE_USER) '
            . ' VALUES (\'local\', %d, \'%s\', \'%s\', %s, \'%s\', %d, \'%s\', \'%s\', \'%s\')',
            $mon_nr,
            $mon_type,
            $mon_name,
            $PrintCamNames,
            $AspectRatio,
            $ReconnectTimeout,
            $allWINS,
            $remote_addr,
            $login_user
        );
        $res = $this->db->query($query);
        $this->error($res);
    }

    /**
     *
     * Метод позволяет удалить раскладку
     * @param string $display
     * @param int $mon_nr
     * @param string $bind_mac
     */
    public function deleteLayouts($display, $mon_nr, $bind_mac = 'local')
    {
        $query = 'DELETE FROM LOCAL_LAYOUTS';
        $query .= " WHERE BIND_MAC ='$bind_mac'";
        $query .= " AND DISPLAY ='$display'";
        $query .= " AND MON_NR = $mon_nr";
        $res = $this->db->query($query);
        $this->error($res);
    }

    /**
     *
     * Метод позволяет удалить раскладку для WEB
     * @param int $mon_nr
     * @param string $bind_mac
     */
    public function webDeleteLayouts($mon_nr, $bind_mac = 'local')
    {
        $query = 'DELETE FROM WEB_LAYOUTS';
        $query .= " WHERE BIND_MAC ='$bind_mac'";
        $query .= " AND MON_NR = $mon_nr";
        $res = $this->db->query($query);
        $this->error($res);
    }

    /**
     *
     * Метод позволяет обновить данные раскладки в БД
     * @param string $display
     * @param unknown_type $mon_nr
     * @param string $mon_type
     * @param string $mon_name
     * @param string $host
     * @param string $user
     * @param array $fWINS
     * @param array $vWINS
     * @param string $bind_mac
     */
    public function updateLayouts(
        $display,
        $mon_nr,
        $mon_type,
        $mon_name,
        $host,
        $user,
        $fWINS,
        $vWINS,
        $bind_mac = 'local'
    ) {
        $query = 'UPDATE LOCAL_LAYOUTS SET ';
        $query .= "MON_TYPE = '$mon_type'";
        $query .= ", MON_NAME = '$mon_name'";
        $query .= ", CHANGE_HOST = '$host'";
        $query .= ", CHANGE_USER = '$user'";

        for ($i = 0; $i < count($vWINS); $i++) {
            if (!empty($vWINS[$i]) /* неважно что '0' даст true, 0-вой камеру тут не будет */) {
                $query .= ", {$fWINS[$i]} = {$vWINS[$i]}";
            } else {
                $query .= ", {$fWINS[$i]} = NULL";
            }
        }

        $query .= " WHERE BIND_MAC ='$bind_mac'";
        $query .= " AND DISPLAY ='$display'";
        $query .= " AND MON_NR = $mon_nr";

        $res = $this->db->query($query);
        $this->error($res);
    }

    /**
     *
     * Метод позволяет обновить данные раскладки для WEB  в БД
     * @param unknown_type $mon_nr
     * @param string $mon_type
     * @param string $mon_name
     * @param string $host
     * @param string $user
     * @param $PrintCamNames
     * @param $AspectRatio
     * @param $ReconnectTimeout
     * @param $allWINS
     * @param string $bind_mac
     * @internal param string $display
     * @internal param array $fWINS
     * @internal param array $vWINS
     */
    public function webUpdateLayouts(
        $mon_nr,
        $mon_type,
        $mon_name,
        $host,
        $user,
        $PrintCamNames,
        $AspectRatio,
        $ReconnectTimeout,
        $allWINS,
        $bind_mac = 'local'
    ) {
        $query = 'UPDATE WEB_LAYOUTS SET ';
        $query .= "MON_TYPE = '$mon_type'";
        $query .= ", SHORT_NAME = '$mon_name'";
        $query .= ", CHANGE_HOST = '$host'";
        $query .= ", CHANGE_USER = '$user'";
        $query .= ", PRINT_CAM_NAME = '$PrintCamNames'";
        $query .= ", PROPORTION = '$AspectRatio'";

        $query .= ", RECONNECT_TOUT = $ReconnectTimeout";

        $query .= ", WINS = '$allWINS'";
        $query .= " WHERE BIND_MAC ='$bind_mac'";
        $query .= " AND MON_NR = $mon_nr";

        $res = $this->db->query($query);
        $this->error($res);
    }

    /**
     *
     * Метод позволяет установить раскладку по умолчанию для WEB
     * @param unknown_type $mon_nr - номер раскладки, устанавливаемый по умолчанию
     */
    public function webSetDefLayout($mon_nr)
    {
        $query = 'UPDATE WEB_LAYOUTS SET ';
        $query .= "IS_DEFAULT = 0";
        $res = $this->db->query($query);
        $this->error($res);

        $query = 'UPDATE WEB_LAYOUTS SET ';
        $query .= "IS_DEFAULT = 1";
        $query .= " WHERE MON_NR = $mon_nr";

        $res = $this->db->query($query);
        $this->error($res);
    }

    /**
     *
     * Метод добавляет или обновляет параметры раскладки
     * @param string $display
     * @param unknown_type $mon_nr
     * @param string $mon_type
     * @param string $mon_name
     * @param string $host
     * @param string $user
     * @param array $fWINS
     * @param array $vWINS
     * @param string $bind_mac
     */
    public function replaceLayouts(
        $display,
        $mon_nr,
        $mon_type,
        $mon_name,
        $host,
        $user,
        $fWINS,
        $vWINS,
        $bind_mac = 'local'
    ) {
        $query = 'SELECT * FROM LOCAL_LAYOUTS ';
        $query .= " WHERE BIND_MAC = '$bind_mac'";
        $query .= " AND MON_NR = $mon_nr";
        $query .= " AND DISPLAY = '$display'";
        $res = $this->db->query($query);
        $this->error($res);
        $res->fetchInto($line);
        if (empty($line)) {
            $this->addLayouts($display, $mon_nr, $mon_type, $mon_name, $host, $user, $fWINS, $vWINS);
        } else {
            $this->updateLayouts($display, $mon_nr, $mon_type, $mon_name, $host, $user, $fWINS, $vWINS);
        }
    }

    /**
     *
     * Метод добавляет или обновляет параметры раскладки для WEB
     * @param unknown_type $mon_nr
     * @param string $mon_type
     * @param string $mon_name
     * @param string $host
     * @param string $user
     * @param $PrintCamNames
     * @param $AspectRatio
     * @param $ReconnectTimeout
     * @param $allWINS
     * @param string $bind_mac
     * @internal param string $display
     * @internal param array $fWINS
     * @internal param array $vWINS
     */
    public function webReplaceLayouts(
        $mon_nr,
        $mon_type,
        $mon_name,
        $host,
        $user,
        $PrintCamNames,
        $AspectRatio,
        $ReconnectTimeout,
        $allWINS,
        $bind_mac = 'local'
    ) {
        $mon_type = trim($mon_type);
        $mon_name = trim($mon_name);
        $host = trim($host);
        $user = trim($user);
        $AspectRatio = trim($AspectRatio);
        $allWINS = trim($allWINS);
        $bind_mac = trim($bind_mac);

        $query = 'SELECT * FROM WEB_LAYOUTS ';
        $query .= " WHERE BIND_MAC = '$bind_mac'";
        $query .= " AND MON_NR = $mon_nr";

        $res = $this->db->query($query);
        $this->error($res);
        $res->fetchInto($line);
        if (empty($line)) {
            $this->webAddLayouts(
                $mon_nr,
                $mon_type,
                $mon_name,
                $host,
                $user,
                $PrintCamNames,
                $AspectRatio,
                $ReconnectTimeout,
                $allWINS
            );
        } else {
            $this->webUpdateLayouts(
                $mon_nr,
                $mon_type,
                $mon_name,
                $host,
                $user,
                $PrintCamNames,
                $AspectRatio,
                $ReconnectTimeout,
                $allWINS
            );
        }
    }

    /**
     *
     * Метод позволяет получить параметры раскладки
     * @param string $display
     * @param int $mon_nr
     * @param string $bind_mac
     * @return array параметры
     */
    public function getMonitor($display, $mon_nr, $bind_mac = 'local')
    {
        $query = 'SELECT MON_NR, MON_TYPE, MON_NAME, IS_DEFAULT, ' .
            'WIN1, WIN2, WIN3, WIN4, WIN5, WIN6, WIN7, WIN8, WIN9,
            WIN10, WIN11, WIN12, WIN13, WIN14, WIN15, WIN16,
            WIN17, WIN18, WIN19, WIN20, WIN21, WIN22, WIN23,
            WIN24, WIN25, WIN26, WIN27, WIN28, WIN29, WIN30,
            WIN31, WIN32, WIN33, WIN34, WIN35, WIN36, WIN37,
            WIN38, WIN39, WIN40 ' .
            'CHANGE_HOST, CHANGE_USER, CHANGE_TIME ' .
            'FROM LOCAL_LAYOUTS ' .
            'WHERE BIND_MAC=\'' . $bind_mac . '\' AND DISPLAY=\'' . $display . '\' AND MON_NR=' . $mon_nr;
        $res = $this->db->query($query);
        $this->error($res);
        $res->fetchInto($line);
        return $line;
    }

    /**
     *
     * Метод позволяет получить параметры раскладки для WEB
     * @param int $mon_nr
     * @param string $bind_mac
     * @return array параметры
     */
    public function webGetMonitor($mon_nr, $bind_mac = 'local')
    {

        $query = 'SELECT MON_NR, MON_TYPE, SHORT_NAME, IS_DEFAULT, WINS,' .
            'CHANGE_HOST, CHANGE_USER, CHANGE_TIME, PRINT_CAM_NAME, PROPORTION, RECONNECT_TOUT ' .
            'FROM WEB_LAYOUTS ' .
            'WHERE BIND_MAC=\'' . $bind_mac . '\' AND MON_NR=' . $mon_nr;

        $res = $this->db->query($query);
        $this->error($res);
        $res->fetchInto($line);
        return $line;
    }

    /**
     *
     * Метод позволяет получить параметры всех раскладок
     * @param string $bind_mac
     * @return array раскладки
     */
    public function getLayouts($bind_mac = 'local')
    {
        $mon = array();
        $query = 'SELECT * FROM LOCAL_LAYOUTS';
        $query .= " WHERE BIND_MAC='$bind_mac'";
        $query .= ' ORDER BY MON_NR';

        $res = $this->db->query($query);
        $this->error($res);
        while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
            $m = array();
            foreach ($line as $k => $v) {
                $k = strtoupper($k);
                $m[$k] = trim($v);
            }
            $mon[] = $m;
        }
        return $mon;
    }

    /**
     *
     * Метод позволяет получить параметры всех раскладок для WEB
     * или WEB-раскладок, разрешенных для пользователя
     * @param null $user
     * @param string $bind_mac
     * @return array раскладки
     */
    public function webGetLayouts($user = null, $bind_mac = 'local')
    {

        $mon = array();
        $query = 'SELECT * FROM WEB_LAYOUTS';

        $allowed_layouts = array();
        //номер раскладки, указанный в пользовательских настройках первым, устанавливается по умолчанию
        $def_num = null;

        //Если пользователь указан - формируем запрос о разрешенных раскладках
        if ($user != null) {
            $sub_query = sprintf("SELECT ALLOW_LAYOUTS FROM USERS WHERE USER_LOGIN='%s'", $user);
            $sub_res = $this->db->query($sub_query);
            $this->error($allowed_layouts);
            //Определяем разрешенные раскладки
            while ($sub_res->fetchInto($vl, DB_FETCHMODE_ASSOC)) {
                $l = array();
                foreach ($vl as $k => $v) {
                    $k = strtoupper($k);
                    $l[$k] = trim($v);
                }
                $lo[] = $l;
            }

            $allowed_layouts = explode(',', trim($lo[0]["ALLOW_LAYOUTS"]));

            //Первая указанная раскладка используется по умолчанию
            $def_num = $allowed_layouts[0];

            /*если разрешены все раскладки(пустое поле разрешенных раскладок - обнуляем пользователя и раскладку
             по умолчанию)*/
            if (trim($lo[0]["ALLOW_LAYOUTS"]) == '' || $allowed_layouts[0] == '') {
                $user = null;
                $def_num = null;
            } else {

                //определяем перечень разрешенных раскладок пользователя и формируем соотв. запрос
                $lts = "'";
                $lts = $lts . implode("', '", $allowed_layouts) . "'";

                $query .= " WHERE BIND_MAC='$bind_mac' AND MON_NR IN ($lts)";
                $query .= ' ORDER BY MON_NR';
            }
        }

        //Если пользователь не указан или не заданны конкретные раскладки - выбираем все раскладки
        if ($user == null) {
            $query .= " WHERE BIND_MAC='$bind_mac'";
            $query .= ' ORDER BY MON_NR';
        }

        $res = $this->db->query($query);
        $this->error($res);
        while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
            $m = array();
            foreach ($line as $k => $v) {
                $k = strtoupper($k);
                $m[$k] = trim($v);
            }
            $mon[] = $m;
        }

        //если в пользовательских настройках указана раскладка по умолчанию
        if ($def_num != null && $user != null) {
            foreach ($mon as $key => $val) {
                $mon[$key]["IS_DEFAULT"] = "0";
                if ($mon[$key]['MON_NR'] == $def_num) {
                    $mon[$key]["IS_DEFAULT"] = '1';
                }
            }
        }
        return $mon;
    }

    /**
     * Метод добавляет пользователя
     *
     * @param string $u_host Допустимые IP-адреса пользователького хоста.
     * @param string $u_name Логин
     * @param string $passwd пароль
     * @param string $groups група
     * @param unknown_type $guest - гостевой доступ
     * @param unknown_type $pda - доступ к PDA версии
     * @param string $u_devacl Доступные камеры
     * @param $u_layouts
     * @param string $u_forced_saving_limit Максимальная длительность принудительной записи (по команде) в минутах
     * @param string $sessions_per_user Ограничение количества одновременных просмотров (камер) пользователем
     * @param string $limit_fps limit_fps, кадров в секунду, [1-25] или sec/frames
     * @param string $nonmotion_fps nonmotion_fps, примеры допустимых значений: "1" - 1 кадр в 1 сек.; "2/1"
     *          - 1 кадр каждые 2 секунды.
     * @param string $limit_kbps limit_kbps, Kбит/сек
     * @param string $session_time session_time - по времени, в минутах
     * @param string $session_volume session_volume - по "закаченному" объёму, в МегаБайтах (10242)
     * @param string $u_longname ФИО
     * @param string $remote_addr хост на котором добавляют
     * @param string $login_user пользователь, который добавляет
     * @return bool результат добавления
     */
    public function addUser(
        $u_host,
        $u_name,
        $passwd,
        $groups,
        $guest,
        $pda,
        $u_devacl,
        $u_layouts,
        $u_forced_saving_limit,
        $sessions_per_user,
        $limit_fps,
        $nonmotion_fps,
        $limit_kbps,
        $session_time,
        $session_volume,
        $u_longname,
        $remote_addr,
        $login_user
    ) {
        $query = sprintf(
            'INSERT INTO USERS
                     ( ALLOW_FROM, USER_LOGIN, PASSWD, STATUS, GUEST, PDA, ALLOW_CAMS, ALLOW_LAYOUTS,
                     MAX_FORCED_REC_MINUTES, MAX_MEDIA_SESSIONS_NB,
                     MAX_VIDEO_FPS, MAX_VIDEO_NONMOTION_FPS, MAX_MEDIA_SESSION_RATE_KB,
                     MAX_MEDIA_SESSION_MINUTES, MAX_MEDIA_SESSION_VOLUME_MB,

                     LONGNAME, CHANGE_HOST, CHANGE_USER, CHANGE_TIME)
                     VALUES ( %s, %s, %s, %u, %b, %b, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW())',
            sql_format_str_val($u_host),
            sql_format_str_val($u_name),
            $this->crypt($passwd),
            $groups,
            $guest,
            $pda,
            sql_format_str_val($u_devacl),
            sql_format_str_val($u_layouts),
            sql_format_int_val($u_forced_saving_limit),
            sql_format_int_val($sessions_per_user),
            sql_format_str_val($limit_fps),
            sql_format_str_val($nonmotion_fps),
            sql_format_int_val($limit_kbps),
            sql_format_int_val($session_time),
            sql_format_int_val($session_volume),
            sql_format_str_val($u_longname),
            sql_format_str_val($remote_addr),
            sql_format_str_val($login_user)
        );

        $res = $this->db->query($query);
        return !$this->error($res, false);
    }

    /**
     *
     * Метод позволяет обновить информацию о пользователе
     * @param string $u_host Допустимые IP-адреса пользователького хоста.
     * @param string $u_name Логин
     * @param string $passwd пароль
     * @param string $groups група
     * @param unknown_type $guest - гостевой доступ
     * @param unknown_type $pda - доступ к PDA версии
     * @param string $u_devacl Доступные камеры
     * @param $u_layouts
     * @param string $u_forced_saving_limit Максимальная длительность принудительной записи (по команде) в минутах
     * @param string $sessions_per_user Ограничение количества одновременных просмотров камер пользователем
     * @param string $limit_fps limit_fps, кадров в секунду, [1-25] или sec/frames
     * @param string $nonmotion_fps nonmotion_fps, примеры допустимых значений: "1" - 1 кадр в 1 сек.; "2/1"
     *          - 1 кадр каждые 2 секунды.
     * @param string $limit_kbps limit_kbps, Kбит/сек
     * @param string $session_time session_time - по времени, в минутах
     * @param string $session_volume session_volume - по "закаченному" объёму, в МегаБайтах (10242)
     * @param string $u_longname ФИО
     * @param string $remote_addr хост на котором добавляют
     * @param string $login_user пользователь, который добавляет
     * @param string $old_u_host старый хост
     * @param string $old_u_name старый логин
     * @return bool результат обновления
     */

    public function updateUser(
        $u_host,
        $u_name,
        $passwd,
        $groups,
        $guest,
        $pda,
        $u_devacl,
        $u_layouts,
        $u_forced_saving_limit,
        $sessions_per_user,
        $limit_fps,
        $nonmotion_fps,
        $limit_kbps,
        $session_time,
        $session_volume,
        $u_longname,
        $remote_addr,
        $login_user,
        $old_u_host,
        $old_u_name
    ) {
        $str = 'UPDATE USERS SET ALLOW_FROM=%s, USER_LOGIN=%s, PASSWD=%s, STATUS=%d, GUEST=%b, PDA=%b, ' .
            'ALLOW_CAMS=%s, ALLOW_LAYOUTS=%s ,MAX_FORCED_REC_MINUTES=%s, MAX_MEDIA_SESSIONS_NB=%s, MAX_VIDEO_FPS=%s, ' .
            'MAX_VIDEO_NONMOTION_FPS=%s, MAX_MEDIA_SESSION_RATE_KB=%s, MAX_MEDIA_SESSION_MINUTES=%s, ' .
            'MAX_MEDIA_SESSION_VOLUME_MB=%s, LONGNAME=%s, CHANGE_HOST=%s, CHANGE_USER=%s, ' .
            'CHANGE_TIME=NOW() WHERE ALLOW_FROM=%s AND USER_LOGIN=%s';
        $query = sprintf(
            $str,
            sql_format_str_val($u_host),
            sql_format_str_val($u_name),
            $this->crypt($passwd),
            $groups,
            $guest,
            $pda,
            sql_format_str_val($u_devacl),
            sql_format_str_val($u_layouts),
            sql_format_int_val($u_forced_saving_limit),
            sql_format_int_val($sessions_per_user),
            sql_format_str_val($limit_fps),
            sql_format_str_val($nonmotion_fps),
            sql_format_int_val($limit_kbps),
            sql_format_int_val($session_time),
            sql_format_int_val($session_volume),
            sql_format_str_val($u_longname),
            sql_format_str_val($remote_addr),
            sql_format_str_val($login_user),
            sql_format_str_val($old_u_host),
            sql_format_str_val($old_u_name)
        );
        $res = $this->db->query($query);
        return !$this->error($res, false);
    }

    /**
     *
     * Метод позволяет удалить пользователя
     * @param string $u_name логин
     * @param string $u_host хост
     * @param int $u_status статус
     */
    public function deleteUser($u_name, $u_host, $u_status)
    {
        $query = sprintf(
            'DELETE FROM USERS WHERE USER_LOGIN=%s AND ALLOW_FROM=%s AND STATUS=%u',
            sql_format_str_val($u_name),
            sql_format_str_val($u_host),
            $u_status
        );

        $res = $this->db->query($query);
        $this->error($res);
    }

    /**
     *
     * Метод позволяет получить пароль пользователя
     * @param string $u_name логин
     * @param string $hosts хост
     * @return string пароль
     */
    public function getUserPassword($u_name, $hosts)
    {
        $query = sprintf(
            "SELECT PASSWD FROM USERS WHERE ALLOW_FROM in(%s) AND USER_LOGIN='%s'",
            "'" . implode("','", $hosts) . "'",
            $u_name
        );
        $res = $this->db->query($query);
        $this->error($res);
        $res->fetchInto($line);
        return isset($line[0]) ? trim($line[0]) : false;
    }

    /**
     *
     * Метод позволяет обновить пароль пользователя
     * @param string $u_name логин
     * @param string $u_pass пароль
     * @param string $hosts хост
     * @return bool
     */
    public function updateUserPassword($u_name, $u_pass, $hosts)
    {
        $query = sprintf(
            "UPDATE USERS SET PASSWD=%s	 WHERE ALLOW_FROM in(%s) AND USER_LOGIN='%s'",
            $this->crypt($u_pass),
            "'" . implode("','", $hosts) . "'",
            $u_name
        );
        $res = $this->db->query($query);
        $this->error($res);
        return true;
    }

    /**
     * Метод позволяет получить пользователей
     *
     * @param bool|int $status статус
     * @return array масив пользователей
     */
    public function getUsers($status = false)
    {
        $users = array();
        $query = 'SELECT ALLOW_FROM, USER_LOGIN, GUEST, PDA, PASSWD, STATUS, ALLOW_CAMS, ALLOW_LAYOUTS, ' .
            'MAX_FORCED_REC_MINUTES,  MAX_MEDIA_SESSIONS_NB, MAX_VIDEO_FPS, MAX_VIDEO_NONMOTION_FPS, ' .
            'MAX_MEDIA_SESSION_RATE_KB, MAX_MEDIA_SESSION_MINUTES, MAX_MEDIA_SESSION_VOLUME_MB,LONGNAME, ' .
            'CHANGE_HOST, CHANGE_USER, CHANGE_TIME ' .
            'FROM USERS ';
        if ($status) {
            $query .= "WHERE STATUS = $status ";
        }
        $query .= 'ORDER BY ALLOW_FROM, USER_LOGIN';

        $res = $this->db->query($query);
        $this->error($res);
        while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
            $users[] = array(
                'HOST' => trim($line[$this->key('ALLOW_FROM')]),
                'USER' => trim($line[$this->key('USER_LOGIN')]),
                'PASSWD' => trim($line[$this->key('PASSWD')]),
                'GUEST' => trim($line[$this->key('GUEST')]),
                'PDA' => trim($line[$this->key('PDA')]),
                'STATUS' => trim($line[$this->key('STATUS')]),
                'ALLOW_CAMS' => trim($line[$this->key('ALLOW_CAMS')]),
                'ALLOW_LAYOUTS' => trim($line[$this->key('ALLOW_LAYOUTS')]),
                'MAX_FORCED_REC_MINUTES' => trim($line[$this->key('MAX_FORCED_REC_MINUTES')]),
                'MAX_MEDIA_SESSIONS_NB' => trim($line[$this->key('MAX_MEDIA_SESSIONS_NB')]),
                'MAX_VIDEO_FPS' => trim($line[$this->key('MAX_VIDEO_FPS')]),
                'MAX_VIDEO_NONMOTION_FPS' => trim($line[$this->key('MAX_VIDEO_NONMOTION_FPS')]),
                'MAX_MEDIA_SESSION_RATE_KB' => trim($line[$this->key('MAX_MEDIA_SESSION_RATE_KB')]),
                'MAX_MEDIA_SESSION_MINUTES' => trim($line[$this->key('MAX_MEDIA_SESSION_MINUTES')]),
                'MAX_MEDIA_SESSION_VOLUME_MB' => trim($line[$this->key('MAX_MEDIA_SESSION_VOLUME_MB')]),
                'LONGNAME' => trim($line[$this->key('LONGNAME')]),
                'CHANGE_HOST' => trim($line[$this->key('CHANGE_HOST')]),
                'CHANGE_USER' => trim($line[$this->key('CHANGE_USER')]),
                'CHANGE_TIME' => trim($line[$this->key('CHANGE_TIME')]),
            );
        }

        return $users;
    }

    private function key($str)
    {
        if ($this->dbtype == 'pgsql') {
            return strtolower($str);
        }
        return $str;
    }

    private function datePart($type, $value)
    {
        if ($this->dbtype == 'pgsql') {
            switch ($type) {
                case 'year':
                    $str = "date_part('year', %%)";
                    break;
                case 'month':
                    $str = "date_part('month', %%)";
                    break;
                case 'day':
                    $str = "date_part('day', %%)";
                    break;
                case 'hour':
                    $str = "date_part('hour', %%)";
                    break;
                case 'weekday':
                    $str = "date_part('dow', %%)";
                    break;
                case 'time':
                    $str = "%%::time";
                    break;
                case 'timestamp':
                    $str = "date_part('epoch', %%)";
                    break;
            }
        } else {

            switch ($type) {
                case 'year':
                    $str = 'YEAR(%%)';
                    break;
                case 'month':
                    $str = 'MONTH(%%)';
                    break;
                case 'day':
                    $str = 'DAYOFMONTH(%%)';
                    break;
                case 'hour':
                    $str = 'HOUR(%%)';
                    break;
                case 'weekday':
                    $str = 'weekday(%%)';
                    break;
                case 'time':
                    $str = 'time(%%)';
                    break;

                case 'timestamp':
                    $str = "UNIX_TIMESTAMP(%%)";
                    break;

            }
        }
        $str = str_replace('%%', $value, $str);

        return $str;
    }

    private function dateFormat($value)
    {
        if ($this->dbtype == 'pgsql') {
            $str = "to_char(%%, 'yyyy_mm_dd_hh24')";
        } else {
            $str = "DATE_FORMAT(%%, '%Y_%m_%d_%H')";
        }
        $str = str_replace('%%', $value, $str);
        return $str;
    }

    private function timediff($d1, $d2)
    {
        if ($this->dbtype == 'pgsql') {
            $str = $d1 . "-" . $d2;
        } else {
            $str = "TIMEDIFF(" . $d1 . " , " . $d2 . ")";
        }
        return $str;
    }

    private function crypt($value)
    {
        if (empty($value)) {
            return "''";
        }

        if ($this->dbtype == 'pgsql') {
            $str = "crypt('%%', 'av')";
        } else {
            $str = "encrypt('%%')";
        }
        $str = str_replace('%%', $value, $str);
        return $str;
    }

    protected function whereIntColumnValue($col_name, $col_value = "")
    {
        if (@empty($col_name)) {
            throw new InvalidArgumentException("empty column name");
        }
        if (@empty($col_value)) {
            throw new InvalidArgumentException("empty '$col_name' column value");
        }
        switch (gettype($col_value)) {
            case 'array':
                return "$col_name in (" . implode(",", $col_value) . ")";
            case "string":
                if (false !== strpos($col_value, ",")) {
                    return "$col_name in (" . $col_value  . ")";
                }
                // else (non CSV) - no break, use as signle number
            case "integer":
                return "$col_name = $col_value";
            default:
                throw new InvalidArgumentException("invalid '$col_name' value");
        }
    }
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
