<?php

class Admin_Model_ComposerVisitLogMapper
{
	protected $_dbTable;
	protected $_tableName = 'composer_visit_log';
	
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
			$this->setDbTable('Admin_Model_DbTable_ComposerVisitLog');
		}
		return $this->_dbTable;
	}
	
	public function save($data)
	{
		$this->getDbTable()->insert($data);
	}	
	
	public function getTopComposers($interval = 7, $limit = 12){
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		$fields = array('count(1) as num_visits', 'composer_id');
		
		$select->from(array('cvl' => $this->_tableName), $fields);
		$select->join(array('c' => 'composer'), 'c.composer_id = cvl.composer_id', array('composer_name', 'image'));
		$select->where('visit_time >= ?', new Zend_Db_Expr("UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL {$interval} DAY))"));
		$select->order('num_visits desc');
		$select->group('composer_id');					
		$select->limit($limit, 0);
		
		//Zend_Debug::dump($select->__toString());
		return $table->fetchAll($select)->toArray();
	}
	
}

