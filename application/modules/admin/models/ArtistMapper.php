<?php

class Admin_Model_ArtistMapper
{
	protected $_dbTable;
	protected $_tableName = 'artist';
	
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
			$this->setDbTable('Admin_Model_DbTable_Artist');
		}
		return $this->_dbTable;
	}
	
	public function save($data)
	{
		$artist = new Admin_Model_Artist($data);
		if (null === ($id = $artist->getArtistId())) {
			$this->getDbTable()->insert($data);
		} else {
			$this->getDbTable()->update($data, array('artist_id = ?' => $id));
		}
	}
	
	public function saveMany($data, $ids = null)
	{	$table = $this->getDbTable();
		$where = $table->getAdapter()->quoteInto('FIND_IN_SET(artist_id,(?))', $ids);
		$table->update($data, $where);
	}
	
	public function find($id, Admin_Model_Artist $artist)
	{
		$result = $this->getDbTable()->find($id);
		if (0 == count($result)) {
			return;
		}
		$row = $result->current();
		
		$artist->setArtistId($row->artist_id)		
		->setArtistName($row->artist_name)
		//->setWikiName($row->wiki_name)
		->setLocked($row->locked)
		->setHasPhoto($row->has_photo)
		->setImage($row->image)
		->setCreatedOn($row->created_on);
	}
	
	public function fetchAll($returnType = 'object')
	{
		$resultSet = $this->getDbTable()->fetchAll();
		if($returnType == 'array'){
			return $resultSet->toArray();
		}
		$entries   = array();
		foreach ($resultSet as $row) {
			$entry = new Admin_Model_Artist();
			$entry->setArtistId($row->artist_id)
			->setArtistName($row->artist_name)
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
		$select->from(array('a' => $this->_tableName));
		if($search){          	      	
        	$select->where('artist_name LIKE ?', "$search%");
        }
        $select->where('locked = ?', 0);
		$select->order('artist_name'); 
		$adapter = new Admin_Model_Paginator_ArtistAdapter($select);
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
			$fields = array('artist_id', 'artist_name', 'locked', 'has_photo');
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
			$fields = array('artist_id', 'artist_name', 'locked', 'has_photo');
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

		$adapter = new Admin_Model_Paginator_ArtistAdapter($select);		
		$paginator = new Zend_Paginator($adapter);
		$paginator->setCurrentPageNumber($pagingOptions['CURRENT_PAGE_NUMBER']);
		$paginator->setItemCountPerPage($pagingOptions['items_count_per_page']);
		$paginator->setPageRange($pagingOptions['page_range']);
		
		return $paginator;        
	}
	
	public function findOneBy($property, $value, Admin_Model_Artist $artist){
		$property = lcfirst($property);
		$filter  = new Zend_Filter_Word_CamelCaseToUnderscore();
		$property = $filter->filter($property);
		$table = $this->getDbTable();
		//$fields  = array('music_id', 'music_title', 'has_photo');
		$where = array($property . ' = ?' => "$value");

		$row = $table->fetchRow($where);
		$artist->setArtistId($row->artist_id)		
		->setArtistName($row->artist_name)
		//->setWikiName($row->wiki_name)
		->setLocked($row->locked)
		->setHasPhoto($row->has_photo)
		->setImage($row->image)
		->setCreatedOn($row->created_on);
	}

	public function adminAutoComplete($match){
		$fields  = array('id' => 'artist_id', 'value' => 'artist_name', 'has_photo' => 'has_photo');
		$where = empty($match) ? null: array('artist_name LIKE ?' => "$match%");
		$order  = array('artist_name');
		return $this->fetchCol($fields, $where, $order);
	}
	
	public function siteAutoComplete($match){
		$fields  = array('id' => 'artist_id', 'value' => 'artist_name', 'has_photo' => 'has_photo');
		$where = empty($match) ? null: array('artist_name LIKE ?' => "$match%");
		$where['locked = ?'] = 0;
		$order  = array('artist_name');
		return $this->fetchCol($fields, $where, $order);
	}
	
	public function adminView($pagingOptions, $filters = null, $order){
		$mapper = new Admin_Model_ArtistMapper();	
		$fields = array('artist_id', 'artist_name', 'locked', 'has_photo');
		if(!$order){
			//$filters['locked'] = array('locked = ?', 0);
			//$filters['image'] = array('image = ?', '');
			$order = array('artist_name asc');		
		}			
		return $mapper->fetchColView($fields, $filters, $order, $pagingOptions);
	}

}

