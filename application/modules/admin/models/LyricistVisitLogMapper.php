<?php

class Admin_Model_LyricistVisitLogMapper
{
	protected $_dbTable;
	protected $_tableName = 'lyricist_visit_log';
	
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
			$this->setDbTable('Admin_Model_DbTable_LyricistVisitLog');
		}
		return $this->_dbTable;
	}
	
	public function save($data)
	{
		$this->getDbTable()->insert($data);
	}	
	
	public function getTopLyricists($interval = 7, $limit = 12){
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		$fields = array('count(1) as num_visits', 'lyricist_id');
		
		$select->from(array('mvl' => $this->_tableName), $fields);
		$select->join(array('m' => 'lyricist'), 'm.lyricist_id = mvl.lyricist_id', array('lyricist_name', 'image'));
		$select->where('visit_time >= ?', new Zend_Db_Expr("UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL {$interval} DAY))"));
		$select->order('num_visits desc');
		$select->group('lyricist_id');
		$select->limit($limit, 0);
		
		//Zend_Debug::dump($select->__toString());
		return $table->fetchAll($select)->toArray();
	}
}

