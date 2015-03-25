<?php

class Admin_Model_DbTable_Music extends Zend_Db_Table_Abstract
{
	protected $_name = 'music';
    protected $_primary = 'music_id';
    
    protected $_dependentTables = array('music_composer', 'music_singer');
    
    protected $_referenceMap    = array(
        'Album' => array(
            'columns'           => array('album_id'),
            'refTableClass'     => 'Admin_Model_DbTable_Album',
            'refColumns'        => array('album_id')
        ),
    );
}
