<?php

class Admin_Model_Paginator_TrailerAdapter extends Zend_Paginator_Adapter_DbSelect
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
		$trailers = array();
		foreach ($rows as $row) {
			$trailer = new Admin_Model_Trailer($row);						
			$album = new Admin_Model_Album($row);					
			$trailer->setAlbum($album);			
			$trailers[] = $trailer;
		}

		return $trailers;
	}
}