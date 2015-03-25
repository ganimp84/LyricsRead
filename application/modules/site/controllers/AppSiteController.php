<?php

class Site_AppSiteController extends Zend_Controller_Action
{

	protected $_siteConfig = null;
	protected $_commonConfig = null;
	protected $_page_id = null;
	protected $_userIdentity = null;
	protected $_filters = array();


	public function init()
	{
		$this->verifySiteLogin();
		$this->view->addScriptPath(APPLICATION_PATH . '/modules/default/views/scripts');
		$this->_siteConfig = Zend_Registry::get('site_config');
		$this->_helper->layout->setLayout('site');

		$this->_socialConfig = Zend_Registry::get('social_config');
		if(!$this->_commonConfig){
			$this->_commonConfig = Zend_Registry::get('common_config');
		}
		$this->view->socialConfig = $this->_socialConfig;
		$this->view->assign('adminPath', ADMIN_ACCESS_PATH);
		$this->view->musicStore = constant('MUSIC_STORE_AUTOCOMPLETE');
		$this->view->addScriptPath(APPLICATION_PATH . '/modules/site/views/scripts');
		$this->view->pageUrl = $this->getRequest()->getPathInfo();
		
		$this->view->lang_name = LANGUAGE_NAME;
		
		$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
		$options = $bootstrap->getOptions();
		$this->view->appSettings = $options['appSettings'];
		//Zend_debug::dump($options['appSettings']);
		
		if($this->_userIdentity && $this->_userIdentity->type == 'admin'){
			$this->view->appSettings['ads']['show'] = 0;
		}
		$this->view->ads = $this->getAds();
		/*
		$namespace = new Zend_Session_Namespace(); // default namespace
		$this->view->show_filter_help = 1;
		if (!isset($namespace->filter_help)) {			
			$namespace->filter_help = 1;		
		} else if($namespace->filter_help <= 1){
			$namespace->filter_help += 1;
		} else {
			$this->view->show_filter_help = 0;
		}
		*/
		$this->logVisitor();
		$this->view->recentlyAdded = $this->getFewRecents();		
	}

	public function verifySiteLogin(){
		$auth		= Zend_Auth::getInstance();
		$this->_userIdentity = $auth->getIdentity();
		//Zend_Debug::dump($user, $label = 'Auth Result:', $echo = true);exit;
		$this->view->assign('userIdentity', $this->_userIdentity);
	}

	public function activateMenu(){
		$layout = Zend_Layout::getMvcInstance();
		$view = $layout->getView();
		$activeNav = $view->navigation()->findById($this->_page_id);
		$activeNav->active = true;
	}

	public function indexAction()
	{
		// action body
	}

	public function songsByAction()
	{
		$songsByType = $this->getRequest()->getParam('songs_by_type', 'album');
		$this->_page_id = $songsByType."_list";
		$this->activateMenu();
		$songsByType = ucfirst($songsByType);
		$songsBy = $this->getRequest()->getParam('songs_by', 'mankatha');
		$search = $this->getRequest()->getParam('search', '');
		$patterns = array('/-/', '/songs?/');
		$replacements = array(' ', '');
		$songsBy = preg_replace($patterns, $replacements, $songsBy);
		$songsBy = trim($songsBy);		
		
		 
		$cache = $this->getCacheInstance('song-by');
		$cacheId = $this->getRequest()->getPathInfo();
		$cacheId = preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheId);
		if(!($data = $cache->load($cacheId)))
		{
			$pagingOptions = $this->_siteConfig['PAGINATION'];
			$pagingOptions['CURRENT_PAGE_NUMBER'] = $this->getRequest()->getParam('page', 1);
			$mapper   = new Admin_Model_MusicMapper();
			$methodName = "findAllBy".$songsByType."Name";
			$this->applyLanguageFilter();	
			$data['paginator'] = $mapper->$methodName($songsBy, $pagingOptions, $search, $this->_filters);
			
			$mapperName = "Admin_Model_".$songsByType."Mapper";
			$itemName = "Admin_Model_".$songsByType;
			$item = new $itemName();
			$mapper = new $mapperName();
			$methodName = "findOneBy".$songsByType."Name";
			$mapper->$methodName($songsBy, $item);
			$data['songsOf'] = $item;
			$cache->save($data);
		} 
		
		$this->view->paginator = $data['paginator'];
		$this->view->songsOf = $data['songsOf'];
		$imagesPath = $this->_siteConfig['LIST_IMAGES_PATH']['MUSIC'];
		$imageSize = $this->_siteConfig['LIST_IMAGES_SIZE']['MUSIC'];
		$this->view->itemStore = constant('MUSIC_STORE_AUTOCOMPLETE');

		$this->view->imageSrc = $imagesPath.'/_{IMAGE_ID}_'.$imageSize.'.jpg';
		$this->view->songsByType = $songsByType;
		$this->view->songsBy = $songsBy;
		//Zend_Debug::dump($musics, $label = 'Options:', $echo = true);exit;
	}

	public function songAction()
	{
		$this->_page_id = "song_list";
		$this->activateMenu();
		$musicTitle = $this->getRequest()->getParam('music_title', 'vilayadu-mankatha');
		$musicTitle = preg_replace('/-/', ' ', $musicTitle);		
		
		$cache = $this->getCacheInstance('song');
		$cacheId = $this->getRequest()->getPathInfo();
		$cacheId = preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheId);
		if(!($music = $cache->load($cacheId)))
		{
			$music  = new Admin_Model_Music();
			$mapper = new Admin_Model_MusicMapper();
			$mapper->findOneByMusicTitle($musicTitle, $music);
			$cache->save($music);
		}
		 
		//Zend_Debug::dump($music, $label = 'Options:', $echo = true);exit;
		$this->view->music = $music;
		$this->view->pageId = 'song_lyrics'; 
		//Zend_Debug::dump($this->view->navigation(), $label = 'Options:', $echo = true);exit;
	}
	
	public function trailerAction()
	{
		$this->_page_id = "trailer_list";
		$this->activateMenu();
		$trailerTitle = $this->getRequest()->getParam('trailer_title', '');
		$trailerTitle = preg_replace('/-/', ' ', $trailerTitle);		
		
		$cache = $this->getCacheInstance('trailer');
		$cacheId = $this->getRequest()->getPathInfo();
		$cacheId = preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheId);
		if(!($trailer = $cache->load($cacheId)))
		{
			$trailer  = new Admin_Model_Trailer();
			$mapper = new Admin_Model_TrailerMapper();
			$mapper->findOneByTrailerTitle($trailerTitle, $trailer);
			$cache->save($trailer);
		}
		 
		$this->view->trailer = $trailer;
		//Zend_Debug::dump($this->view->navigation(), $label = 'Options:', $echo = true);exit;
	}

	public function listSearchAction()
	{
		 
		$itemType = $this->getRequest()->getParam('item_type', 'album');
		$itemType = $itemType == 'song' ? 'music' : $itemType;
		$this->view->itemType = $itemType;

		$imagesPath = $this->_siteConfig['LIST_IMAGES_PATH'][strtoupper($itemType)];
		$imageSize = $this->_siteConfig['LIST_IMAGES_SIZE'][strtoupper($itemType)];

		$this->view->imageSrc = $imagesPath.'/_{IMAGE_ID}_'.$imageSize.'.jpg';
		$this->view->addScriptPath(APPLICATION_PATH . '/modules/default/views/scripts');
		$this->view->itemStore = constant(strtoupper($itemType).'_STORE_AUTOCOMPLETE');

		$itemType = ucfirst($itemType);
		$mapperName = 'Admin_Model_' . $itemType . 'Mapper';
		$mapper = new $mapperName();
		$pagingOptions = $this->_siteConfig['PAGINATION'];
		$pagingOptions['CURRENT_PAGE_NUMBER'] = $this->getRequest()->getParam('page', 1);

		$this->view->paginator = $mapper->fetchAllList($pagingOptions);
		//Zend_Debug::dump($this->getRequest(), $label = 'Options:', $echo = true);exit;
	}
	
	public function filterAction(){
		$redirectUrl = $this->getRequest()->getHeader('REFERER');
		$lang_id = $this->getRequest()->getParam('lang_id', '');
		$lang_name = $this->getRequest()->getParam('lang_name', '');
		$namespace = new Zend_Session_Namespace(); // default namespace
		$namespace->lang_id = $lang_id;
		$namespace->lang_name = $lang_name;	
		$this->_redirect($redirectUrl);
	}
	
	public function getCacheInstance($type = ''){
		
		$cacheDir = APPLICATION_PATH . '/..' .$this->_commonConfig['CACHING']['DIR'].'/'. $type;
		$frontendOptions = array(
		   'lifetime' => 3600,
		   'automatic_serialization' => true
		);
		$backendOptions = array(
    		'cache_dir' => $cacheDir,
			'file_name_prefix' => 'zend_cache_'.LANGUAGE_ID,
		);
		
		return Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
	}
	
	public function getAds(){
		
		$leaderBoard = array(	
						'<a href="http://click.linksynergy.com/fs-bin/click?id=UmKpvOJoUGc&offerid=146261.10006033&type=4&subid=0"><IMG alt="US iTunes, App Store, iBookstore, and Mac App Store" border="0" src="http://images.apple.com/itunesaffiliates/US/banners/Match_728x90.jpg"></a><IMG border="0" width="1" height="1" src="http://ad.linksynergy.com/fs-bin/show?id=UmKpvOJoUGc&bids=146261.10006033&type=4&subid=0">',
						'<a href="http://click.linksynergy.com/fs-bin/click?id=UmKpvOJoUGc&offerid=222795.10000467&type=4&subid=0"><IMG alt="Fly Roundtrip for $149 or less. Discount Flights GUARANTEED. Book Now!" border="0" src="http://affiliates.onetravel.com/banners/promo4/images/728X90.gif"></a><IMG border="0" width="1" height="1" src="http://ad.linksynergy.com/fs-bin/show?id=UmKpvOJoUGc&bids=222795.10000467&type=4&subid=0">',
					);
		$rand_key = array_rand($leaderBoard);
		$ads['leaderBoard'] = $leaderBoard[$rand_key];			
					
		$rightAdItemOne = array(
						'<a href="http://click.linksynergy.com/fs-bin/click?id=UmKpvOJoUGc&offerid=7097.10000088&subid=0&type=4"><IMG border="0"   alt="LinkShare_336x280" src="http://ad.linksynergy.com/fs-bin/show?id=UmKpvOJoUGc&bids=7097.10000088&subid=0&type=4&gridnum=12"></a>',
					);			
					
		$rand_key = array_rand($rightAdItemOne);		
		$ads['rightAdItemOne'] = $rightAdItemOne[$rand_key];
		
		$rightAdItemTwo = array(
						'<a href="http://click.linksynergy.com/fs-bin/click?id=UmKpvOJoUGc&offerid=7097.10000088&subid=0&type=4"><IMG border="0"   alt="LinkShare_336x280" src="http://ad.linksynergy.com/fs-bin/show?id=UmKpvOJoUGc&bids=7097.10000088&subid=0&type=4&gridnum=12"></a>',
					);			
					
		$rand_key = array_rand($rightAdItemTwo);		
		$ads['rightAdItemTwo'] = $rightAdItemTwo[$rand_key];

		$leftAdItems = array(
						'<a href="http://click.linksynergy.com/fs-bin/click?id=UmKpvOJoUGc&offerid=7097.10000079&subid=0&type=4"><IMG border="0"   alt="LinkShare_160x600Skyscpr" src="http://ad.linksynergy.com/fs-bin/show?id=UmKpvOJoUGc&bids=7097.10000079&subid=0&type=4&gridnum=9"></a>',
						'<a href="http://click.linksynergy.com/fs-bin/click?id=UmKpvOJoUGc&offerid=222795.10000468&type=4&subid=0"><IMG alt="Fly Roundtrip for $149 or less. Discount Flights GUARANTEED. Book Now!" border="0" src="http://affiliates.onetravel.com/banners/promo4/images/160x600.jpg"></a><IMG border="0" width="1" height="1" src="http://ad.linksynergy.com/fs-bin/show?id=UmKpvOJoUGc&bids=222795.10000468&type=4&subid=0">',
					);			
					
		$rand_key = array_rand($leftAdItems);
		$ads['leftAdItems'] = $leftAdItems[$rand_key];

		return $ads;
	}

	public function applyLanguageFilter(){
		$lang_id = LANGUAGE_ID;
		if($lang_id){
			$this->_filters['language_id = ?'] = $lang_id;
		}
	}
    
	public function getFewRecents()
    {
    	$cache = $this->getCacheInstance('few-recent');
		$cacheId = 'show_recent';
		if(!($data = $cache->load($cacheId)))
		{
    		$mapper   = new Admin_Model_MusicMapper();
			$data['recent'] = $mapper->fetchRecent();
	    	$cache->save($data);
		} 
		return $data['recent'];
    }
    
    private function logVisitor()
    {
    
    	$logFile = APPLICATION_PATH . '/..' .$this->_commonConfig['CACHING']['DIR'].'/log/visitors.log';
	    $writer = new Zend_Log_Writer_Stream($logFile);
	    $logger = new Zend_Log($writer);
		$datetime = date('d/m/Y h:i:s');
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$visitorIp = $this->get_client_ip();
		$visitorInfo = "Visitor IP: {$visitorIp} | Agent: {$agent} | Time: {$datetime} \r\n";
		if(!preg_match('/bot|spider/i', $agent)){
	    	$logger->info($visitorInfo);
		}
    
    }
    
	// Function to get the client ip address
	protected function get_client_ip() {
	    $ipaddress = '';
	    if ($_SERVER['HTTP_CLIENT_IP'])
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    else if($_SERVER['HTTP_X_FORWARDED_FOR'])
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    else if($_SERVER['HTTP_X_FORWARDED'])
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    else if($_SERVER['HTTP_FORWARDED_FOR'])
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    else if($_SERVER['HTTP_FORWARDED'])
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    else if($_SERVER['REMOTE_ADDR'])
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    else
	        $ipaddress = 'UNKNOWN';
	 
	    return $ipaddress;
	}

}







