<?php

class Admin_Model_DbTable_MusicLyricist extends Zend_Db_Table_Abstract
{
	protected $_name = 'music_lyricist';
	protected $_primary = array('music_id', 'lyricist_id');
	
	 protected $_referenceMap    = array(
        'Music' => array(
            'columns'           => array('music_id'),
            'refTableClass'     => 'Admin_Model_DbTable_Music',
            'refColumns'        => array('music_id')
        ),
    );
}
