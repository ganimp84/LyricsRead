<?php

require_once APPLICATION_PATH.'/modules/site/controllers/AppSiteController.php';

class Site_AlbumController extends Site_AppSiteController
{
	
    public function init()
    {
		parent::init();
    }
    
    public function indexAction()
    {
    	$this->_page_id = "album_list";
    	$this->activateMenu();
        $search = $this->getRequest()->getParam('ls', '');
        $page = $this->getRequest()->getParam('page', 1);
        
        $imagesPath = $this->_siteConfig['LIST_IMAGES_PATH']['ALBUM'];
        $imageSize = $this->_siteConfig['LIST_IMAGES_SIZE']['ALBUM'];
        
        $this->view->imageSrc = $imagesPath.'/_{IMAGE_ID}_'.$imageSize.'.jpg';        
        $this->view->albumStore = constant('ALBUM_STORE_AUTOCOMPLETE');
        
        $cache = $this->getCacheInstance('album');
		$cacheId = $this->getRequest()->getPathInfo();
		$cacheId = preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheId);
		if(!($data = $cache->load($cacheId)))
		{
	        $mapper = new Admin_Model_AlbumMapper();
	        $pagingOptions = $this->_siteConfig['PAGINATION'];
	    	$pagingOptions['CURRENT_PAGE_NUMBER'] = $page;
	    	$this->applyLanguageFilter();	        
	        $data['paginator'] = $mapper->fetchAllList($pagingOptions, $search, $this->_filters);
	    	$cache->save($data);
		}
        
        $this->view->paginator = $data['paginator'];
        $this->view->browse_numbers = true;
        $this->view->search = $search;
        $this->view->page = $page;
    }
    
    public function featuredAction()
    {
   	 	$cache = $this->getCacheInstance('featured');
		$cacheId = 'show_featured';
		if(!($data = $cache->load($cacheId)))
		{
	        $mapper = new Admin_Model_AlbumMapper();
	        $where['featured = ?'] = 1;
	        $where['locked = ?'] = 0; 
	        $order[]= 'modified_on desc';
	        $order[]= 'album_id desc'; 
	        $data['featured'] = $mapper->fetchCol(null, $where, $order);
	    	$cache->save($data);
		}
		
		$this->view->featured = $data['featured'];
		//Zend_Debug::dump($data); exit;
    }


}

