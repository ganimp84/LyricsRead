<?php

require_once APPLICATION_PATH.'/modules/admin/controllers/AppAdminController.php';

class Admin_TrailerController extends Admin_AppAdminController
{

    protected $_form = null;    

    public function init()
    {
    	$this->setControllerId('trailer');
    	$this->searchTitle = 'Trailer';
    	parent::init();
    }

    public function indexAction()
    {	

    }

    public function addAction()
    {
		$this->view->albumStore = constant('ALBUM_STORE_AUTOCOMPLETE');	
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
    	$this->view->paginator = Admin_Model_TrailerMapper::adminView($pagingOptions, $filters, $order);
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
			    'trailer_title'   => $request->getParam('trailer_title'),
	            'video'   => $request->getParam('video'),
				'album_id'   => $request->getParam('album_id'),
				'published'   => $request->getParam('published', '0'),
			);	
			if($id = $request->getParam('trailer_id', null)){
				$data['trailer_id'] = $id;
			}	
			//Zend_Debug::dump($data,  $label = 'Trailer:', $echo = true);exit;		
			$mapper  = new Admin_Model_TrailerMapper();
			$mapper->save($data);
			
			$overlay = $request->getParam('overlay', null);
			if($overlay != '1')
			$this->_redirect(ADMIN_ACCESS_PATH . '/trailer/view');

		}

		$this->_helper->layout()->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
			
    }
    
    public function editAction()
    {
    	$request = $this->getRequest();
    	if ($request->isGet()) {    		 
    		$trailer_id = $request->getParam('trailer_id');    		
    		$trailer    = new Admin_Model_Trailer();
			$mapper   = new Admin_Model_TrailerMapper();
			$mapper->find($trailer_id, $trailer);
			$this->view->trailer = $trailer; 
			$this->view->albumStore = constant('ALBUM_STORE_AUTOCOMPLETE');
			//Zend_Debug::dump($trailer,  $label = 'Trailer:', $echo = true);exit;
    	}
    }
    
    public function getSearchOptions(){
    
    	if($this->searchOptions === null){
	    	$this->searchOptions = array(
	    			'trailer_name' => array( 	'type' => 'text',
	    									'label' => 'Trailer'
	    								),
	    			'album_id' => array( 'type' => 'autocomplete',
	    									'label' => 'Album',
	    									'label_field' => 'album_name',
	    									'store' => constant('ALBUM_STORE_AUTOCOMPLETE'),
	    									'alias' => 'a'
	    								),	
	    			'published'    => array('type' => 'radio',
	    									'label' => 'Published',
	    									'radio_options' => array('-1' => 'Doesn\'t matter',
	    															'1' => 'Yes',
	    															'0' => 'No')
	    								),															
	    		);
    	}
    	return $this->searchOptions;	
    }
    
    public function getOrderbyOptions(){
    	if($this->orderbyOptions === null) {
    		$this->orderbyOptions = array(	
    					'first_orderby' => 	array(	'label' => '1. Order by ',
    												'options' => array(	'' => '--Field--', 
    																	'a.album_name' => 'Album')
    										),
    				);
    	} 
    	return $this->orderbyOptions;
    }
    
    public function getUpdateOneFields(){
    	if($this->updateOneFields === null){
    		$this->updateOneFields = array(
    						'published' => array('trailer_id', 'published'),
    				);
    	}
    	return $this->updateOneFields;
    }  
    
}
