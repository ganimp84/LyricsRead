<?php

class Admin_Model_DbTable_Trailer extends Zend_Db_Table_Abstract
{

	protected $_name = 'trailer';
    protected $_primary = 'trailer_id';
    
    protected $_referenceMap    = array(
        'Album' => array(
            'columns'           => array('album_id'),
            'refTableClass'     => 'Admin_Model_DbTable_Album',
            'refColumns'        => array('album_id')
        ),
    );
    
    
}

