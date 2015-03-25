<?php

require_once APPLICATION_PATH.'/modules/admin/controllers/AppAdminController.php';

class Admin_MusicController extends Admin_AppAdminController
{

    public function init()
    {
    	$this->setControllerId('music');
    	$this->searchTitle = 'Music';
    	parent::init();
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
    
	public function viewAction()
    {	
    	parent::viewAction();
    	$adminConfig = Zend_Registry::get('admin_config');
    	
    	$request = $this->getRequest();
    	$sort_by = $request->getParam('sort', 'music_title');
    	$sort_type = $request->getParam('type', 'asc');
    	$filters = null;    	
   		
    	if($request->isPost()){
    		$filters = $this->getSubmittedFilters();
    		$order = $this->getSubmittedOrderby();	
    	}    	
    	$pagingOptions = $adminConfig['PAGINATION'];
    	$pagingOptions['CURRENT_PAGE_NUMBER'] = $request->getParam('page', 1);
    	$this->view->paginator = Admin_Model_MusicMapper::adminView($pagingOptions, $filters, $order);
        $this->view->searchOptions = $this->getSearchOptions();
        //Zend_Debug::dump($this->view->paginator, $label = 'Options:', $echo = true);
        $this->view->orderbyOptions = $this->getOrderbyOptions();
        $this->view->request = $request;
        $this->view->sort_by = $sort_by;$this->view->sort_type = ($sort_type == 'asc') ? 'desc' : 'asc';
    }

    public function addAction()
    {
        $this->view->albumStore = constant('ALBUM_STORE_AUTOCOMPLETE');
        $this->view->composerStore = constant('COMPOSER_STORE_AUTOCOMPLETE');
        $this->view->singerStore = constant('SINGER_STORE_AUTOCOMPLETE');
        $this->view->lyricistStore = constant('LYRICIST_STORE_AUTOCOMPLETE');
        $this->view->artistStore = constant('ARTIST_STORE_AUTOCOMPLETE');
        
        $this->view->artistStore = constant('ARTIST_STORE_AUTOCOMPLETE');
    }
    
    public function saveAction()
    {	
		$request = $this->getRequest();
		if ($request->isPost()) {			
			$data = array(
				'album_id'   => $request->getParam('album_id', ''),
				'music_title'   => $request->getParam('music_title', ''),
				'lyrics'   => $request->getParam('lyrics', ''),			
				'published'   => $request->getParam('published', 0),
				'image'   => $request->getParam('image', ''),            
	            'video'   => $request->getParam('video', ''),            
	            'created_on' => date('Y-m-d H:i:s'),
			);	
			if($id = $request->getParam('music_id', null)){
				$data['music_id'] = $id;
			}	
			//Zend_Debug::dump($_POST, $label = 'Options:', $echo = true);exit;
			
			$mapper  = new Admin_Model_MusicMapper();
			$music_id = $mapper->save($data);
			
			if(empty($id)) $this->saveDependents($music_id);
			
			$save_type = $request->getParam('save_type', 'save');
			switch($save_type){
				case 'save': $action = 'view'; break;
				case 'save_and_new': $action = 'add'; break;
				case 'save_and_edit': $action = 'edit/music_id/'.$music_id; break;
			}			
			$this->_redirect(ADMIN_ACCESS_PATH . '/music/'. $action);

		}

		$this->_helper->layout()->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
			
    }
    
	public function updateRelAction()
    {	
		$request = $this->getRequest();
		if ($request->isGet()) {	
			$music_id = $request->getParam('music_id', null);		
						
			$music  = new Admin_Model_Music();
			$mapper  = new Admin_Model_MusicMapper();
			$mapper->find($music_id, $music);
			
			$album = $music->getAlbum();
			$data = array(	'locked' => 0,
							'album_id' => $album->getAlbumId());
			$mapper  = new Admin_Model_AlbumMapper();
			$mapper->save($data);
			
			$composers = $music->getComposers();
			if(is_array($composers)){
				$composerIds = array();
				foreach($composers as $composer){
					array_push($composerIds, $composer->getComposerId());
				}
				$data = array('locked' => 0);
				$composer_ids = implode(',', $composerIds);
				$mapper  = new Admin_Model_ComposerMapper();
				$mapper->saveMany($data, $composer_ids);
			}
			
			$singers = $music->getSingers();
			if(is_array($singers)){
				$singerIds = array();
				foreach($singers as $singer){
					array_push($singerIds, $singer->getSingerId());
				}
				$data = array('locked' => 0);
				$singer_ids = implode(',', $singerIds);
				$mapper  = new Admin_Model_SingerMapper();
				$mapper->saveMany($data, $singer_ids);
			}
			
			$lyricists = $music->getLyricists();
			if(is_array($lyricists)){
				$lyricistIds = array();
				foreach($lyricists as $lyricist){
					array_push($lyricistIds, $lyricist->getLyricistId());
				}
				$data = array('locked' => 0);
				$lyricist_ids = implode(',', $lyricistIds);
				$mapper  = new Admin_Model_LyricistMapper();
				$mapper->saveMany($data, $lyricist_ids);
			}
			
			$artists = $music->getArtists();
			if(is_array($artists)){
				$artistIds = array();
				foreach($artists as $artist){
					array_push($artistIds, $artist->getArtistId());
				}
				$data = array('locked' => 0);
				$artist_ids = implode(',', $artistIds);
				$mapper  = new Admin_Model_ArtistMapper();
				$mapper->saveMany($data, $artist_ids);
			}
			
			//Zend_Debug::dump($lyricist_ids, $label = 'Options:', $echo = true);exit;
			
			$this->_redirect(ADMIN_ACCESS_PATH . '/music/view');

		}

		$this->_helper->layout()->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
			
    }

    public function saveRelAction()
    {	
		$request = $this->getRequest();
		if ($request->isPost()) {		
			$this->saveDependents(
						$request->getParam('music_id', ''),
						 true);			
		}	
		
		$this->_redirect(ADMIN_ACCESS_PATH . '/music/view');
		//$this->_helper->layout()->disableLayout();
		//$this->getHelper('viewRenderer')->setNoRender();
			
    }
    
    public function saveDependents($music_id, $delete = false){
    	$request = $this->getRequest();
		$data['music_id'] = $music_id;
		
		$rel_types = array('composer', 'singer', 'lyricist', 'artist');
		foreach($rel_types as $type){
			$stringIds = $request->getParam($type.'_ids', '');
			if(empty($stringIds)){
				$arrayIds = array();
			} else {
				$arrayIds = explode(',', $stringIds);					
			}
			$mapperName = 'Admin_Model_Music' . ucfirst($type) . 'Mapper';
			$mapper  = new $mapperName();
			$mapper->delete($music_id);
			foreach($arrayIds as $id){
				$data[$type.'_id'] = $id;
				//Zend_Debug::dump($data, $label = 'Options:', $echo = true);exit;
				$mapper->save($data);
				unset($data[$type.'_id']);
			}
		}
    }
    
    public function editAction()
    {
    	$request = $this->getRequest();
    	if ($request->isGet()) {    		 
    		$music_id = $request->getParam('music_id');    		
    		$music    = new Admin_Model_Music();
			$mapper   = new Admin_Model_MusicMapper();
			$mapper->find($music_id, $music);
			//Zend_Debug::dump($music, $label = 'Options:', $echo = true);exit;
			$this->view->music = $music; 
    	}
    	
	   	$this->view->albumStore = constant('ALBUM_STORE_AUTOCOMPLETE');
        $this->view->composerStore = constant('COMPOSER_STORE_AUTOCOMPLETE');
        $this->view->singerStore = constant('SINGER_STORE_AUTOCOMPLETE');
        $this->view->lyricistStore = constant('LYRICIST_STORE_AUTOCOMPLETE');
        $this->view->artistStore = constant('ARTIST_STORE_AUTOCOMPLETE');
    }
    
	public function getSearchOptions(){
    
    	if($this->searchOptions === null){
	    	$this->searchOptions = array(
	    			'music_title' => array( 	'type' => 'text',
	    									'label' => 'Music Title'
	    								),
	    			'album_id' => array( 'type' => 'autocomplete',
	    									'label' => 'Album',
	    									'label_field' => 'album_name',
	    									'store' => constant('ALBUM_STORE_AUTOCOMPLETE'),
	    									'alias' => 'a'
	    								),										
	    			'has_photo' => array( 	'type' => 'radio',
	    									'label' => 'With photo',
	    									'radio_options' => array('-1' => 'Doesn\'t matter',
	    															'1' => 'Yes',
	    															'0' => 'No')
	    								),										
	    			'published' => array( 	'type' => 'radio',
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
    																	'music_title' => 'Music Title')
    										),    									
    				);
    	} 
    	return $this->orderbyOptions;
    }
    
    public function getUpdateOneFields(){
    	if($this->updateOneFields === null){
    		$this->updateOneFields = array(
    						'photo' => array('music_id', 'has_photo'),
    						'published' => array('music_id', 'published'),
    				);
    	}
    	return $this->updateOneFields;
    }  

}

	