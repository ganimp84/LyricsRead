<?php

require_once APPLICATION_PATH.'/modules/site/controllers/AppSiteController.php';

class Site_MusicController extends Site_AppSiteController
{
 	
	public function init()
    {
		parent::init();
    }	

    public function indexAction()
    {
    	$this->_page_id = "song_list";
    	$this->activateMenu();
        $search = $this->getRequest()->getParam('ls', '');
        $page = $this->getRequest()->getParam('page', 1);
        
        $imagesPath = $this->_siteConfig['LIST_IMAGES_PATH']['MUSIC'];
        $imageSize = $this->_siteConfig['LIST_IMAGES_SIZE']['MUSIC'];
        
        $this->view->imageSrc = $imagesPath.'/_{IMAGE_ID}_'.$imageSize.'.jpg';        
        $this->view->musicStore = constant('MUSIC_STORE_AUTOCOMPLETE');
        
        $cache = $this->getCacheInstance('song');
		$cacheId = $this->getRequest()->getPathInfo();
		$cacheId = preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheId);
		if(!($data = $cache->load($cacheId)))
		{
	        $mapper = new Admin_Model_MusicMapper();
	        $pagingOptions = $this->_siteConfig['PAGINATION'];
	    	$pagingOptions['CURRENT_PAGE_NUMBER'] = $page;
	    	$this->applyLanguageFilter();	
	    	$data['paginator'] = $mapper->fetchAllList($pagingOptions, $search, $this->_filters);
	    	$cache->save($data);
		}
        
        $this->view->paginator = $data['paginator'];
        $this->view->search = $search;        
        $this->view->page = $page;
    }
    
    public function recentAction()
    {
    	$this->_page_id = "recently_added";
    	$this->activateMenu();
        
        $cache = $this->getCacheInstance('recent');
		$cacheId = $this->getRequest()->getPathInfo();
		$cacheId = preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheId);
		if(!($data = $cache->load($cacheId)))
		{
	        $mapper = new Admin_Model_MusicMapper();
	    	$this->applyLanguageFilter();	
	    	$data['recent'] = $mapper->fetchRecent($this->_filters, 50);
	    	$cache->save($data);
		}
        
        $this->view->recent = $data['recent'];
        $this->view->pageId = $this->_page_id;
        //Zend_Debug::dump($this->view->recent);
    }
    
	


}

