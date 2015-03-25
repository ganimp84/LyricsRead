<?php

class Admin_Model_AlbumMapper
{
	protected $_dbTable;
	protected $_tableName = 'album';
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
			$this->setDbTable('Admin_Model_DbTable_Album');
		}
		return $this->_dbTable;
	}

	public function save($data)
	{
		$album = new Admin_Model_Album($data);
		//Zend_Debug::dump($album, $label = 'Album', $echo = true);
		//exit;
		if (null === ($id = $album->getAlbumId())) {
			$this->getDbTable()->insert($data);
		} else {
			$this->getDbTable()->update($data, array('album_id = ?' => $id));
		}
	}
	
	public function saveMany($data, $ids = null)
	{	
		$table = $this->getDbTable();
		$where = $table->getAdapter()->quoteInto('FIND_IN_SET(album_id,(?))', $ids);
		$table->update($data, $where);
	}
	
	public function insertMany($data)
	{	$table = $this->getDbTable();
		$stmt = $table->getAdapter()->prepare('INSERT INTO '.$this->_tableName.' (album_name, language_id) VALUES (?, ?)');
		if(is_array($data)){
			foreach($data as $row){
				$stmt->execute( array($row['album_name'], $row['language_id']) ); 
			}	
		}		
	}

	public function find($id, Admin_Model_Album $album)
	{
		$result = $this->getDbTable()->find($id);
		if (0 == count($result)) {
			return;
		}
		$row = $result->current();
		$parentRow = $row->findParentRow('Admin_Model_DbTable_Language');
		if($parentRow){
			$parentRow = $parentRow->toArray();			
		}
		$language = new Admin_Model_Language($parentRow);
			
		$album->setAlbumId($row->album_id)		
		->setAlbumName($row->album_name)
		->setLanguageId($row->language_id)		
		->setLocked($row->locked)
		->setFeatured($row->featured)
		->setHasPhoto($row->has_photo)
		->setImage($row->image)
		->setCreatedOn($row->created_on)
		->setLanguage($language);
	}
	
	public function fetchRow($album_name)
	{
		$db = $this->getDbTable();
		$select = $db->select()->where('album_name = ?', $album_name);
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
			$entry = new Admin_Model_Album();
			
			$entry->setAlbumId($row->album_id)			
			->setAlbumName($row->album_name)
			->setLanguageId($row->language_id)
			->setLocked($row->locked)
			->setFeatured($row->featured)
			->setHasPhoto($row->has_photo)
			->setImage($row->image)	
			->setCreatedOn($row->created_on);
			$entries[] = $entry;
		}
		return $entries;
	}
	
	public function fetchAllList($pagingOptions, $search = null, $filters = null)
	{
		$db = $this->getDbTable()->getAdapter();
        $select = $db->select();
        $select->from(array('a' => $this->_tableName));
        if($search){          	      	
        	$select->where('album_name LIKE ?', "$search%");
        }
        if(is_array($filters)){
        	foreach($filters as $filter => $value){
        		$select->where($filter, $value);
        	}
        }
        
        $select->where('locked = ?', 0);
        $select->order('album_name');
		$adapter = new Admin_Model_Paginator_AlbumAdapter($select);		
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
		$select->setIntegrityCheck(false);
		if(is_null($fields)){
			$fields = array('album_id', 'album_name', 'locked', 'featured', 'image');
		}
		$select->from(array('a' => $this->_tableName), $fields);
		$select->join(array('l' => 'language'), 'a.language_id = l.language_id', array('language_id', 'language_name', 'language_code'));
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
			$fields = array('album_id', 'album_name', 'locked', 'featured', 'has_photo');
		}
		$select->from(array('a' => $this->_tableName), $fields);
		$select->join(array('l' => 'language'), 'a.language_id = l.language_id', array('language_id', 'language_name', 'language_code'));
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
		$adapter = new Admin_Model_Paginator_AlbumAdapter($select);
		$paginator = new Zend_Paginator($adapter);
		//Zend_Debug::dump($select->__toString(), $label = 'Select:', $echo = true);		
		
		$paginator->setCurrentPageNumber($pagingOptions['CURRENT_PAGE_NUMBER']);
		$paginator->setItemCountPerPage($pagingOptions['items_count_per_page']);
		$paginator->setPageRange($pagingOptions['page_range']);
		
		return $paginator;        
	}
	
	public function findOneBy($property, $value, Admin_Model_Album $album){
		$property = lcfirst($property);
		$filter  = new Zend_Filter_Word_CamelCaseToUnderscore();
		$property = $filter->filter($property);
		$table = $this->getDbTable();
		//$fields  = array('music_id', 'music_title', 'has_photo');
		$where = array($property . ' = ?' => "$value");

		$row = $table->fetchRow($where);
		
		$album->setAlbumId($row->album_id)		
		->setAlbumName($row->album_name)
		->setLanguageId($row->language_id)		
		->setLocked($row->locked)
		->setFeatured($row->featured)
		->setHasPhoto($row->has_photo)
		->setImage($row->image)
		->setCreatedOn($row->created_on);
	}	
	
	public function adminAutoComplete($match){
		$fields  = array('id' => 'album_id', 'value' => 'album_name', 'has_photo' => 'has_photo');
		$where = empty($match) ? null: array('album_name LIKE ?' => "$match%");
		$order  = array('album_name');
		return $this->fetchCol($fields, $where, $order);
	}
	
	public function siteAutoComplete($match){
		$fields  = array('id' => 'album_id', 'value' => 'album_name', 'has_photo' => 'has_photo');
		$where = empty($match) ? null: array('album_name LIKE ?' => "$match%");
		$where['locked = ?'] = 0;
		$lang_id = LANGUAGE_ID;
		if($lang_id){
			$where['language_id = ?'] = $lang_id;
		}
		$order  = array('album_name');
		return $this->fetchCol($fields, $where, $order);
	}
	
	public function adminView($pagingOptions, $filters = null, $order){
		$mapper = new Admin_Model_AlbumMapper();	
		$fields = array('album_id', 'album_name', 'locked', 'featured', 'has_photo');
		if(!$order){
			$order = array('album_name asc');		
		}			
		return $mapper->fetchColView($fields, $filters, $order, $pagingOptions);
	}
	
}

