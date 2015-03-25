<?php

class Admin_Model_TrailerMapper
{
	protected $_dbTable;
	protected $_tableName = 'trailer';
	protected $_totalItemCount= 0;
	
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
			$this->setDbTable('Admin_Model_DbTable_Trailer');
		}
		return $this->_dbTable;
	}

	public function save($data)
	{
		$trailer = new Admin_Model_Trailer($data);
		if (null === ($id = $trailer->getTrailerId())) {
			$this->getDbTable()->insert($data);
		} else {
			$this->getDbTable()->update($data, array('trailer_id = ?' => $id));
		}
	}
	
	public function saveMany($data, $ids = null)
	{	
		$table = $this->getDbTable();
		$where = $table->getAdapter()->quoteInto('FIND_IN_SET(trailer_id,(?))', $ids);
		$table->update($data, $where);
	}
	
	public function insertMany($data)
	{	$table = $this->getDbTable();
		$stmt = $table->getAdapter()->prepare('INSERT INTO '.$this->_tableName.' (video, album_id) VALUES (?, ?)');
		if(is_array($data)){
			foreach($data as $row){
				$stmt->execute( array($row['video'], $row['album_id']) ); 
			}	
		}		
	}

	public function find($id, Admin_Model_Trailer $trailer)
	{
		$result = $this->getDbTable()->find($id);
		if (0 == count($result)) {
			return;
		}
		$row = $result->current();		
		$parentRow = $row->findParentRow('Admin_Model_DbTable_Album');
		//Zend_Debug::dump($parentRow);exit;
		if($parentRow){
			$parentRow = $parentRow->toArray();			
		}
		$album = new Admin_Model_Album($parentRow);
			
		$trailer->setTrailerId($row->trailer_id)
		->setTrailerTitle($row->trailer_title)		
		->setVideo($row->video)
		->setAlbumId($row->album_id)		
		->setPublished($row->published)
		->setAlbum($album);
	}
	
	public function fetchRow($video)
	{
		$db = $this->getDbTable();
		$select = $db->select()->where('trailer_title = ?', $video);
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
			$entry = new Admin_Model_Trailer();
			
			$entry->setTrailerId($row->trailer_id)
			->setTrailerTitle($row->trailer_title)			
			->setVideo($row->video)
			->setAlbumId($row->album_id)
			->setPublished($row->published);
			$entries[] = $entry;
		}
		return $entries;
	}
	
	public function fetchAllList($pagingOptions, $search = null, $filters = null)
	{
		$db = $this->getDbTable()->getAdapter();
        $select = $db->select();
        $select->from(array('t' => $this->_tableName));
        $select->join(array('a' => 'album'), 'a.album_id = t.album_id', array('album_id', 'album_name', 'image'));
        if($search){          	      	
        	$select->where('trailer_title LIKE ?', "$search%");
        }
		if(is_array($filters)){
        	foreach($filters as $filter => $value){
        		$select->where($filter, $value);
        	}
        }
        $select->where('published = ?', 1);
		
        //$select->order('video');
		$adapter = new Admin_Model_Paginator_TrailerAdapter($select);		
		$paginator = new Zend_Paginator($adapter);
		//Zend_Debug::dump($select->__toString(), $label = 'Select:', $echo = true);exit;
		
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
			$fields = array('trailer_id', 'trailer_title', 'video', 'published');
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
		
		//Zend_Debug::dump($select->__toString(), $label = 'Options:', $echo = true);exit;
		return $table->fetchAll($select);
        
	}
	
	public function fetchColView($fields = null, $filters = null, $order = null, $pagingOptions = null)
	{
		$db = $this->getDbTable()->getAdapter();
        $select = $db->select();
		if(is_null($fields)){
			$fields = array('trailer_id', 'trailer_title', 'video', 'published');
		}
		$select->from(array('t' => $this->_tableName), $fields);
		$select->join(array('a' => 'album'), 'a.album_id = t.album_id', array('album_id', 'album_name'));
		if(is_array ($filters)){				
			foreach ($filters as $field => $filter){	
				if(is_array($filter))			
				$select->where($filter[0], $filter[1]);
			}
		}
		if(is_array ($order)){				
			$select->order($order);			
		}
		
		//exit;
		$adapter = new Admin_Model_Paginator_TrailerAdapter($select);
		$paginator = new Zend_Paginator($adapter);
		//Zend_Debug::dump($select->__toString(), $label = 'Select:', $echo = true);		
		
		$paginator->setCurrentPageNumber($pagingOptions['CURRENT_PAGE_NUMBER']);
		$paginator->setItemCountPerPage($pagingOptions['items_count_per_page']);
		$paginator->setPageRange($pagingOptions['page_range']);
		
		return $paginator;        
	}
	
	public function findOneBy($property, $value, Admin_Model_Trailer $trailer){
		$property = lcfirst($property);
		$filter  = new Zend_Filter_Word_CamelCaseToUnderscore();
		$property = $filter->filter($property);
		$table = $this->getDbTable();
		//$fields  = array('music_id', 'music_title', 'has_photo');
		$where = array($property . ' = ?' => "$value");

		$row = $table->fetchRow($where);
		//Zend_Debug::dump($where);	exit;	
		$parentRow = $row->findParentRow('Admin_Model_DbTable_Album');
		if($parentRow){
			$parentRow = $parentRow->toArray();			
		}
		$album = new Admin_Model_Album($parentRow);
		$trailer->setTrailerId($row->trailer_id)
			->setTrailerTitle($row->trailer_title)			
			->setVideo($row->video)
			->setAlbumId($row->album_id)
			->setPublished($row->published)
			->setAlbum($album);
		
	}	
	
	public function adminAutoComplete($match){
		$fields  = array('id' => 'trailer_id', 'value' => 'trailer_title');
		$where = empty($match) ? null: array('video LIKE ?' => "$match%");
		$order  = array('video');
		return $this->fetchCol($fields, $where, $order);
	}
	
	public function siteAutoComplete($match){
		$fields  = array('id' => 'trailer_id', 'trailer_title', 'value' => 'video');
		$where = empty($match) ? null: array('video LIKE ?' => "$match%");
		$where['published = ?'] = 0;
		return $this->fetchCol($fields, $where, $order);
	}
	
	public function adminView($pagingOptions, $filters = null, $order){
		$mapper = new Admin_Model_TrailerMapper();	
		$fields = array('trailer_id', 'trailer_title', 'video', 'published');
		return $mapper->fetchColView($fields, $filters, $order, $pagingOptions);
	}
	
}

