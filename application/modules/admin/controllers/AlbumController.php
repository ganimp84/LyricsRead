<?php

require_once APPLICATION_PATH.'/modules/admin/controllers/AppAdminController.php';

class Admin_AlbumController extends Admin_AppAdminController
{

    protected $_form = null;    

    public function init()
    {
    	$this->setControllerId('album');
    	$this->searchTitle = 'Album';
    	parent::init();
    }

    public function indexAction()
    {	

    }

    public function addAction()
    {
		$this->view->languageStore = constant('LANGUAGE_STORE_AUTOCOMPLETE');	
    }
    
    public function viewAction()
    {	
    	parent::viewAction();
    	$adminConfig = Zend_Registry::get('admin_config');
    	
    	$request = $this->getRequest();
    	$sort_by = $request->getParam('sort', 'album_name');
    	$sort_type = $request->getParam('type', 'asc');
    	$filters = null;    	
   		$order = $this->getSubmittedOrderby();
    	if($request->isPost()){
    		$filters = $this->getSubmittedFilters();	
    	}    	
    	//Zend_Debug::dump($filters, $label = 'Options:', $echo = true);
    	$pagingOptions = $adminConfig['PAGINATION'];
    	$pagingOptions['CURRENT_PAGE_NUMBER'] = $request->getParam('page', 1);
    	$this->view->paginator = Admin_Model_AlbumMapper::adminView($pagingOptions, $filters, $order);
        $this->view->albumConfig = $adminConfig['ALBUM'];
        $this->view->albumGalleryPath = constant('ALBUM_GALEERY_PATH');
        $this->view->albumGalleryPublicPath = constant('ALBUM_GALEERY_PUBLIC_PATH');
        $this->view->searchOptions = $this->getSearchOptions();
        $this->view->orderbyOptions = $this->getOrderbyOptions();
        $this->view->request = $request;
        $this->view->sort_by = $sort_by;
        $this->view->sort_type = ($sort_type == 'asc') ? 'desc' : 'asc';
    }    

    public function saveAction()
    {
	
		$request = $this->getRequest();
		if ($request->isPost()) {
			
			$data = array(
	            'album_name'   => $request->getParam('album_name'),
				'language_id'   => $request->getParam('language_id'),
				'image'   => $request->getParam('image', ''),            
	            'created_on' => date('Y-m-d H:i:s'),
			);	
			if($id = $request->getParam('album_id', null)){
				$data['album_id'] = $id;
			}			
			$mapper  = new Admin_Model_AlbumMapper();
			$mapper->save($data);
			
			$overlay = $request->getParam('overlay', null);
			if($overlay != '1')
			$this->_redirect(ADMIN_ACCESS_PATH . '/album/view');

		}

		$this->_helper->layout()->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
			
    }
    
    public function editAction()
    {
    	$request = $this->getRequest();
    	if ($request->isGet()) {    		 
    		$album_id = $request->getParam('album_id');    		
    		$album    = new Admin_Model_Album();
			$mapper   = new Admin_Model_AlbumMapper();
			$mapper->find($album_id, $album);
			$this->view->album = $album; 
			$this->view->languageStore = constant('LANGUAGE_STORE_AUTOCOMPLETE');
			//Zend_Debug::dump($album,  $label = 'Album:', $echo = true);exit;
    	}
    }
    
    public function getSearchOptions(){
    
    	if($this->searchOptions === null){
	    	$this->searchOptions = array(
	    			'album_name' => array( 	'type' => 'text',
	    									'label' => 'Album'
	    								),
	    			'language_id' => array( 'type' => 'autocomplete',
	    									'label' => 'Language',
	    									'label_field' => 'language_name',
	    									'store' => constant('LANGUAGE_STORE_AUTOCOMPLETE'),
	    									'alias' => 'a'
	    								),					
	    			'has_photo' => array( 	'type' => 'radio',
	    									'label' => 'With photo',
	    									'radio_options' => array('-1' => 'Doesn\'t matter',
	    															'1' => 'Yes',
	    															'0' => 'No')
	    								),
	    			'locked'    => array( 	'type' => 'radio',
	    									'label' => 'Locked',
	    									'radio_options' => array('-1' => 'Doesn\'t matter',
	    															'1' => 'Yes',
	    															'0' => 'No')
	    								),
	    			'featured' => array( 	'type' => 'radio',
	    									'label' => 'Featured',
	    									'radio_options' => array('-1' => 'Doesn\'t matter',
	    															'1' => 'Yes',
	    															'0' => 'No')
	    								)															
	    		);
    	}
    	return $this->searchOptions;	
    }
    
    public function getOrderbyOptions(){
    	if($this->orderbyOptions === null) {
    		$this->orderbyOptions = array(	
    					'first_orderby' => 	array(	'label' => '1. Order by ',
    												'options' => array(	'' => '--Field--',
    																	'a.album_name' => 'Album', 
    																	'l.language_name' => 'Language')
    										),
    					'second_orderby' => array(	'label' => '2. Order by ',
    												'options' => array(	'' => '--Field--',
    																	'a.album_name' => 'Album', 
    																	'l.language_name' => 'Language')
    										)					
    				);
    	} 
    	return $this->orderbyOptions;
    }
    
    public function getUpdateOneFields(){
    	if($this->updateOneFields === null){
    		$this->updateOneFields = array(
    						'photo' => array('album_id', 'has_photo'),
    						'lock' => array('album_id', 'locked'),
    						'complete' => array('album_id', 'featured')
    				);
    	}
    	return $this->updateOneFields;
    }  
    
}
