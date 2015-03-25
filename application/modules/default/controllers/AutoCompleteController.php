<?php

class AutoCompleteController extends Zend_Controller_Action
{
    
    public function __call($method, array $arguments = array()){
    	if (@preg_match('/(.+)(StoreAction)$/', $method, $match)) {
    		$this->autocomplete($match[1]);
        }
    }

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
    
    public function autocomplete($store)
    {    	
    	$items = array();
		if ($this->getRequest()->isGet()) {
			$match = trim($this->getRequest()->getParam('term', ''));
			if(strlen($match) >= 1){
				$type = trim($this->getRequest()->getParam('type', ''));
				if($type == 'admin'){
					$items = $this->adminAutoComplete($store, $match);
				} else {
					$items = $this->siteAutoComplete($store, $match);
				}
			}
		}
		echo $json = Zend_Json::encode($items);
		$this->_helper->layout()->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();		
    }	
			
    
    public function adminAutoComplete($store, $match)
    {    	 
    	$mapperName = 'Admin_Model_'.ucfirst($store).'Mapper';
    	$mapper = new $mapperName();
    	return $mapper->adminAutoComplete($match)->toArray();
    }
    
 	public function siteAutoComplete($store, $match)
    {       	
    	$type = trim($this->getRequest()->getParam('type', ''));
    	$id = trim($this->getRequest()->getParam('id', ''));
    	$mapperName = 'Admin_Model_'.ucfirst($store).'Mapper';
    	$mapper = new $mapperName();
    	return $mapper->siteAutoComplete($match)->toArray();
    }
 
}

