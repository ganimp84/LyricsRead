<?php

class Admin_Model_ArtistVisitLogMapper
{
	protected $_dbTable;
	protected $_tableName = 'artist_visit_log';
	
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
			$this->setDbTable('Admin_Model_DbTable_ArtistVisitLog');
		}
		return $this->_dbTable;
	}
	
	public function save($data)
	{
		$this->getDbTable()->insert($data);
	}	
	
	public function getTopArtists($interval = 7, $limit = 12){
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		$fields = array('count(1) as num_visits', 'artist_id');
		
		$select->from(array('avl' => $this->_tableName), $fields);
		$select->join(array('a' => 'artist'), 'a.artist_id = avl.artist_id', array('artist_name', 'image'));
		$select->where('visit_time >= ?', new Zend_Db_Expr("UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL {$interval} DAY))"));
		$select->order('num_visits desc');
		$select->group('artist_id');
		$select->limit($limit, 0);
		
		//Zend_Debug::dump($select->__toString());exit;
		return $table->fetchAll($select)->toArray();
	}
	
}

