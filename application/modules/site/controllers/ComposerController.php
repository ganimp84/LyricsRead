<?php

require_once APPLICATION_PATH.'/modules/site/controllers/AppSiteController.php';

class Site_ComposerController extends Site_AppSiteController
{
	
    public function init()
    {
		parent::init();
    }

    public function indexAction()
    {
    	$this->_page_id = "composer_list";
    	$this->activateMenu();
        $search = $this->getRequest()->getParam('ls', '');
        $page = $this->getRequest()->getParam('page', 1);
        
        $imagesPath = $this->_siteConfig['LIST_IMAGES_PATH']['COMPOSER'];
        $imageSize = $this->_siteConfig['LIST_IMAGES_SIZE']['COMPOSER'];
        
        $this->view->imageSrc = $imagesPath.'/_{IMAGE_ID}_'.$imageSize.'.jpg';        
        $this->view->composerStore = constant('COMPOSER_STORE_AUTOCOMPLETE');
        
        $cache = $this->getCacheInstance('composer');
		$cacheId = $this->getRequest()->getPathInfo();
		$cacheId = preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheId);
		if(!($data = $cache->load($cacheId)))
		{ 
	        $mapper = new Admin_Model_ComposerMapper();
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

