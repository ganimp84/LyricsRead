<?php

require_once APPLICATION_PATH.'/modules/site/controllers/AppSiteController.php';

class IndexController extends Site_AppSiteController
{
	protected $_visitInterval = 7;

	public function init()
	{
		parent::init();
	}

	public function indexAction()
	{
		$this->view->homePage = true;
		$this->_page_id = "home";
		//return $this->_forward('index', 'Music', 'site');
		$this->activateMenu();
		$this->view->musicStore = constant('MUSIC_STORE_AUTOCOMPLETE');
		$this->adCode = $adCode;
		$homePageSong = 209;
		//$this->getHomePageSong($homePageSong);

		$cache = $this->getCacheInstance('home');
		$cacheId = $this->getRequest()->getPathInfo();
		$cacheId = preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheId);
		//Zend_Debug::dump($cacheId);
		if(!($data = $cache->load($cacheId)))
		{
			$data['topMusics'] = $this->getTopMusics();
			$data['topAlbums'] = $this->getTopAlbums();
			$data['topArtists'] = $this->getTopArtists();
			$data['topComposers'] = $this->getTopComposers();
			$data['topLyricists'] = $this->getTopLyricists();
			$data['topSingers'] = $this->getTopSingers();
			$cache->save($data);
		}

		$this->_visitInterval = $this->_siteConfig['TOP_ITEMS']['VISIT_INTERVAL'];
		$this->view->topMusics = $data['topMusics'];
		$this->view->topAlbums = $data['topAlbums'];
		$this->view->topArtists = $data['topArtists'];
		$this->view->topComposers = $data['topComposers'];
		$this->view->topLyricists = $data['topLyricists'];
		$this->view->topSingers = $data['topSingers'];
		$this->view->pageId = $this->_page_id;
		//Zend_Debug::dump($this->_siteConfig);
	}

	public function getHomePageSong($music_id)
	{
		$music    = new Admin_Model_Music();
		$mapper   = new Admin_Model_MusicMapper();
		$mapper->findOneByMusicId($music_id, $music);
			
		$this->view->music = $music;
		//Zend_Debug::dump($music, $label = 'Home page Song:', $echo = true);exit;
	}

	public function getTopMusics()
	{
		$mapper   = new Admin_Model_MusicVisitLogMapper();
		return $mapper->getTopMusics($this->_visitInterval);
		//Zend_Debug::dump($topMusics, $label = 'topMusics:', $echo = true);
	}

	public function getTopAlbums()
	{
		$mapper   = new Admin_Model_AlbumVisitLogMapper();
		return $mapper->getTopAlbums($this->_visitInterval);
		//Zend_Debug::dump($topAlbums, $label = 'topAlbums:', $echo = true);
	}

	public function getTopArtists()
	{
		$mapper   = new Admin_Model_ArtistVisitLogMapper();
		return $mapper->getTopArtists($this->_visitInterval);
		//Zend_Debug::dump($topArtists, $label = 'topArtists:', $echo = true);
	}

	public function getTopComposers()
	{
		$mapper   = new Admin_Model_ComposerVisitLogMapper();
		return $mapper->getTopComposers($this->_visitInterval);
		//Zend_Debug::dump($topComposers, $label = 'topComposers:', $echo = true);
	}

	public function getTopLyricists()
	{
		$mapper   = new Admin_Model_LyricistVisitLogMapper();
		return $mapper->getTopLyricists($this->_visitInterval);
		//Zend_Debug::dump($topLyricists, $label = 'topLyricists:', $echo = true);
	}

	public function getTopSingers()
	{
		$mapper   = new Admin_Model_SingerVisitLogMapper();
		return $mapper->getTopSingers($this->_visitInterval);
		//Zend_Debug::dump($topSingers, $label = 'topSingers:', $echo = true);
	}

	public function termsAction()
	{
			
	}

	public function privacyAction()
	{
			
	}
	
	public function sitemapAction()
	{
			
	}
	

	public function contactAction()
	{
		$captcha = new Zend_Captcha_Image();
		$captcha->setImgDir(APPLICATION_PATH . '/../img/captcha/');
		$captcha->setImgUrl($this->view->baseUrl('/img/captcha/'));
		$captcha->setFont(APPLICATION_PATH . '/../css/fonts/azoft-sans-bold.ttf');
		$captcha->setWordlen(6);
		$captcha->setFontSize(28);
		$captcha->setLineNoiseLevel(1);
		$captcha->setWidth(150);
		$captcha->setHeight(64);
		$captcha->generate();
		$this->view->captcha = $captcha;
	}

	public function submitContactAction(){
		$request = $this->getRequest();	
		if ($request->isPost()) {
			$captchaId = $request->getParam('captcha_id', null);
			$captcha = $request->getParam('captcha', null);
			$captchaSession = new Zend_Session_Namespace('Zend_Form_Captcha_'.$captchaId);
			if ($captchaId && $captchaSession->word && $captcha == $captchaSession->word)
			{

				$data = array(
				'topic'   => $request->getParam('topic', '1'),
	            'name'   => $request->getParam('name'),
				'email'   => $request->getParam('email'),
				'message'   => $request->getParam('message'),
				);
				if($id = $request->getParam('id', null)){
					$data['id'] = $id;
				}
				$mapper  = new Admin_Model_ContactMapper();
				$mapper->save($data);
				
				//Zend_Debug::dump($data);
			}
				
				

		}

		//$this->_helper->layout()->disableLayout();
		//$this->getHelper('viewRenderer')->setNoRender();
		$this->_redirect('/contact_us/');
	}

}

