<?php

class Admin_Model_MusicVisitLogMapper
{
	protected $_dbTable;
	protected $_tableName = 'music_visit_log';
	
	function __call($method, $arguments){
		if (@preg_match('/(find(?:One|All)?By)(.+)/', $method, $match)) {
			return $this->{$match[1]}($match[2], $arguments[0], $arguments[1], $arguments[2]);
		}
	}
	
	public function setDbTable($dbTable)
	{		
		if (is_string($dbTable)) {
			$dbTable = new $dbTable();
		}
		if (!$dbTable instanceof Zend_Db_Table_Abstract) {
			throw new Exception('Invalid table data gateway provided');
		}
		$this->_dbTable = $dbTable;
		return $this;
	}

	public function getDbTable()
	{
		if (null === $this->_dbTable) {
			$this->setDbTable('Admin_Model_DbTable_MusicVisitLog');
		}
		return $this->_dbTable;
	}
	
	public function save($data)
	{
		$this->getDbTable()->insert($data);
	}	
	
	public function getTopMusics($interval = 7, $limit = 15){
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		$fields = array('count(1) as num_visits', 'music_id');
		
		$select->from(array('mvl' => $this->_tableName), $fields);
		$select->join(array('m' => 'music'), 'm.music_id = mvl.music_id', array('music_title', 'image'));
		$select->join(array('a' => 'album'), 'm.album_id = a.album_id', array('album_name'));
		$select->where('visit_time >= ?', new Zend_Db_Expr("UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL {$interval} DAY))"));
		$select->order('num_visits desc');
		$select->group('music_id');
		$select->limit($limit, 0);
		
		//Zend_Debug::dump($select->__toString());
		return $table->fetchAll($select)->toArray();
	}
}

