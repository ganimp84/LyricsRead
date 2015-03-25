<?php

class Admin_Model_DbTable_MusicSinger extends Zend_Db_Table_Abstract
{
	protected $_name = 'music_singer';
	protected $_primary = array('music_id', 'singer_id');
	
	 protected $_referenceMap    = array(
        'Music' => array(
            'columns'           => array('music_id'),
            'refTableClass'     => 'Admin_Model_DbTable_Music',
            'refColumns'        => array('music_id')
        ),
    );
}
