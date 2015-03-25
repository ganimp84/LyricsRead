<?php

require_once APPLICATION_PATH.'/modules/site/controllers/AppSiteController.php';

class Site_TrailerController extends Site_AppSiteController
{
	
    public function init()
    {
		parent::init();
    }
    
    public function indexAction()
    {
    	$this->_page_id = "trailer_list";
    	$this->activateMenu();
        $search = $this->getRequest()->getParam('ls', '');
        $page = $this->getRequest()->getParam('page', 1);
        
        $imagesPath = $this->_siteConfig['LIST_IMAGES_PATH']['TRAILER'];
        $imageSize = $this->_siteConfig['LIST_IMAGES_SIZE']['TRAILER'];
        
        $this->view->imageSrc = $imagesPath.'/_{IMAGE_ID}_'.$imageSize.'.jpg';        
        $this->view->trailerStore = constant('TRAILER_STORE_AUTOCOMPLETE');
        
        $cache = $this->getCacheInstance('trailer');
		$cacheId = $this->getRequest()->getPathInfo();
		$cacheId = preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheId);
		if(!($data = $cache->load($cacheId)) || 1==2)
		{			
	        $mapper = new Admin_Model_TrailerMapper();
	        $pagingOptions = $this->_siteConfig['PAGINATION'];
	    	$pagingOptions['CURRENT_PAGE_NUMBER'] = $page;
	    	$this->applyLanguageFilter();		        
	        $data['paginator'] = $mapper->fetchAllList($pagingOptions, $search, $this->_filters);
	    	$cache->save($data);
		}
        
        $this->view->paginator = $data['paginator'];
        $this->view->search = $search;
        $this->view->page = $page;
        $this->view->browse_numbers = true;
    }


}

