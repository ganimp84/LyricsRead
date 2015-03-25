<?php

class Admin_Model_MusicLyricistMapper
{
	protected $_dbTable;
	protected $_tableName = 'music_lyricist';
	
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
			$this->setDbTable('Admin_Model_DbTable_MusicLyricist');
		}
		return $this->_dbTable;
	}
	
	public function save($data)
	{
		$music_lyricist = new Admin_Model_MusicLyricist($data);		
		$id = $this->getDbTable()->insert($data);		
	}
	
	public function delete($id)
	{
		$where = $this->getDbTable()->getAdapter()->quoteInto('music_id = ?', $id);		
		$id = $this->getDbTable()->delete($where);		
	}
	
}

