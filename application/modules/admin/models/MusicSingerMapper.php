<?php

class Admin_Model_MusicSingerMapper
{
	protected $_dbTable;
	protected $_tableName = 'music_singer';
	
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
			$this->setDbTable('Admin_Model_DbTable_MusicSinger');
		}
		return $this->_dbTable;
	}
	
	public function save($data)
	{
		$music_singer = new Admin_Model_MusicSinger($data);		
		$id = $this->getDbTable()->insert($data);		
	}
	
	public function delete($id)
	{
		$where = $this->getDbTable()->getAdapter()->quoteInto('music_id = ?', $id);		
		$id = $this->getDbTable()->delete($where);		
	}
	
}

