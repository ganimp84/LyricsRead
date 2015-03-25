<?php

class Admin_Model_Paginator_MusicAdapter extends Zend_Paginator_Adapter_DbSelect
{

	/**
	* Returns an array of items for a page.
	*
	* @param  integer $offset Page offset
	* @param  integer $itemCountPerPage Number of items per page
	* @return array
	*/

	public function getItems($offset, $itemCountPerPage)
	{
		$rows = parent::getItems($offset, $itemCountPerPage);
		
		$musics = array();
		foreach ($rows as $row) {
			$music = new Admin_Model_Music($row);	
			$album = new Admin_Model_Album($row);			
			$music->setAlbum($album);
			$musics[] = $music;
		}

		return $musics;
	}
}