<?php

class Admin_Model_MusicMapper
{
	protected $_dbTable;
	protected $_tableName = 'music';

	function __call($method, $arguments){
		if (@preg_match('/(find(?:One|All)?By)(.+)/', $method, $match)) {
			return $this->{$match[1]}($match[2], $arguments[0], $arguments[1], $arguments[2], $arguments[3]);
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
			$this->setDbTable('Admin_Model_DbTable_Music');
		}
		return $this->_dbTable;
	}

	public function save($data)
	{
		$music = new Admin_Model_Music($data);

		if (null === ($id = $music->getMusicId())) {
			$id = $this->getDbTable()->insert($data);
		} else {
			$this->getDbTable()->update($data, array('music_id = ?' => $id));
		}

		return $id;
	}

	public function saveMany($data, $ids = null)
	{	
		$table = $this->getDbTable();
		$where = $table->getAdapter()->quoteInto('FIND_IN_SET(music_id,(?))', $ids);
		$table->update($data, $where);
	}
	
	public function updateMany($records)
	{	
		if(is_array($records)){
			foreach($records as $music_id => $data){
				$this->getDbTable()->update($data, array('music_id = ?' => $music_id));
			}	
		}		
	}

	public function find($id, Admin_Model_Music $music)
	{
		$result = $this->getDbTable()->find($id);
		if (0 == count($result)) {
			return;
		}
		$row = $result->current();
		$this->buildMusic($row, $music);
	}

	public function fetchAll($returnType = 'object')
	{
		$resultSet = $this->getDbTable()->fetchAll();
		if($returnType == 'array'){
			return $resultSet->toArray();
		}
		$entries   = array();
		foreach ($resultSet as $row) {
			$entry = new Admin_Model_Music();
			$this->buildMusic($row, $entry);
			$entries[] = $entry;
		}
		return $entries;
	}

	public function fetchAllList($pagingOptions, $search = null, $filters = null)
	{
		$db = $this->getDbTable()->getAdapter();
		$select = $db->select();
		$select->from(array('m' => $this->_tableName));
		$select->join(array('a' => 'album'), 'm.album_id = a.album_id', array('a.album_id', 'a.album_name'));
		if($search){
			$select->where('music_title LIKE ?', "$search%");
		}
		if(is_array($filters)){
        	foreach($filters as $filter => $value){
        		$select->where($filter, $value);
        	}
        }
		$select->where('lyrics != ?', '');
		$select->where('published = ?', 1);
		$select->order('music_title'); 
		$adapter = new Admin_Model_Paginator_MusicAdapter($select);
		$paginator = new Zend_Paginator($adapter);
		//Zend_Debug::dump($select->__toString(), $label = 'Select:', $echo = true);exit;

		$paginator->setCurrentPageNumber($pagingOptions['CURRENT_PAGE_NUMBER']);
		$paginator->setItemCountPerPage($pagingOptions['items_count_per_page']);
		$paginator->setPageRange($pagingOptions['page_range']);
		return $paginator;
	}

	public function fetchRecent($filters = null, $limit = '6')
	{	
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		
		$select->from(array('m' => $this->_tableName));
		$select->join(array('a' => 'album'), 'm.album_id = a.album_id', array('album_name'));
		if(is_array($filters)){
        	foreach($filters as $filter => $value){
        		$select->where($filter, $value);
        	}
        }
        $select->where('lyrics != ?', '');
		$select->where('published = ?', 1);
		$select->order('created_on desc'); 
		$select->limit($limit, 0);
		
		//Zend_Debug::dump($select->__toString());
		return $table->fetchAll($select)->toArray();
	}
	
	public function fetchCol($fields = null, $where = null, $order = null, $limit = null)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->setIntegrityCheck(false);
		if(is_null($fields)){
			$fields = array('music_id', 'music_title', 'has_photo');
		}
		$select->from(array('m' => $this->_tableName), $fields);
		$select->join(array('a' => 'album'), 'm.album_id = a.album_id', array('a.album_id', 'a.album_name'));
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
		//echo $select->__toString();exit;
		return $table->fetchAll($select);

	}

	public function fetchColView($fields = null, $filters = null, $order = null, $pagingOptions = null)
	{
		$db = $this->getDbTable()->getAdapter();
		$select = $db->select();
		if(is_null($fields)){
			$fields = array('music_id', 'music_title', 'published', 'has_photo', 'image', 'video');
		}
		$select->from(array('m' =>$this->_tableName), $fields);
		$select->join(array('a' => 'album'), 'm.album_id = a.album_id', array('a.album_id', 'a.album_name'));

		if(is_array ($filters)){
			foreach ($filters as $field => $filter){
				if(is_array($filter))
				$select->where($filter[0], $filter[1]);
			}
		}
		//$select->where('m.lyrics = ? ', '');
		//$select->where('m.published = ? ', 1);
		if(is_array ($order)){
			$select->order($order);
		}
		
		
		if($pagingOptions){
			//Zend_Debug::dump($select->__toString(), $label = 'Select:', $echo = true);exit;
			$adapter = new Admin_Model_Paginator_MusicAdapter($select);
			$paginator = new Zend_Paginator($adapter);
			$paginator->setCurrentPageNumber($pagingOptions['CURRENT_PAGE_NUMBER']);
			$paginator->setItemCountPerPage($pagingOptions['items_count_per_page']);
			$paginator->setPageRange($pagingOptions['page_range']);
	
			return $paginator;
		} else {
			return $db->fetchAll($select);
		}
	}

	public function findOneBy($property, $value, Admin_Model_Music $music){
		$property = lcfirst($property);
		$filter  = new Zend_Filter_Word_CamelCaseToUnderscore();
		$property = $filter->filter($property);
		$table = $this->getDbTable();
		//$fields  = array('music_id', 'music_title', 'has_photo');
		$where = array($property . ' = ?' => "$value");

		$row = $table->fetchRow($where);
		$this->buildMusic($row, $music);
	}
	
	public function findAlbumSongs($album_id, $music_id = null){
		$property = lcfirst($property);
		$filter  = new Zend_Filter_Word_CamelCaseToUnderscore();
		$property = $filter->filter($property);
		$table = $this->getDbTable();
		//$fields  = array('music_id', 'music_title', 'has_photo');
		$where['album_id = ?'] = "$album_id";
		if($music_id){
			$where['music_id != ?'] = "$music_id";
		}
		$resultSet = $table->fetchAll($where);
		
		$entries   = array();
		foreach ($resultSet as $row) {
			$entry = new Admin_Model_Music();
			$this->buildMusic($row, $entry);
			$entries[] = $entry;
		}
		//Zend_Debug::dump($where, $label = 'Select:', $echo = true);exit;
			
		return $entries;
	}

	public function findAllBy($property, $value, $pagingOptions = null, $search = null, $filters = null){

		$db = $this->getDbTable()->getAdapter();
		$select = $db->select();
		//$table = $this->getDbTable();
		//$select = $table->select();
		$select->from(array('m' => $this->_tableName));
		switch($property){
			case 'AlbumName': $property = 'a.album_name';
			$select->join(array('a' => 'album'), 'a.album_id = m.album_id', array('album_name'));
			break;
			case 'AlbumId': $property = 'a.album_id';
			$select->join(array('a' => 'album'), 'a.album_id = m.album_id', array('album_name'));
			break;
			case 'ComposerName': $property = 'c.composer_name';
			$select->join(array('mc' => 'music_composer'), 'm.music_id = mc.music_id')			
			->join(array('c' => 'composer'), 'mc.composer_id = c.composer_id', array())
						->join(array('al' => 'album'), 'al.album_id = m.album_id', array('album_name'));
			break;
			case 'SingerName': $property = 's.singer_name';
			$select->join(array('ms' => 'music_singer'), 'm.music_id = ms.music_id')
			->join(array('s' => 'singer'), 'ms.singer_id = s.singer_id', array())
			->join(array('al' => 'album'), 'al.album_id = m.album_id', array('album_name'));
			break;
			case 'LyricistName': $property = 'l.lyricist_name';
			$select->join(array('ml' => 'music_lyricist'), 'm.music_id = ml.music_id')
			->join(array('l' => 'lyricist'), 'ml.lyricist_id = l.lyricist_id', array())
						->join(array('al' => 'album'), 'al.album_id = m.album_id', array('album_name'));
			break;
			case 'ArtistName': $property = 'at.artist_name';
			$select->join(array('mat' => 'music_artist'), 'm.music_id = mat.music_id')
			->join(array('at' => 'artist'), 'mat.artist_id = at.artist_id', array())
						->join(array('al' => 'album'), 'al.album_id = m.album_id', array('album_name'));
			break;
		}
		$select->where($property . ' = ?', "$value");
		if($search){
			$select->where('music_title LIKE ?', "$search%");
		}
		if(is_array($filters)){
        	foreach($filters as $filter => $value){
        		$select->where($filter, $value);
        	}
        }
		$select->where('lyrics != ?', '');
		$select->where('published = ?', 1);
		$select->order('music_title'); 
		//Zend_Debug::dump($select->__toString(), $label = 'Options:', $echo = true);exit;
		if($pagingOptions){
			$adapter = new Admin_Model_Paginator_MusicAdapter($select);
			$paginator = new Zend_Paginator($adapter);
			$paginator->setCurrentPageNumber($pagingOptions['CURRENT_PAGE_NUMBER']);
			$paginator->setItemCountPerPage($pagingOptions['items_count_per_page']);
			$paginator->setPageRange($pagingOptions['page_range']);
			
			return $paginator;
		} else {
			return $db->fetchAll($select);
		}

	}

	public function buildMusic($row, Admin_Model_Music $music){

		//Zend_Debug::dump($row, $label = 'Options:', $echo = true);exit;
		if(!$row) return null;
		$parentRow = $row->findParentRow('Admin_Model_DbTable_Album');
		if($parentRow){
			$parentRow = $parentRow->toArray();
		}		
		$album = new Admin_Model_Album($parentRow);
		
		$composersRowSet = $row->findDependentRowset('Admin_Model_DbTable_MusicComposer')->toArray();
		$composers = array();
		if(is_array($composersRowSet)){
			$composerMapper = new Admin_Model_ComposerMapper();
			foreach($composersRowSet as $composersRow){
				$composer = new Admin_Model_Composer();
				$composerMapper->find($composersRow['composer_id'], $composer);
				$composers[] = $composer;
			}
		}

		$singersRowSet = $row->findDependentRowset('Admin_Model_DbTable_MusicSinger')->toArray();
		$singers = array();
		if(is_array($singersRowSet)){
			$singerMapper = new Admin_Model_SingerMapper();
			foreach($singersRowSet as $singersRow){
				$singer = new Admin_Model_Singer();
				$singerMapper->find($singersRow['singer_id'], $singer);
				$singers[] = $singer;
			}
		}

		$lyricistsRowSet = $row->findDependentRowset('Admin_Model_DbTable_MusicLyricist')->toArray();
		$lyricists = array();
		if(is_array($lyricistsRowSet)){
			$lyricistMapper = new Admin_Model_LyricistMapper();
			foreach($lyricistsRowSet as $lyricistsRow){
				$lyricist = new Admin_Model_Lyricist();
				$lyricistMapper->find($lyricistsRow['lyricist_id'], $lyricist);
				$lyricists[] = $lyricist;
			}
		}

		$artistsRowSet = $row->findDependentRowset('Admin_Model_DbTable_MusicArtist')->toArray();
		$artists = array();
		if(is_array($artistsRowSet)){
			$artistMapper = new Admin_Model_ArtistMapper();
			foreach($artistsRowSet as $artistsRow){
				$artist = new Admin_Model_Artist();
				$artistMapper->find($artistsRow['artist_id'], $artist);
				$artists[] = $artist;
			}
		}

		$music->setMusicId($row->music_id)
		->setMusicTitle($row->music_title)
		->setLyrics($row->lyrics)
		->setPublished($row->published)
		->setHasPhoto($row->has_photo)		
		->setImage($row->image)
		->setVideo($row->video)
		->setAlbum($album)
		->setComposers($composers)
		->setSingers($singers)
		->setLyricists($lyricists)
		->setArtists($artists)
		->setCreatedOn($row->created_on);
	}

	public function adminAutoComplete($match){
		$fields  = array('id' => 'music_id', 'value' => 'music_title', 'has_photo' => 'has_photo');
		$where = empty($match) ? null: array('music_title LIKE ?' => "$match%");
		$order  = array('music_title');
		return $this->fetchCol($fields, $where, $order);
	}
	
	public function siteAutoComplete($match){
		$fields  = array('id' => 'music_id', 'value' => 'music_title', 'has_photo' => 'has_photo');
		$where = empty($match) ? null: array('music_title LIKE ?' => "$match%");
		$where['lyrics != ?'] = '';
		$where['published = ?'] = 1;
		$lang_id = LANGUAGE_ID;
		if($lang_id){
			$where['language_id = ?'] = $lang_id;
		}
		$order  = array('music_title');
		return $this->fetchCol($fields, $where, $order);
	}	

	public function adminView($pagingOptions, $filters = null, $order){
		$mapper = new Admin_Model_MusicMapper();
		$fields = array('music_id', 'music_title', 'published', 'has_photo');
		if(!$order){
			$order = array('music_title asc');
		}
		return $mapper->fetchColView($fields, $filters, $order, $pagingOptions);
	}

}

