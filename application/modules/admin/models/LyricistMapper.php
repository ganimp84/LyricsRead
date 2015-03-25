<?php

class Admin_Model_LyricistMapper
{
	protected $_dbTable;
	protected $_tableName = 'lyricist';
	
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
			$this->setDbTable('Admin_Model_DbTable_Lyricist');
		}
		return $this->_dbTable;
	}
	
	public function save($data)
	{
		$lyricist = new Admin_Model_Lyricist($data);
		
		if (null === ($id = $lyricist->getLyricistId())) {
			$this->getDbTable()->insert($data);
		} else {
			$this->getDbTable()->update($data, array('lyricist_id = ?' => $id));
		}
	}
	
	public function saveMany($data, $ids = null)
	{	$table = $this->getDbTable();
		$where = $table->getAdapter()->quoteInto('FIND_IN_SET(lyricist_id,(?))', $ids);
		$table->update($data, $where);
	}
	
	public function insertMany($data)
	{	$table = $this->getDbTable();
		$stmt = $table->getAdapter()->prepare('INSERT INTO '.$this->_tableName.' (lyricist_name) VALUES (?)');
		if(is_array($data)){
			foreach($data as $row){
				$stmt->execute( array($row['lyricist_name']) ); 
			}	
		}		
	}
	
	public function find($id, Admin_Model_Lyricist $lyricist)
	{
		$result = $this->getDbTable()->find($id);
		if (0 == count($result)) {
			return;
		}
		$row = $result->current();
		
		$lyricist->setLyricistId($row->lyricist_id)		
		->setLyricistName($row->lyricist_name)
		->setLocked($row->locked)
		->setHasPhoto($row->has_photo)
		->setImage($row->image)
		->setCreatedOn($row->created_on);
	}
	
	public function fetchRow($lyricist_name)
	{
		$db = $this->getDbTable();
		$select = $db->select()->where('lyricist_name = ?', $lyricist_name);
		return $db->fetchRow($select);
	}
	
	public function fetchAll($returnType = 'object')
	{
		$resultSet = $this->getDbTable()->fetchAll();
		if($returnType == 'array'){
			return $resultSet->toArray();
		}
		$entries   = array();
		foreach ($resultSet as $row) {
			$entry = new Admin_Model_Lyricist();
			$entry->setLyricistId($row->lyricist_id)
			->setLyricistName($row->lyricist_name)
			->setLocked($row->locked)
			->setHasPhoto($row->has_photo)
			->setImage($row->image)
			->setCreatedOn($row->created_on);
			$entries[] = $entry;
		}
		return $entries;
	}
	
	public function fetchAllList($pagingOptions, $search = null)
	{
		$db = $this->getDbTable()->getAdapter();
		$select = $db->select();
		$select->from(array('l' => $this->_tableName));	
		if($search){          	      	
        	$select->where('lyricist_name LIKE ?', "$search%");
        }
        $select->where('locked = ?', 0);
		$select->order('lyricist_name'); 
		$adapter = new Admin_Model_Paginator_LyricistAdapter($select);
		$paginator = new Zend_Paginator($adapter);
		//Zend_Debug::dump($paginator, $label = 'Select:', $echo = true);exit;
			
		$paginator->setCurrentPageNumber($pagingOptions['CURRENT_PAGE_NUMBER']);
		$paginator->setItemCountPerPage($pagingOptions['items_count_per_page']);
		$paginator->setPageRange($pagingOptions['page_range']);
		return $paginator;
	}
	
	public function fetchCol($fields = null, $where = null, $order = null, $limit = null)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		if(is_null($fields)){
			$fields = array('lyricist_id', 'lyricist_name', 'locked', 'has_photo');
		}
		$select->from($table, $fields);
		if(is_array ($where)){				
			foreach ($where as $field => $value){				
				$select->where($field, $value);
			}
		}
		if(is_array ($order)){				
			$select->order($order);
		}
		if(is_array ($limit)){							
			$select->limit($limit['count'], $limit['offset']);			
		}
		
		return $table->fetchAll($select);
        
	}
	
	public function fetchColView($fields = null, $filters = null, $order = null, $pagingOptions = null)
	{
		$db = $this->getDbTable()->getAdapter();
        $select = $db->select();
		if(is_null($fields)){
			$fields = array('lyricist_id', 'lyricist_name', 'locked', 'has_photo');
		}
		$select->from(array($this->_tableName), $fields);
		
		if(is_array ($filters)){				
			foreach ($filters as $field => $filter){	
				if(is_array($filter))			
				$select->where($filter[0], $filter[1]);
			}
		}
		if(is_array ($order)){				
			$select->order($order);			
		}

		$adapter = new Admin_Model_Paginator_LyricistAdapter($select);		
		$paginator = new Zend_Paginator($adapter);
		$paginator->setCurrentPageNumber($pagingOptions['CURRENT_PAGE_NUMBER']);
		$paginator->setItemCountPerPage($pagingOptions['items_count_per_page']);
		$paginator->setPageRange($pagingOptions['page_range']);
		
		return $paginator;        
	}
	
	public function findOneBy($property, $value, Admin_Model_Lyricist $lyricist){
		$property = lcfirst($property);
		$filter  = new Zend_Filter_Word_CamelCaseToUnderscore();
		$property = $filter->filter($property);
		$table = $this->getDbTable();
		//$fields  = array('music_id', 'music_title', 'has_photo');
		$where = array($property . ' = ?' => "$value");
		
		$row = $table->fetchRow($where);

		$lyricist->setLyricistId($row->lyricist_id)		
		->setLyricistName($row->lyricist_name)
		->setLocked($row->locked)
		->setHasPhoto($row->has_photo)
		->setImage($row->image)
		->setCreatedOn($row->created_on);
	}
	
	public function adminAutoComplete($match){
		$fields  = array('id' => 'lyricist_id', 'value' => 'lyricist_name', 'has_photo' => 'has_photo');
		$where = empty($match) ? null: array('lyricist_name LIKE ?' => "$match%");
		$order  = array('lyricist_name');
		return $this->fetchCol($fields, $where, $order);
	}
	
	public function siteAutoComplete($match){
		$fields  = array('id' => 'lyricist_id', 'value' => 'lyricist_name', 'has_photo' => 'has_photo');
		$where = empty($match) ? null: array('lyricist_name LIKE ?' => "$match%");
		$where['locked = ?'] = 0;
		$order  = array('lyricist_name');
		return $this->fetchCol($fields, $where, $order);
	}
	
	public function adminView($pagingOptions, $filters = null, $order){
		$mapper = new Admin_Model_LyricistMapper();	
		$fields = array('lyricist_id', 'lyricist_name', 'locked', 'has_photo');
		if(!$order){
			$order = array('lyricist_name asc');		
		}			
		return $mapper->fetchColView($fields, $filters, $order, $pagingOptions);
	}

}

