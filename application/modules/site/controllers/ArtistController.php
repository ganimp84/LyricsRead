<?php

require_once APPLICATION_PATH.'/modules/site/controllers/AppSiteController.php';

class Site_ArtistController extends Site_AppSiteController
{
	private $siteConfig = null;
	
    public function init()
    {
		parent::init();    	    	
    }

    public function indexAction()
    {
    	$this->_page_id = "artist_list";
    	$this->activateMenu();
        $search = $this->getRequest()->getParam('ls', '');
        $page = $this->getRequest()->getParam('page', 1);
        
        $imagesPath = $this->_siteConfig['LIST_IMAGES_PATH']['ARTIST'];
        $imageSize = $this->_siteConfig['LIST_IMAGES_SIZE']['ARTIST'];
        
        $this->view->imageSrc = $imagesPath.'/_{IMAGE_ID}_'.$imageSize.'.jpg';        
        $this->view->artistStore = constant('ARTIST_STORE_AUTOCOMPLETE');
        
        $cache = $this->getCacheInstance('artist');
		$cacheId = $this->getRequest()->getPathInfo();
		$cacheId = preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheId);
		if(!($data = $cache->load($cacheId)))
		{        
	        $mapper = new Admin_Model_ArtistMapper();
	        $pagingOptions = $this->_siteConfig['PAGINATION'];
	    	$pagingOptions['CURRENT_PAGE_NUMBER'] = $page;
        	$data['paginator'] = $mapper->fetchAllList($pagingOptions, $search);
	    	$cache->save($data);
		}
        
        $this->view->paginator = $data['paginator'];
        $this->view->search = $search;
        $this->view->page = $page;
    }
	

}

