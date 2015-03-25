<?php

require_once APPLICATION_PATH.'/modules/site/controllers/AppSiteController.php';

class Site_SingerController extends Site_AppSiteController
{
	public function init()
    {
		parent::init();
    }

    public function indexAction()
    {
    	$this->_page_id = "singer_list";
    	$this->activateMenu();
        $search = $this->getRequest()->getParam('ls', '');
        $page = $this->getRequest()->getParam('page', 1);
        
        $imagesPath = $this->_siteConfig['LIST_IMAGES_PATH']['SINGER'];
        $imageSize = $this->_siteConfig['LIST_IMAGES_SIZE']['SINGER'];
        
        $this->view->imageSrc = $imagesPath.'/_{IMAGE_ID}_'.$imageSize.'.jpg';        
        $this->view->singerStore = constant('SINGER_STORE_AUTOCOMPLETE');
        
        $cache = $this->getCacheInstance('singer');
		$cacheId = $this->getRequest()->getPathInfo();
		$cacheId = preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheId);
		if(!($data = $cache->load($cacheId)))
		{
	        $mapper = new Admin_Model_SingerMapper();
	        $pagingOptions = $this->_siteConfig['PAGINATION'];
	    	$pagingOptions['CURRENT_PAGE_NUMBER'] = $page;
			$data['paginator'] = $mapper->fetchAllList($pagingOptions, $search);
	    	$cache->save($data);
		}
        
        $this->view->paginator = $data['paginator'];        
        $this->view->page = $page;
        $this->view->search = $search;
    }


}

