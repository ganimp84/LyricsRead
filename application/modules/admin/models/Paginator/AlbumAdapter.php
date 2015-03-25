<?php

class Admin_Model_Paginator_AlbumAdapter extends Zend_Paginator_Adapter_DbSelect
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
		$albums = array();
		foreach ($rows as $row) {
			$album = new Admin_Model_Album($row);			
			$language = new Admin_Model_Language($row);			
			$album->setLanguage($language);			
			$albums[] = $album;
		}

		return $albums;
	}
}