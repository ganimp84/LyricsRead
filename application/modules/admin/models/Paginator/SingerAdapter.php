<?php

class Admin_Model_Paginator_SingerAdapter extends Zend_Paginator_Adapter_DbSelect
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
		$singers = array();
		foreach ($rows as $row) {
			$singer = new Admin_Model_Singer($row);	
			$singers[] = $singer;
		}

		return $singers;
	}
}