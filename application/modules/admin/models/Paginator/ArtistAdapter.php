<?php

class Admin_Model_Paginator_ArtistAdapter extends Zend_Paginator_Adapter_DbSelect
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
		$artists = array();
		foreach ($rows as $row) {
			$artist = new Admin_Model_Artist($row);	
			$artists[] = $artist;
		}

		return $artists;
	}
}