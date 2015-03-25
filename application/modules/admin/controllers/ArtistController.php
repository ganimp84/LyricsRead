<?php

require_once APPLICATION_PATH.'/modules/admin/controllers/AppAdminController.php';

class Admin_ArtistController extends Admin_AppAdminController
{

    public function init()
    {
    	$this->setControllerId('artist');
    	$this->searchTitle = 'Artist';
    	parent::init();
    }

    public function indexAction()
    {
        
    }
    
	public function viewAction()
    {	
    	parent::viewAction();
    	$adminConfig = Zend_Registry::get('admin_config');
    	
    	$request = $this->getRequest();
    	$sort_by = $request->getParam('sort', 'artist_name');
    	$sort_type = $request->getParam('type', 'asc');
    	$filters = null;    	
   		
    	if($request->isPost()){
    		$filters = $this->getSubmittedFilters();
    		$order = $this->getSubmittedOrderby();	
    	}    	
    	$pagingOptions = $adminConfig['PAGINATION'];
    	$pagingOptions['CURRENT_PAGE_NUMBER'] = $request->getParam('page', 1);
    	$this->view->paginator = Admin_Model_ArtistMapper::adminView($pagingOptions, $filters, $order);
        $this->view->searchOptions = $this->getSearchOptions();
        //Zend_Debug::dump($this->view->searchOptions, $label = 'Options:', $echo = true);
        $this->view->orderbyOptions = $this->getOrderbyOptions();
        $this->view->request = $request;
        $this->view->sort_by = $sort_by;$this->view->sort_type = ($sort_type == 'asc') ? 'desc' : 'asc';
    }

    public function addAction()
    {
        // action body
    }
    
    public function saveAction()
    {	
		$request = $this->getRequest();
		if ($request->isPost()) {
			
			$data = array(
	            'artist_name'   => $request->getParam('artist_name'),
				'image'   => $request->getParam('image', ''),            
	            'created_on' => date('Y-m-d H:i:s'),
			);	
			if($id = $request->getParam('artist_id', null)){
				$data['artist_id'] = $id;
			}			
			$mapper  = new Admin_Model_ArtistMapper();
			$mapper->save($data);
			$overlay = $request->getParam('overlay', null);
			if($overlay != '1')
			$this->_redirect(ADMIN_ACCESS_PATH . '/artist/view');

		}

		$this->_helper->layout()->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
			
    }
    
    public function editAction()
    {
    	$request = $this->getRequest();
    	if ($request->isGet()) {    		 
    		$artist_id = $request->getParam('artist_id');    		
    		$artist    = new Admin_Model_Artist();
			$mapper   = new Admin_Model_ArtistMapper();
			$mapper->find($artist_id, $artist);
			//Zend_Debug::dump($artist_id, $label = 'Options:', $echo = true);exit;
			$this->view->artist = $artist; 
    	}
    }
    
	public function getSearchOptions(){
    
    	if($this->searchOptions === null){
	    	$this->searchOptions = array(
	    			'artist_name' => array( 	'type' => 'text',
	    									'label' => 'Artist'
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
	    		);
    	}
    	return $this->searchOptions;	
    }
    
    public function getOrderbyOptions(){
    	if($this->orderbyOptions === null) {
    		$this->orderbyOptions = array(	
    					'first_orderby' => 	array(	'label' => '1. Order by ',
    												'options' => array(	'' => '--Field--',
    																	'artist_name' => 'Artist')
    										),    									
    				);
    	} 
    	return $this->orderbyOptions;
    }
    
    public function getUpdateOneFields(){
    	if($this->updateOneFields === null){
    		$this->updateOneFields = array(
    						'photo' => array('artist_id', 'has_photo'),
    						'lock' => array('artist_id', 'locked'),
    				);
    	}
    	return $this->updateOneFields;
    }  

}

