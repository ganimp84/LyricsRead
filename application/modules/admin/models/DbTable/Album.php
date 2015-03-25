<?php

class Admin_Model_DbTable_Album extends Zend_Db_Table_Abstract
{

	protected $_name = 'album';
    protected $_primary = 'album_id';
    
    protected $_dependentTables = array('music');
    
    protected $_referenceMap    = array(
        'Language' => array(
            'columns'           => array('language_id'),
            'refTableClass'     => 'Admin_Model_DbTable_Language',
            'refColumns'        => array('language_id')
        ),
    );
    
    
}

