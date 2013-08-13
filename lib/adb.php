<?php

/**
 *
 * @file lib/adb.php
 * @brief В файле реализован класс, который обеспечивает взаимодействие с БД,<br />а также инициализируется экземпляр этого класса
 *
 * Все обращения к БД должны быть реализованы посредством этого класса,
 * а работа с БД в обход этого класса крайне нежелательна.
 *
 * Для инициализации экземпляра класса используется объект конфигурации $conf из /etc/avreg/site-defaults.php
 *
 * Для подключения к БД класс использует /usr/share/php/DB.php
 *
 */

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

class Adb {

   private
		/// Название БД
      $_database = '',
      /// Пользователь БД
      $_user = '',
      /// Пароль БД
      $_password = '',
      /// Тип БД mysql - MySql, pgsql - PostgreSql
      $_dbtype = 'mysql',
      /// Хост БД
      $_host = 'localhost',
      /// Объект для работы с БД
      $_db = false,
   	  ///Объект PEAR
   	  $_pear = false;
		

	/**
	 *  Конструктор по умолчанию
	 * Устанавливает соединение с БД
	 * @param array $param масив конфигурации класса
	 * @return true если соединение с баззой успешно, false если произошла ошибка.
	 */
   public function __construct($param) {
      $this->_database = $param['db-name'];
      $this->_user = $param['db-user'];
      $this->_password = $param['db-passwd'];
      if (isset($param['db-type']) && !empty($param['db-type']))
         $this->_dbtype = $param['db-type'];
      if (isset($param['db-host']) && !empty($param['db-host']))
         $this->_host = $param['db-host'];

//      $this->_host ='localhost';

      $this->_pear= new PEAR();

      $dsn = "{$this->_dbtype}://{$this->_user }:{$this->_password}@{$this->_host}/{$this->_database}";

      $this->_pear= new PEAR();

      $db = new DB();
      $this->_db = $db->connect($dsn,true);

//      $this->_db = DB::connect($dsn,true);

      $this->_error($this->_db);

      if ($this->_dbtype == 'mysql')
         $res = $this->_db->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
      else
         $res = $this->_db->query("SET NAMES 'utf8'");
      $this->_error($res);
      return true;
   }

	/**
	 *  Деструктор по умолчанию
	 * Закрывает соединение с БД
	 */
   public function __destruct() {
//       if (!PEAR::isError($this->_db))
      if (!$this->_pear->isError($this->_db))
         $this->_db->disconnect();
   }

   /**
    *  Проверка на ошибку в запросе к БД.
    *
    * @param object $r Объект запроса
    * @param bool $die true - закончить скрипт, false - вывести ошибку
    *
    * @return true - если ошибка, false - если нет ошибок
    */
      public function _error($r, $die = true) {
//       if (PEAR::isError($r)) {
      	if ($this->_pear->isError($r)) {
         @header('Content-Type: text/html; charset=' . $GLOBALS['chset']);

         echo 'Standard Message: ' . $r->getMessage() . "<br>";
         echo 'Standard Code: ' . $r->getCode() . "<br>";
         echo 'DBMS/User Message: ' . $r->getUserInfo() . "<br>";
         echo 'DBMS/Debug Message: ' . $r->getDebugInfo() . "<br>";

         if ($die) die($r->getMessage());
         return true;

         echo $r->getDebugInfo();
         if ($die) die($r->getMessage());
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
    * - $param['date'] дата событий
    * - $param['limit']
    * - $param['offset']
    *
    * @return array масив событий
    */

   public function gallery_get_event($param) {
      $events = array();
      $query = "SELECT ".$this->_date_format('DT1').", DT1, EVT_CONT, ALT2, ALT1, CAM_NR, FILESZ_KB, EVT_ID, ".$this->_timediff('DT2', 'DT1').", DT2";
      $query .= ' FROM EVENTS';
      $query .= ' WHERE EVT_ID in ('. implode(",", $param['events']) .')';
      $query .= ' AND EVENTS.CAM_NR in ('. implode(",", $param['cameras']).')';

      if (isset($param['date'][0]))
         $query .= ' AND '.$this->_date_part('year', 'DT1').'= '.$param['date'][0];

      if (isset($param['date'][1]))
         $query .= ' AND '.$this->_date_part('month', 'DT1').'= '.$param['date'][1];

      if (isset($param['date'][2]))
         $query .= ' AND '.$this->_date_part('day', 'DT1').'= '.$param['date'][2];

      if (isset($param['date'][3]))
         $query .= ' AND '.$this->_date_part('hour', 'DT1').'= '.$param['date'][3];

      // сортировать по дате, от текущей позиции с лимитом заданный в конфиге
      $query .= ' ORDER BY DT1 ASC LIMIT '.$param['limit']. ' OFFSET '.$param['offset'];

      $res = $this->_db->query($query);

      $this->_error($res);
      while ($res->fetchInto($line)) {
         $line[6] = filesizeHuman($line[6]);
         if (in_array((int)$line[7], array(15,16,17,18,19,20,21))) {
            $line[7] = 'image';
         } else if ((int)$line[7] == 23 ) {
            $line[7] = 'video';
         } else if ((int)$line[7] == 12 ) {
            	$line[7] = 'video';
         } else if ((int)$line[7] == 32 ) {
            $line[7] = 'audio';
         }

         // формирование уникального индекса, для работы кэша в браузере пользователя
        // $events[str_replace(array('/', '.'),'_',$line[5].'_'.$line[2].'_'.$line[0] )] = $line;
         array_push($events, $line);
      }

      return $events;
   }

    /**
     *  Метод позволяет получить дату текущего события
     *
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

    public function gallery_get_event_date($param) {
        $events = array();
        $query = "SELECT ".$this->_date_format('DT2').", DT2";
        $query .= ' FROM EVENTS';
        $query .= ' WHERE EVT_ID in ('. implode(",", $param['events']) .')';
        $query .= ' AND EVENTS.CAM_NR in ('. implode(",", $param['cameras']).')';

        if (isset($param['date'][0]))
            $query .= ' AND '.$this->_date_part('year', 'DT1').'= '.$param['date'][0];

        if (isset($param['date'][1]))
            $query .= ' AND '.$this->_date_part('month', 'DT1').'= '.$param['date'][1];

        if (isset($param['date'][2]))
            $query .= ' AND '.$this->_date_part('day', 'DT1').'= '.$param['date'][2];

        if (isset($param['date'][3]))
            $query .= ' AND '.$this->_date_part('hour', 'DT1').'= '.$param['date'][3];

        // сортировать по дате, от текущей позиции с лимитом заданный в конфиге
        $query .= ' ORDER BY DT1 ASC LIMIT '.$param['limit']. ' OFFSET '.$param['offset'];

        $res = $this->_db->query($query);

        $this->_error($res);

        $res->fetchInto($line);

        $date_events = $line[1];

        return $date_events;
    }

   /**
    *  Метод позволяет получить последнюю дату события
    *
    * @param array $param Параметры
    * - $param['cameras']  список камер
    *
    * @return string дата последнего события
    */
   public function gallery_get_last_event_date($param = array()) {
      $event = '1970-01-01 00:00:00';

      $query = 'SELECT DT1 FROM EVENTS WHERE EVT_ID in (12, 15,16,17, 23, 32)';
      if (isset($param['cameras'])) {
         $query .= ' AND EVENTS.CAM_NR in ('. implode(",", $param['cameras']).')';
      }
      // групировать и сортировать по дате
      $query .= ' ORDER BY DT1 DESC LIMIT 1';
      $res = $this->_db->query($query);
      $this->_error($res);
      if ($res->fetchInto($line))
         $event = $line[0];
      return $event;
   }
   /**
    *  Метод позволяет получить последнюю дату события в дереве
    *
    *
    * @param array $param Параметры
    * - $param['cameras']  список камер
    *
    * @return string дата последнего события дерева
    */
   public function gallery_get_last_tree_event_data($param=array()){
      $event = '1970-01-01 00:00:00';

      $query = "SELECT LAST_UPDATE";
      $query .= ' FROM TREE_EVENTS';
      if (isset($param['cameras'])) {
         $query .= ' WHERE ';
         $query .= ' TREE_EVENTS.CAM_NR in ('. implode(",", $param['cameras']).')';
      }
      $query .= ' ORDER BY LAST_UPDATE DESC LIMIT 1';
      $res = $this->_db->query($query);
      $this->_error($res);
      if ($res->fetchInto($line))
         $event = $line[0];
      return $event;
   }

   /**
    *  Метод позволяет получить дерево событий
    *
    *
    * @param array $param Параметры
    * - $param['cameras']  список камер
    *
    * @return array масив дерева событий со статистикой
    */

   public function gallery_get_tree_events($param){
      $tree_events_result = array();

      $query = "SELECT *";
      $query .= ' FROM TREE_EVENTS';
      $query .= ' WHERE TREE_EVENTS.CAM_NR in ('. implode(",", $param['cameras']).')';
      $query .= ' ORDER BY '.$this->_date_part('year', 'LAST_UPDATE').' DESC, LAST_UPDATE ASC';
      $res = $this->_db->query($query);
      $this->_error($res);
      while ($res->fetchInto($v, DB_FETCHMODE_ASSOC)){
         $date = $v[$this->_key('BYHOUR')];
         if (!isset($tree_events_result[$date])) {
            $tree_events_result[$date] = array(
               'date' => $date,
            );
         }
         $tree_events_result[$date]['image_'.$v[$this->_key('CAM_NR')].'_count'] = $v[$this->_key('IMAGE_COUNT')];
         $tree_events_result[$date]['image_'.$v[$this->_key('CAM_NR')].'_size'] = $v[$this->_key('IMAGE_SIZE')];
         $tree_events_result[$date]['video_'.$v[$this->_key('CAM_NR')].'_count'] = $v[$this->_key('VIDEO_COUNT')];
         $tree_events_result[$date]['video_'.$v[$this->_key('CAM_NR')].'_size'] = $v[$this->_key('VIDEO_SIZE')];
         $tree_events_result[$date]['audio_'.$v[$this->_key('CAM_NR')].'_count'] = $v[$this->_key('AUDIO_COUNT')];
         $tree_events_result[$date]['audio_'.$v[$this->_key('CAM_NR')].'_size'] = $v[$this->_key('AUDIO_SIZE')];
      }	
      return $tree_events_result;
   }

/**
 *  Метод обновляет дерево событий
 *
 * @param string $start Дата начала обновления дерева
 * @param string $end Дата окончания обновления дерева
 * @param string $cameras Список камер
 * @param string $on_dbld_evnts действие при обнаружении дублированных событий
 * values:
 * 'inform_user' - информировать пользователя,
 * 'ignore' - корректно заполнть TREE_EVENTS без удаления дублей из EVENTS,
 * 'clear' - удалить дублирующие записи из EVENTS и, после этого, заполнить TREE_EVENTS
 */
   public function gallery_update_tree_events($start, $end, $cameras = false, $on_dbld_evnts='ignore' ) {
      $query = 'SELECT * FROM EVENTS WHERE EVT_ID in (12, 15,16,17, 23, 32)';
      if ($start) {
         $tstart = date('Y-m-d H:00:00',strtotime($start));
         $query .= " AND DT1 >= '".$tstart."'";
      }
      if ($end) {
         $tend = date('Y-m-d H:59:59',strtotime($end));
         $query .= " AND DT1 <= '".$tend."'";
      }
      if ($cameras)
         $query .= ' AND CAM_NR in ('.$cameras.')';
      $query .= ' ORDER BY DT1 ASC';

      $res = $this->_db->query($query);
      $this->_error($res);

	
      $evt_key = '';
      $tmp = array();
      $dbl_events = array();
	
      $tree_events = array();
      while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
      	
      	
      	//проверка на наличие дублирующих записей
      	$evt_key = 'DT1='.$line[$this->_key('DT1')]."&"
      			.'DT2='.$line[$this->_key('DT2')]."&"
      			.'CAM_NR='.$line[$this->_key('CAM_NR')].'&'
      			.'EVT_ID='.$line[$this->_key('EVT_ID')].'&'
      			.'SESS_NR='.$line[$this->_key('SESS_NR')].'&'
	   			.'FILESZ_KB='.$line[$this->_key('FILESZ_KB')]."&"
    			.'FRAMES='.$line[$this->_key('FRAMES')]."&"
     			.'ALT1='.$line[$this->_key('ALT1')]."&"
     			.'ALT2='.$line[$this->_key('ALT2')]."&"
      			.'EVT_CONT='.$line[$this->_key('EVT_CONT')];
      	//проверяем уникальность ключей
      	if(isset($tmp[$evt_key])){
      		//сохраняем дублированое значение
      		array_push($dbl_events, $line);
      		continue;
      	}
      	//записываем ключи в массив
      	$tmp[$evt_key] = 1;

      	
      	
         $date = date('Y_m_d_H',strtotime($line[$this->_key('DT1')]));
         $key = $date.'_'.$line[$this->_key('CAM_NR')];
         if (!isset($tree_events[$key])) {
            $tree_events[$key] = array (
               'DATE' => $date,
               'CAM_NR' => $line[$this->_key('CAM_NR')],
               'IMAGE_COUNT' => 0,
               'IMAGE_SIZE' => 0,
               'VIDEO_COUNT' => 0,
               'VIDEO_SIZE' => 0,
               'AUDIO_COUNT' => 0,
               'AUDIO_SIZE' => 0,
               'LAST_UPDATE' => $line[$this->_key('DT1')],
            );
         }

         if (in_array( $line[$this->_key('EVT_ID')], array(15,16,17))) {
            $tree_events[$key]['IMAGE_COUNT']++;
            $tree_events[$key]['IMAGE_SIZE'] += $line[$this->_key('FILESZ_KB')];

         } else if (in_array( $line[$this->_key('EVT_ID')], array(23, 12))) {
            $tree_events[$key]['VIDEO_COUNT']++;
            $tree_events[$key]['VIDEO_SIZE'] += $line[$this->_key('FILESZ_KB')];
         } else if (in_array( $line[$this->_key('EVT_ID')], array(32))) {
         	
            $tree_events[$key]['AUDIO_COUNT']++;
            $tree_events[$key]['AUDIO_SIZE'] += $line[$this->_key('FILESZ_KB')];
         }
         $tree_events[$key]['LAST_UPDATE']=$line[$this->_key('DT1')];
      }

      //если обнаружены дублированые события
      if(sizeof($dbl_events)>0){
      	if($on_dbld_evnts=='inform_user'){
      		//Собщаем пользователю
      		return  array(
      			'status' => 'error',
      			'code'=>'1',
      			'description'=>'Doubled events detected',
      			'qtty'=>sizeof($dbl_events),
//      			'dbl_rows'=> $dbl_events,
      			'range_start'=>$dbl_events[0][$this->_key('DT1')],
      			'range_end'=>$dbl_events[sizeof($dbl_events)-1][$this->_key('DT1')]
      		) ;
      	}elseif ($on_dbld_evnts=='clear'){
      		//устраиваем чистку таблицы EVENTS от дублирующих записей
      		$cor_nr = $this->clear_dubled_evnts($dbl_events);
      		
      		if($cor_nr<sizeof($dbl_events)){
      			$rst_dbl_events = array_slice($dbl_events, $cor_nr);
      			return  array(
      				'status' => 'error',
      				'code'=>'2',
      				'description'=>'Error during cleaning',
      				'qtty'=>$cor_nr,
      				//'dbl_rows'=>$rst_dbl_events
      				'range_start'=>$rst_dbl_events[0][$this->_key('DT1')]  ,
      			    'range_end'=>$rst_dbl_events[sizeof($rst_dbl_events)-1][$this->_key('DT1')]
      			) ;
      		}
      		
      	}elseif($on_dbld_evnts=='ignore'){
      		//Игнорируем
      		//при проверке ключей дубли будут игнорироваться при заполнении TREE_EVENTS
      	}
      }

      $query = 'DELETE FROM TREE_EVENTS';
      $query .= ' WHERE 1=1';

      if ($start)
         $query .= " AND BYHOUR >= '".date('Y_m_d_H',strtotime($start))."'";

      if ($end)
         $query .= " AND BYHOUR <= '".date('Y_m_d_H',strtotime($end))."'";

      if ($cameras)
         $query .= ' AND CAM_NR in ('.$cameras.')';

      $res = $this->_db->query($query);
      $this->_error($res);
      foreach ($tree_events as $row) {
         $query = 'INSERT INTO TREE_EVENTS ';
         $query .= '(BYHOUR, CAM_NR, IMAGE_COUNT, IMAGE_SIZE, VIDEO_COUNT, VIDEO_SIZE, AUDIO_COUNT, AUDIO_SIZE, LAST_UPDATE)';
         $query .=" VALUES ('".$row['DATE']."',".$row['CAM_NR'].','.$row['IMAGE_COUNT'].','.$row['IMAGE_SIZE'].','.$row['VIDEO_COUNT'].','.$row['VIDEO_SIZE'].','.$row['AUDIO_COUNT'].','.$row['AUDIO_SIZE'].",'".$row['LAST_UPDATE']."')";
         $res = $this->_db->query($query);
         $this->_error($res);
      }
      return array('status' => 'success');
   }

   /**
    *
    * Метод для удаления дублированных записей из EVENTS
    * @param array $dbl_evts параметры для удаления и востановления записей
    * @return кол-во исправленных записей
    */
   private function clear_dubled_evnts($dbl_evts){
	$cntr = 0;
   	foreach($dbl_evts as $key=>$val){
		//Удаляем дубли
		$query = "DELETE FROM EVENTS WHERE"
		   			." DT1='".$val['DT1']
					."' AND DT2='".$val['DT2']
		   			."' AND CAM_NR=".$val['CAM_NR']
					." AND EVT_ID=".$val['EVT_ID']
		   			." AND SESS_NR=".$val['SESS_NR']
		   			." AND FILESZ_KB=".$val['FILESZ_KB']
		   			." AND FRAMES=".$val['FRAMES']
		   			." AND ALT1=".$val['ALT1']
		   			." AND ALT2=".$val['ALT2']
		   			." AND EVT_CONT='".$val['EVT_CONT']."'; ";
	
		try {
			$res = $this->_db->query($query);
			$this->_error($res);
		}catch(Exception $err){
			return $cntr;
		}
		//востанавливаем запись
		$query = "INSERT INTO EVENTS (DT1, DT2, CAM_NR, EVT_ID, SESS_NR, FILESZ_KB, FRAMES, ALT1, ALT2, EVT_CONT) "
					." VALUES("  		
					."'".$val['DT1']
					."', '".$val['DT2']
					."', ".$val['CAM_NR']
					.", ".$val['EVT_ID']
					.", ".$val['SESS_NR']
					.", ".$val['FILESZ_KB']
					.", ".$val['FRAMES']
					.", ".$val['ALT1']
					.", ".$val['ALT2']
					.", '".$val['EVT_CONT']."'); ";
		
		try {
			$res = $this->_db->query($query);
			$this->_error($res);
		}catch(Exception $err){
			return $cntr;
		}
		$cntr++;
	   	}
   	return $cntr;
   }

/**
 *  Метод получения событий

 * @param int $camera номер камеры
 * @param int $ser_nr
 * @param string $timebegin дата начала
 * @param string $timeend дата окончания
 * @param string $order сортировка
 *
 * @return array масив событий
 */

   public function get_files($camera, $ser_nr, $timebegin, $timeend = false, $order = ''){
      $files = array();
      $query = 'SELECT '.$this->_date_part('timestamp', 'DT1').' as START, '.$this->_date_part('timestamp', 'DT1').' as FINISH,  EVT_ID, FILESZ_KB, FRAMES, ALT1 as U16_1, ALT2 as U16_2, EVT_CONT';
      $query .= ' FROM EVENTS';
      $query .= " WHERE CAM_NR=$camera AND SESS_NR=$ser_nr";
      $query .= " AND EVT_ID in (15,16,17)";
      if (empty($timeend))
         $query .= " AND ((DT1 >= '$timebegin') OR (DT2 >= '$timebegin'))";
      else
         $query .= " AND((DT1 between '$timebegin' and '$timeend') or (DT2 between '$timebegin' and '$timeend'))";
      $query .= " ORDER BY DT1 " .$order;
      $res = $this->_db->query($query);
      $this->_error($res);
      while($res->fetchInto($line)){
         $f = array();
         foreach ($line as $k=>$v) {
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
   public function get_pda_events($cams_csv, $timebegin, $timeend , $order = ''){
   	
      $files = array();
      $query = 'SELECT '.$this->_date_part('timestamp', 'E1.DT1').' as START, '.$this->_date_part('timestamp', 'E2.DT1').' as FINISH,  E1.CAM_NR, E1.SESS_NR AS SESS_NR';
      $query .= ' FROM EVENTS AS E1';
      $query .= ' LEFT JOIN EVENTS AS E2 ON (E1.SESS_NR = E2.SESS_NR AND E1.CAM_NR = E2.CAM_NR AND E1.DT1 = E2.DT2 AND E1.EVT_ID = 13 AND E2.EVT_ID = 14)';
      $query .= " WHERE E1.CAM_NR in ($cams_csv)";
      $query .= " AND E1.EVT_ID in (13)";
      $query .= " AND ((E1.DT1 between '$timebegin' and '$timeend') and (E2.DT1 is null or E2.DT1 between '$timebegin' and '$timeend'))";
      $query .= " ORDER BY E1.DT1 " .$order;

      $res = $this->_db->query($query);

      $this->_error($res);
      while($res->fetchInto($line)){
         $f = array();
         foreach ($line as $k=>$v) {
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
 * @param int $timemode фильтр даты
 * @param array $date дата
 * @param array $evt_ids тип событий
 * @param array $dayofweek дни недели
 * @param array $page лимит и номер страницы
 *
 * @return array масив событий
 */
   public function events_select($cams, $timemode = false, $date, $evt_ids, $dayofweek, $page = false){
   	
      $all_continuous_events = array(12,23,32);
      $query_continuous_events    = array_intersect($all_continuous_events,  $evt_ids);
      $query_noncontinuous_events = array_diff($evt_ids, $all_continuous_events);

      $events = array();
      $query = 'SELECT '.$this->_date_part('timestamp', 'DT1').' as UDT1, '.$this->_date_part('timestamp', 'DT2').' as UDT2,';
      $query .= ' CAM_NR, EVT_ID, SESS_NR AS SER_NR, FILESZ_KB, FRAMES, ALT1 as U16_1, ALT2 as U16_2, EVT_CONT';
      $query .= ' FROM EVENTS';
      $query .= ' WHERE';
      $query .= " CAM_NR in (0, ".implode(',', $cams).")";
      $query .= " AND (";

      if (!empty($timemode) && $timemode == 1) {
         $timebegin = sprintf('20%02s-%02u-%02u %02u:%02u:00',$date['from'][0],$date['from'][1],$date['from'][2],$date['from'][3],$date['from'][4]);
         $timeend   = sprintf('20%02s-%02u-%02u %02u:%02u:59',$date['to'][0],$date['to'][1],$date['to'][2],$date['to'][3],$date['to'][4]);

         if ( count($query_continuous_events) > 0 ) {
            $query .= " ( EVT_ID in (".implode(',', $query_continuous_events).") and ( (DT1 between '$timebegin' and '$timeend') or (DT2 between '$timebegin' and '$timeend') ))";
         }
         if ( count($query_noncontinuous_events) > 0 ) {
            if (count($query_continuous_events) > 0)
               $query .= " OR ";
            $query .= "(EVT_ID in (".implode(',', $query_noncontinuous_events).") and (DT1 between '$timebegin' and '$timeend'))";
         }
      } else {
         $timebegin = sprintf('20%02s-%02u-%02u 00:00:00',$date['from'][0],$date['from'][1],$date['from'][2]);
         $timeend   = sprintf('20%02s-%02u-%02u 23:59:59',$date['to'][0],$date['to'][1],$date['to'][2]);
         $time_in_day_begin = sprintf('%02u:%02u:00',$date['from'][3],$date['from'][4]);
         $time_in_day_end   = sprintf('%02u:%02u:59',$date['to'][3],$date['to'][4]);

         if ( count($query_continuous_events) > 0 ) {
            $query .= "( EVT_ID in (".implode(',', $query_continuous_events).") and ( ( DT1 between '$timebegin' and '$timeend' )";
            $query .= " or ( DT2 between '$timebegin' and '$timeend' ) ) and ( ".$this->_date_part('weekday', 'DT1')." in (".implode(',', $dayofweek).") or ".$this->_date_part('weekday', 'DT2')." in (".implode(',', $dayofweek).") )";
            $query .= " and ( ( ".$this->_date_part('time', 'DT1')." between '$time_in_day_begin' and '$time_in_day_end' ) or ( ".$this->_date_part('time', 'DT2')." between '$time_in_day_begin' and '$time_in_day_end' ) ))";
         }

         if ( count($query_noncontinuous_events) > 0 ) {
            if (count($query_continuous_events) > 0)
               $query .= " OR ";
            $query .= "( EVT_ID in (".implode(',', $query_noncontinuous_events).")and ( DT1 between '$timebegin' and '$timeend' )";
            $query .=" and ( ".$this->_date_part('weekday', 'DT1')." in (".implode(',', $dayofweek).") ) and ( (".$this->_date_part('time', 'DT1')." between '$time_in_day_begin' and '$time_in_day_end') ))";
         }
      }

      $query .= " )";
      $query .= ' ORDER BY DT1';
      if (!empty($page)) {
         $query .= ' LIMIT '.$page['limit'];
         $query .= ' OFFSET '.$page['offset'];
      }

      $res = $this->_db->query($query);
      $this->_error($res);
      while($res->fetchInto($line, DB_FETCHMODE_ASSOC)){
         $f = array();
         foreach ($line as $k=>$v) {
            $k = strtoupper($k);
            $f[$k] = trim($v);
         }
         $events[] = $f;
      }
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
   public function add_camera ($bind_mac, $cam_nr, $parname, $parval, $host, $user) {
      $parval = $parval == null ? 'NULL' : "'$parval'";
      $query = 'INSERT INTO CAMERAS ';
      $query .= '(BIND_MAC, CAM_NR, PARNAME, PARVAL, CHANGE_HOST, CHANGE_USER)';
      $query .=" VALUES ('".$bind_mac."',".$cam_nr.",'".$parname."',".$parval.",'".$host."','".$user."')";
      $res = $this->_db->query($query);
      $this->_error($res);
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
   public function update_camera ($bind_mac, $cam_nr, $parname, $parval, $host, $user) {
      $parval = $parval == null ? 'NULL' : "'$parval'";
      $query = 'UPDATE CAMERAS SET';
      $query .= " PARVAL = $parval";
      $query .= ", CHANGE_HOST = '$host'";
      $query .= ", CHANGE_USER = '$user'";
      $query .= " WHERE BIND_MAC = '$bind_mac'";
      $query .= " AND CAM_NR = $cam_nr";
      $query .= " AND PARNAME = '$parname'";
      $res = $this->_db->query($query);
      $this->_error($res);
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
   public function replace_camera ($bind_mac, $cam_nr, $parname, $parval, $host, $user) {
      $query = 'SELECT * FROM CAMERAS ';
      $query .= " WHERE BIND_MAC = '$bind_mac'";
      $query .= " AND CAM_NR = $cam_nr";
      $query .= " AND PARNAME = '$parname'";
      $res = $this->_db->query($query);
      $this->_error($res);
      $res->fetchInto($line);
      if (empty($line)) {
         $this->add_camera($bind_mac, $cam_nr, $parname, $parval, $host, $user);
      } else {
         $this->update_camera($bind_mac, $cam_nr, $parname, $parval, $host, $user);
      }
   }
/**
 *
 * Метод получает настройки камеры по умолчанию
 * @param int $cam_nr номер камеры
 * @param string $bind_mac 'local'
 * @return array параметры камеры
 */
   public function get_def_cam_params($cam_nr = 0, $bind_mac = 'local') {
      $cams = array();
      $query = 'SELECT CAM_NR, PARNAME, PARVAL, CHANGE_HOST, CHANGE_USER, CHANGE_TIME FROM CAMERAS';
      $query .= ' WHERE BIND_MAC=\''.$bind_mac.'\' AND (CAM_NR=0 OR CAM_NR='.$cam_nr.')';

      $res = $this->_db->query($query);

      $this->_error($res);

      while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
         $cams[] = array(
            'CAM_NR' => trim($line[$this->_key('CAM_NR')]),
            'PARAM' => trim($line[$this->_key('PARNAME')]),
            'VALUE' => trim($line[$this->_key('PARVAL')]),
            'CHANGE_HOST' => trim($line[$this->_key('CHANGE_HOST')]),
            'CHANGE_USER' => trim($line[$this->_key('CHANGE_USER')]),
            'CHANGE_TIME' => trim($line[$this->_key('CHANGE_TIME')]),

         );
      }
      return  $cams;
   }

/**
 *
 * Метод позволяет получить параметры камер
 * @param string $cams_list  список камер
 * @param string $param_list список параметров
 * @param string $bind_mac 'local'
 * @return array параметры камер
 */
   public function get_cam_params($cams_list = '', $param_list = '', $bind_mac = 'local') {
      $cams = array();
      $query = 'SELECT CAM_NR, PARNAME, PARVAL FROM CAMERAS';
      $query .= ' WHERE BIND_MAC=\''.$bind_mac.'\'';
      if (!empty($cams_list)) {
         $query .= ' AND (CAM_NR=0  OR CAM_NR in('.$cams_list.'))';
      }
      $query .= ' AND PARNAME IN ('.$param_list.') AND  PARVAL<>\'\' AND PARVAL IS NOT NULL ';
      $query .= ' ORDER BY CAM_NR';
      $res = $this->_db->query($query);
      $this->_error($res);
      while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
         $cams[] = array(
            'CAM_NR' => trim($line[$this->_key('CAM_NR')]),
            'PARAM' => trim($line[$this->_key('PARNAME')]),
            'VALUE' => trim($line[$this->_key('PARVAL')]),
         );
      }
      return  $cams;
   }
/**
 *
 * Метод позволяет получить названия камер
 * @param string $cams_list Список камер
 * @return array Список названий
 */
   public function get_cameras_name($cams_list = false) {
      $cams = array();
      /* Performing new SQL query */
      $query = 'SELECT c1.CAM_NR, c1.PARVAL as work , c2.PARVAL as text_left, '.
         'c1.CHANGE_HOST, c1.CHANGE_USER, c1.CHANGE_TIME '.
         'FROM CAMERAS c1 LEFT OUTER JOIN CAMERAS c2 '.
         'ON ( c1.CAM_NR = c2.CAM_NR AND c1.BIND_MAC=c2.BIND_MAC AND c2.PARNAME = \'text_left\' ) '.
         'WHERE c1.BIND_MAC=\'local\' AND';
      if (empty($cams_list))
         $query .= ' c1.CAM_NR>0';
      else
         $query .= " c1.CAM_NR in($cams_list)";

      $query .=' AND c1.PARNAME = \'work\' '.
         'ORDER BY c1.CAM_NR';
      $res = $this->_db->query($query);
      $this->_error($res);
      while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
         $cams[] = array(
            'CAM_NR' => trim($line[$this->_key('CAM_NR')]),
            'work' => trim($line[$this->_key('work')]),
            'text_left' => trim($line[$this->_key('text_left')]),
         );
      }
      return  $cams;

   }
/**
 *
 * Метод позволяет получить последний номер камеры
 * @param string $bind_mac 'local'
 * @return int номер камеры
 */
   public function max_cam_nr($bind_mac = 'local') {
      $query = 'SELECT MAX(CAM_NR) AS LAST_NUM FROM CAMERAS WHERE BIND_MAC=\''.$bind_mac.'\'';
      $res = $this->_db->query($query);
      $this->_error($res);
      $res->fetchInto($line);
      return isset($line[0]) ? $line[0] : false;
   }	
/**
 *
 * Метод позволяет удалить камеру
 * @param int $cam_nr номер камеры
 * @param string $bind_mac 'local'
 */

   public function delete_camera($cam_nr, $bind_mac = 'local') {
      $query = sprintf('DELETE FROM CAMERAS WHERE BIND_MAC=\''.$bind_mac.'\' AND CAM_NR=%d', $cam_nr);
      $res = $this->_db->query($query);
      $this->_error($res);
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
   public function add_layouts($display,$mon_nr,$mon_type,$mon_name, $remote_addr, $login_user, $fWINS, $vWINS,$bind_mac = 'local') {
      $query = sprintf('INSERT INTO LOCAL_LAYOUTS (BIND_MAC, DISPLAY, MON_NR, MON_TYPE, MON_NAME, %s, CHANGE_HOST, CHANGE_USER) VALUES (\'local\', \'%s\', %d, \'%s\', \'%s\', %s, \'%s\', \'%s\')',
         implode (', ',$fWINS), $display, $mon_nr, $mon_type, $mon_name, implode (', ',$vWINS), $remote_addr, $login_user);
      $res = $this->_db->query($query);
      $this->_error($res);
   }

   /**
   *
   * Метод позволяет добавить раскладку для WEB
   * @param int $mon_nr
   * @param string $mon_type
   * @param string $mon_name
   * @param string $remote_addr
   * @param string $login_user
   * @param array $fWINS
   * @param array $vWINS
   * @param string $bind_mac
   */
   public function web_add_layouts($mon_nr,$mon_type,$mon_name, $remote_addr, $login_user, $PrintCamNames, $AspectRatio, $ReconnectTimeout , $allWINS, $bind_mac = 'local') {
   	$mon_type =trim($mon_type);
   	$mon_name = trim($mon_name);
   	$remote_addr = trim($remote_addr);
   	$login_user = trim($login_user);
   	$AspectRatio = trim($AspectRatio);
   	$allWINS = trim($allWINS);
   	$bind_mac = trim($bind_mac);
   	
   	$query = sprintf('INSERT INTO WEB_LAYOUTS (BIND_MAC, MON_NR, MON_TYPE, SHORT_NAME, PRINT_CAM_NAME , PROPORTION, RECONNECT_TOUT, WINS, CHANGE_HOST, CHANGE_USER) '
   	.' VALUES (\'local\', %d, \'%s\', \'%s\', %s, \'%s\', %d, \'%s\', \'%s\', \'%s\')',
   	$mon_nr, $mon_type, $mon_name, $PrintCamNames , $AspectRatio , $ReconnectTimeout, $allWINS , $remote_addr, $login_user);
   	$res = $this->_db->query($query);
   	$this->_error($res);
   }

/**
 *
 * Метод позволяет удалить раскладку
 * @param string $display
 * @param int $mon_nr
 * @param string $bind_mac
 */
   public function delete_layouts($display, $mon_nr, $bind_mac = 'local') {
      $query = 'DELETE FROM LOCAL_LAYOUTS';
      $query .= " WHERE BIND_MAC ='$bind_mac'";
      $query .= " AND DISPLAY ='$display'";
      $query .= " AND MON_NR = $mon_nr";		
      $res = $this->_db->query($query);
      $this->_error($res);
   }

   /**
 *
 * Метод позволяет удалить раскладку для WEB
 * @param string $display
 * @param int $mon_nr
 * @param string $bind_mac
 */
   public function web_delete_layouts($display, $mon_nr, $bind_mac = 'local') {
      $query = 'DELETE FROM WEB_LAYOUTS';
      $query .= " WHERE BIND_MAC ='$bind_mac'";
//      $query .= " AND DISPLAY ='$display'";
      $query .= " AND MON_NR = $mon_nr";
      $res = $this->_db->query($query);
      $this->_error($res);
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
   public function update_layouts ($display,$mon_nr,$mon_type,$mon_name, $host, $user, $fWINS, $vWINS,$bind_mac = 'local') {
      $query = 'UPDATE LOCAL_LAYOUTS SET ';
      $query .= "MON_TYPE = '$mon_type'";
      $query .= ", MON_NAME = '$mon_name'";
      $query .= ", CHANGE_HOST = '$host'";
      $query .= ", CHANGE_USER = '$user'";

      for ($i = 0; $i < count($vWINS); $i++) {
         if ( !empty($vWINS[$i]) /* неважно что '0' даст true, 0-вой камеру тут не будет */ )
            $query .= ", {$fWINS[$i]} = {$vWINS[$i]}";
         else
            $query .= ", {$fWINS[$i]} = NULL";
      }

      $query .= " WHERE BIND_MAC ='$bind_mac'";
      $query .= " AND DISPLAY ='$display'";
      $query .= " AND MON_NR = $mon_nr";

      $res = $this->_db->query($query);
      $this->_error($res);
   }

/**
 *
 * Метод позволяет обновить данные раскладки для WEB  в БД
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
   public function web_update_layouts($mon_nr,$mon_type,$mon_name, $host, $user, $PrintCamNames, $AspectRatio, $ReconnectTimeout, $allWINS,  $bind_mac = 'local') {
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

      $res = $this->_db->query($query);
      $this->_error($res);
   }

   /**
   *
   * Метод позволяет установить раскладку по умолчанию для WEB
   * @param unknown_type $mon_nr - номер раскладки, устанавливаемый по умолчанию
   */
   public function web_set_def_layout($mon_nr) {
   	$query = 'UPDATE WEB_LAYOUTS SET ';
   	$query .= "IS_DEFAULT = 0";
   	$res = $this->_db->query($query);
   	$this->_error($res);
   	
   	$query = 'UPDATE WEB_LAYOUTS SET ';
   	$query .= "IS_DEFAULT = 1";
   	$query .= " WHERE MON_NR = $mon_nr";

   	$res = $this->_db->query($query);
   	$this->_error($res);
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
   public function replace_layouts ($display,$mon_nr,$mon_type,$mon_name, $host, $user, $fWINS, $vWINS, $bind_mac = 'local') {
      $query = 'SELECT * FROM LOCAL_LAYOUTS ';
      $query .= " WHERE BIND_MAC = '$bind_mac'";
      $query .= " AND MON_NR = $mon_nr";
      $query .= " AND DISPLAY = '$display'";
      $res = $this->_db->query($query);
      $this->_error($res);
      $res->fetchInto($line);
      if (empty($line))
         $this->add_layouts($display,$mon_nr,$mon_type,$mon_name, $host, $user, $fWINS, $vWINS);
      else
         $this->update_layouts($display,$mon_nr,$mon_type,$mon_name, $host, $user, $fWINS, $vWINS);
   }	

   /**
   *
   * Метод добавляет или обновляет параметры раскладки для WEB
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
   public function web_replace_layouts ($mon_nr,$mon_type,$mon_name, $host, $user, $PrintCamNames, $AspectRatio, $ReconnectTimeout, $allWINS, $bind_mac = 'local') {
   	$mon_type = trim($mon_type);
   	$mon_name = trim($mon_name);
   	$host = trim($host);
   	$user = trim($user);
   	$AspectRatio = trim($AspectRatio);
   	$allWINS = trim($allWINS);
   	$bind_mac= trim($bind_mac);
   	
   	$query = 'SELECT * FROM WEB_LAYOUTS ';
   	$query .= " WHERE BIND_MAC = '$bind_mac'";
   	$query .= " AND MON_NR = $mon_nr";

   	$res = $this->_db->query($query);
   	$this->_error($res);
   	$res->fetchInto($line);
   	if (empty($line))
   	$this->web_add_layouts($mon_nr,$mon_type,$mon_name, $host, $user, $PrintCamNames, $AspectRatio, $ReconnectTimeout, $allWINS);
   	else
   	$this->web_update_layouts( $mon_nr,$mon_type,$mon_name, $host, $user, $PrintCamNames, $AspectRatio, $ReconnectTimeout, $allWINS);
   }

/**
 *
 * Метод позволяет получить параметры раскладки
 * @param string $display
 * @param int $mon_nr
 * @param string $bind_mac
 * @return array параметры
 */
   public function get_monitor($display, $mon_nr, $bind_mac = 'local') {
      $query = 'SELECT MON_NR, MON_TYPE, MON_NAME, IS_DEFAULT, ' .
         'WIN1, WIN2, WIN3, WIN4, WIN5, WIN6, WIN7, WIN8, WIN9,
         WIN10, WIN11, WIN12, WIN13, WIN14, WIN15, WIN16,
         WIN17, WIN18, WIN19, WIN20, WIN21, WIN22, WIN23,
         WIN24, WIN25, WIN26, WIN27, WIN28, WIN29, WIN30,
         WIN31, WIN32, WIN33, WIN34, WIN35, WIN36, WIN37,
         WIN38, WIN39, WIN40 '.
         'CHANGE_HOST, CHANGE_USER, CHANGE_TIME '.
         'FROM LOCAL_LAYOUTS '.
         'WHERE BIND_MAC=\''.$bind_mac.'\' AND DISPLAY=\''.$display.'\' AND MON_NR='.$mon_nr;
      $res = $this->_db->query($query);
      $this->_error($res);
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
   public function web_get_monitor($mon_nr, $bind_mac = 'local') {
   	
      $query = 'SELECT MON_NR, MON_TYPE, SHORT_NAME, IS_DEFAULT, WINS,' .
         'CHANGE_HOST, CHANGE_USER, CHANGE_TIME, PRINT_CAM_NAME, PROPORTION, RECONNECT_TOUT '.
         'FROM WEB_LAYOUTS '.
         'WHERE BIND_MAC=\''.$bind_mac.'\' AND MON_NR='.$mon_nr;

      $res = $this->_db->query($query);
      $this->_error($res);
      $res->fetchInto($line);
      return $line;
   }

/**
 *
 * Метод позволяет получить параметры всех раскладок
 * @param string $bind_mac
 * @return array раскладки
 */
   public function get_layouts($bind_mac = 'local'){
      $mon = array();
      $query = 'SELECT * FROM LOCAL_LAYOUTS';
      $query .= " WHERE BIND_MAC='$bind_mac'";
      $query .= ' ORDER BY MON_NR';

      $res = $this->_db->query($query);
      $this->_error($res);
      while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
         $m = array();
         foreach ($line as $k=>$v) {
            $k = strtoupper($k);
            $m[$k] = trim($v);
         }
         $mon[] = $m;
      }
      return  $mon;
   }

/**
 *
 * Метод позволяет получить параметры всех раскладок для WEB
 * или WEB-раскладок, разрешенных для пользователя
 * @param string $bind_mac
 * @return array раскладки
 */
   public function web_get_layouts($user=NULL, $bind_mac = 'local'){
   	
      $mon = array();
      $query = 'SELECT * FROM WEB_LAYOUTS';

      $allowed_layouts=array();
      //номер раскладки, указанный в пользовательских настройках первым, устанавливается по умолчанию
      $def_num = null;

      //Если пользователь указан - формируем запрос о разрешенных раскладках
      if($user!=NULL){
      	$sub_query = sprintf("SELECT ALLOW_LAYOUTS FROM USERS WHERE USER_LOGIN='%s'", $user);
      	$sub_res = $this->_db->query($sub_query);
      	$this->_error($allowed_layouts);
		//Определяем разрешенные раскладки
      	while ($sub_res->fetchInto($vl, DB_FETCHMODE_ASSOC)) {
      		$l = array();
      		foreach ($vl as $k=>$v) {
      			$k = strtoupper($k);
      			$l[$k] = trim($v);
      		}
      		$lo[] = $l;
      	}
      	
      	$allowed_layouts = explode(',', trim( $lo[0]["ALLOW_LAYOUTS"]));
      	
      	//Первая указанная раскладка используется по умолчанию
      	$def_num = $allowed_layouts[0];

      	//если разрешены все раскладки(пустое поле разрешенных раскладок - обнуляем пользователя и раскладку по умолчанию)
		if(trim( $lo[0]["ALLOW_LAYOUTS"])==''||$allowed_layouts[0]==''){
			$user = null;
			$def_num = null;
		}
		else{
			
			//определяем перечень разрешенных раскладок пользователя и формируем соотв. запрос
			$lts = "'";
			$lts =$lts.implode("', '", $allowed_layouts)."'";
			
			$query .= " WHERE BIND_MAC='$bind_mac' AND MON_NR IN ($lts)";
			$query .= ' ORDER BY MON_NR';
		}
      }

	  //Если пользователь не указан или не заданны конкретные раскладки - выбираем все раскладки
      if($user==NULL){
      	$query .= " WHERE BIND_MAC='$bind_mac'";
      	$query .= ' ORDER BY MON_NR';
      }

      $res = $this->_db->query($query);
      $this->_error($res);
      while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
         $m = array();
         foreach ($line as $k=>$v) {
            $k = strtoupper($k);
            $m[$k] = trim($v);
         }
         $mon[] = $m;
      }

      //если в пользовательских настройках указана раскладка по умолчанию
      if($def_num!=null && $user!=NULL){
      	foreach ($mon as $key=>$val){
      		$mon[$key]["IS_DEFAULT"]="0";
      		if ($mon[$key]['MON_NR']==$def_num){
      			$mon[$key]["IS_DEFAULT"]='1';
      		}
      	}
      }
      return  $mon;
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
 * @param string $u_forced_saving_limit Максимальная длительность принудительной записи (по команде) в минутах
 * @param string $sessions_per_user Ограничение количества одновременных просмотров (камер) пользователем
 * @param string $limit_fps limit_fps, кадров в секунду, [1-25] или sec/frames
 * @param string $nonmotion_fps nonmotion_fps, примеры допустимых значений: "1" - 1 кадр в 1 сек.; "2/1" - 1 кадр каждые 2 секунды.
 * @param string $limit_kbps limit_kbps, Kбит/сек
 * @param string $session_time session_time - по времени, в минутах
 * @param string $session_volume session_volume - по "закаченному" объёму, в МегаБайтах (10242)
 * @param string $u_longname ФИО
 * @param string $remote_addr хост на котором добавляют
 * @param string $login_user пользователь, который добавляет
 * @return bool результат добавления
 */
   public function add_user($u_host, $u_name, $passwd, $groups, $guest, $pda, $u_devacl, $u_layouts, $u_forced_saving_limit, $sessions_per_user,$limit_fps,$nonmotion_fps, $limit_kbps, $session_time, $session_volume, $u_longname, $remote_addr, $login_user) {
   	$query = sprintf('INSERT INTO USERS
         ( ALLOW_FROM, USER_LOGIN, PASSWD, STATUS, GUEST, PDA, ALLOW_CAMS, ALLOW_LAYOUTS, MAX_FORCED_REC_MINUTES, MAX_MEDIA_SESSIONS_NB,
         MAX_VIDEO_FPS, MAX_VIDEO_NONMOTION_FPS, MAX_MEDIA_SESSION_RATE_KB,
         MAX_MEDIA_SESSION_MINUTES, MAX_MEDIA_SESSION_VOLUME_MB,

         LONGNAME, CHANGE_HOST, CHANGE_USER, CHANGE_TIME)
         VALUES ( %s, %s, %s, %u, %b, %b, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW())',
            sql_format_str_val($u_host),
            sql_format_str_val($u_name),
            $this->_crypt($passwd),
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
            sql_format_str_val($login_user));

      $res = $this->_db->query($query);
      return !$this->_error($res, false);
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
 * @param string $u_forced_saving_limit Максимальная длительность принудительной записи (по команде) в минутах
 * @param string $sessions_per_user Ограничение количества одновременных просмотров камер пользователем
 * @param string $limit_fps limit_fps, кадров в секунду, [1-25] или sec/frames
 * @param string $nonmotion_fps nonmotion_fps, примеры допустимых значений: "1" - 1 кадр в 1 сек.; "2/1" - 1 кадр каждые 2 секунды.
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

   public function update_user($u_host,$u_name,$passwd, $groups, $guest, $pda, $u_devacl, $u_layouts, $u_forced_saving_limit, $sessions_per_user, $limit_fps, $nonmotion_fps, $limit_kbps, $session_time, $session_volume, $u_longname, $remote_addr, $login_user, $old_u_host,$old_u_name){
      $query = sprintf(
         'UPDATE USERS SET ALLOW_FROM=%s, USER_LOGIN=%s, PASSWD=%s, STATUS=%d, GUEST=%b, PDA=%b, ALLOW_CAMS=%s, ALLOW_LAYOUTS=%s ,MAX_FORCED_REC_MINUTES=%s, MAX_MEDIA_SESSIONS_NB=%s, MAX_VIDEO_FPS=%s, MAX_VIDEO_NONMOTION_FPS=%s, MAX_MEDIA_SESSION_RATE_KB=%s, MAX_MEDIA_SESSION_MINUTES=%s, MAX_MEDIA_SESSION_VOLUME_MB=%s, LONGNAME=%s, CHANGE_HOST=%s, CHANGE_USER=%s, CHANGE_TIME=NOW() WHERE ALLOW_FROM=%s AND USER_LOGIN=%s',
         sql_format_str_val($u_host),
         sql_format_str_val($u_name),
         $this->_crypt($passwd),
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
         sql_format_str_val($old_u_name));
      $res = $this->_db->query($query);
      $res = $this->_db->query($query);
      return !$this->_error($res, false);
   }

/**
 *
 * Метод позволяет удалить пользователя
 * @param string $u_name логин
 * @param string $u_host хост
 * @param int $u_status статус
 */
   public function delete_user($u_name, $u_host, $u_status){
      $query = sprintf('DELETE FROM USERS WHERE USER_LOGIN=%s AND ALLOW_FROM=%s AND STATUS=%u',
   		sql_format_str_val($u_name),
      	sql_format_str_val( $u_host),
       	$u_status);

      $res = $this->_db->query($query);
      $this->_error($res);
   }
/**
 *
 * Метод позволяет получить пароль пользователя
 * @param string $u_name логин
 * @param string $hosts хост
 * @return string пароль
 */
   public function get_user_passwd($u_name, $hosts) {
      $query = sprintf("SELECT PASSWD FROM USERS WHERE ALLOW_FROM in(%s) AND USER_LOGIN='%s'", "'".implode("','",$hosts)."'", $u_name);
      $res = $this->_db->query($query);
      $this->_error($res);
      $res->fetchInto($line);
      return isset($line[0]) ? trim($line[0]) : false;
   }
/**
 *
 * Метод позволяет обновить пароль пользователя
 * @param string $u_name логин
 * @param string $u_pass пароль
 * @param string $hosts хост
 */
   public function update_user_passwd($u_name, $u_pass, $hosts) {
      $query = sprintf("UPDATE USERS SET PASSWD=%s	 WHERE ALLOW_FROM in(%s) AND USER_LOGIN='%s'",
         $this->_crypt($u_pass),
         "'".implode("','",$hosts)."'",
         $u_name);		
      $res = $this->_db->query($query);
      $this->_error($res);
      return true;
   }
/**
 * Метод позволяет получить пользователей
 *
 * @param int $status статус
 * @return array масив пользователей
 */
   public function get_users($status = false) {
      $users = array();
      $query = 'SELECT ALLOW_FROM, USER_LOGIN, GUEST, PDA, PASSWD, STATUS, ALLOW_CAMS, ALLOW_LAYOUTS, MAX_FORCED_REC_MINUTES,  MAX_MEDIA_SESSIONS_NB, MAX_VIDEO_FPS, MAX_VIDEO_NONMOTION_FPS, MAX_MEDIA_SESSION_RATE_KB, MAX_MEDIA_SESSION_MINUTES, MAX_MEDIA_SESSION_VOLUME_MB,LONGNAME, CHANGE_HOST, CHANGE_USER, CHANGE_TIME '.
         'FROM USERS ';
      if ($status)
         $query .= "WHERE STATUS = $status ";
      $query .=  'ORDER BY ALLOW_FROM, USER_LOGIN';

      $res = $this->_db->query($query);
      $this->_error($res);
      while ($res->fetchInto($line, DB_FETCHMODE_ASSOC)) {
         $users[] =array(
            'HOST' => trim($line[$this->_key('ALLOW_FROM')]),
            'USER' => trim($line[$this->_key('USER_LOGIN')]),
            'PASSWD' => trim($line[$this->_key('PASSWD')]),
            'GUEST' => trim($line[$this->_key('GUEST')]),
         	'PDA' => trim($line[$this->_key('PDA')]),
            'STATUS' => trim($line[$this->_key('STATUS')]),
            'ALLOW_CAMS' => trim($line[$this->_key('ALLOW_CAMS')]),
            'ALLOW_LAYOUTS' => trim($line[$this->_key('ALLOW_LAYOUTS')]),
            'MAX_FORCED_REC_MINUTES' => trim($line[$this->_key('MAX_FORCED_REC_MINUTES')]),
            'MAX_MEDIA_SESSIONS_NB' => trim($line[$this->_key('MAX_MEDIA_SESSIONS_NB')]),
            'MAX_VIDEO_FPS' => trim($line[$this->_key('MAX_VIDEO_FPS')]),
            'MAX_VIDEO_NONMOTION_FPS' => trim($line[$this->_key('MAX_VIDEO_NONMOTION_FPS')]),
            'MAX_MEDIA_SESSION_RATE_KB' => trim($line[$this->_key('MAX_MEDIA_SESSION_RATE_KB')]),
            'MAX_MEDIA_SESSION_MINUTES' => trim($line[$this->_key('MAX_MEDIA_SESSION_MINUTES')]),
            'MAX_MEDIA_SESSION_VOLUME_MB' => trim($line[$this->_key('MAX_MEDIA_SESSION_VOLUME_MB')]),
            'LONGNAME' => trim($line[$this->_key('LONGNAME')]),
            'CHANGE_HOST' => trim($line[$this->_key('CHANGE_HOST')]),
            'CHANGE_USER' => trim($line[$this->_key('CHANGE_USER')]),
            'CHANGE_TIME' => trim($line[$this->_key('CHANGE_TIME')]),
         );
      }

      return $users;
   }

   private function _key($str){
      if ($this->_dbtype == 'pgsql')
         return strtolower($str);
      return $str;
   }

   private function _date_part($type, $value){
      if ($this->_dbtype == 'pgsql') {
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
      $str = str_replace('%%', $value,$str);

      return $str;
   }
   private function _date_format($value) {
      if ($this->_dbtype == 'pgsql')
         $str = "to_char(%%, 'yyyy_mm_dd_hh24')";
      else
         $str = "DATE_FORMAT(%%, '%Y_%m_%d_%H')";
      $str = str_replace('%%', $value,$str);
      return $str;
   }

   private function _timediff($d1, $d2){
      if ($this->_dbtype == 'pgsql')
         $str = $d1."-".$d2;
      else
         $str = "TIMEDIFF(".$d1." , ".$d2.")";
      return $str;
   }

   private function _crypt($value) {
      if (empty($value))
         return "''";

      if ($this->_dbtype == 'pgsql')
         $str = "crypt('%%', 'av')";
      else
         $str = "encrypt('%%')";
      $str = str_replace('%%', $value,$str);
      return $str;
   }
}
?>
