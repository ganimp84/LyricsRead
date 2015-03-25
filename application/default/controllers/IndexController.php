<?php

require_once APPLICATION_PATH.'/modules/site/controllers/AppSiteController.php';

class IndexController extends Site_AppSiteController
{

    public function init()
    {
       	parent::init();
    }

    public function indexAction()
    {
    	$this->view->homePage = true;
    	return $this->_forward('index', 'Music', 'site');
    	$this->_page_id = "home";
    	$this->activateMenu();        
        $this->view->musicStore = constant('MUSIC_STORE_AUTOCOMPLETE');
		$this->adCode = $adCode;
        $homePageSong = 209;
        $this->getHomePageSong($homePageSong);
    }
    
	public function getHomePageSong($music_id)
    {
    	$music    = new Admin_Model_Music();
    	$mapper   = new Admin_Model_MusicMapper();
    	$mapper->findOneByMusicId($music_id, $music);
    	
    	$this->view->music = $music;
    	//Zend_Debug::dump($music, $label = 'Home page Song:', $echo = true);exit;
    }


}

