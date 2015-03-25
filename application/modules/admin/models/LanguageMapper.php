<?php

class Admin_Model_LanguageMapper
{
	protected $_dbTable;

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
			$this->setDbTable('Admin_Model_DbTable_Language');
		}
		return $this->_dbTable;
	}
	
	public function find($id, Admin_Model_Language $language)
	{
		$result = $this->getDbTable()->find($id);
		if (0 == count($result)) {
			return;
		}
		$row = $result->current();
		//Zend_Debug::dump($language, $label = 'Language', $echo = true);		
		$language->setLanguageId($row->language_id)
		->setLanguageName($row->language_name)		
		->setLanguageCode($row->language_code);
	}

	public function fetchCol($fields = null, $where = null, $order = null, $limit = null)
	{
		$table = $this->getDbTable(); 		
		$select = $table->select();
		if(is_null($fields)){
			$fields = array('language_id', 'language_name', 'language_code');
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
		//Zend_Debug::dump($fields, $label = 'Table', $echo = true);exit;
		return $table->fetchAll($select);
        
	}
	
	public function adminAutoComplete($match){
		$mapper  = new Admin_Model_LanguageMapper();		
		$fields  = array('id' => 'language_id', 'value' => 'language_name');
		$where = empty($match) ? null: array('language_name LIKE ?' => "$match%");		
		$order  = array('language_name');
		return $mapper->fetchCol($fields, $where, $order);
	}
	
	public function adminView(){
		$mapper = new Admin_Model_LanguageMapper();		 	
		$order  = array('language_name'); 	
		$limit  = array('count' => 0, 'offset' => 0);
		$results = $mapper->fetchCol(null, null, $order, $limit);
		return $results->toArray();
	}
	

}

