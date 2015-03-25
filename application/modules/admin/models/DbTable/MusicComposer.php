<?php

class Admin_Model_DbTable_MusicComposer extends Zend_Db_Table_Abstract
{
	protected $_name = 'music_composer';
	protected $_primary = array('music_id', 'composer_id');
	
	 protected $_referenceMap    = array(
        'Music' => array(
            'columns'           => array('music_id'),
            'refTableClass'     => 'Admin_Model_DbTable_Music',
            'refColumns'        => array('music_id'),
	 		'onUpdate'          => self::RESTRICT
        ),
    );
}
