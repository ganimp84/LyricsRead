<?php

class AjaxController extends Zend_Controller_Action
{
    
    public function init()
    {
    	$this->_helper->layout()->disableLayout();	
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
    
 	public function albumSongsAction()
    {       	
    	if(!$this->getRequest()->isPost()){
    		return null;
    	}
    	$album_id = $this->getRequest()->getParam('album_id', null);
    	$mapper = new Admin_Model_MusicMapper();
    	$musics = $mapper->findAlbumSongs($album_id);
    	    	
    	//Zend_Debug::dump($musics, $label = 'Musics:', $echo = true);exit;    	
    	$this->view->musics = $musics; 	
    	
    }
    
	public function listLangAction()
    {       	
    	if(!$this->getRequest()->isGet()){
    		return null;
    	}
    	$mapper = new Admin_Model_LanguageMapper();
    	$where = array('published = ?' => 1);
    	$langs = $mapper->fetchCol(null, $where);
    	    	
    	$this->view->lang_id = LANGUAGE_ID;    	
    	$this->view->langs = $langs;		    	
    	
    }
    
	public function vlAction()
    {       	    	
    	if(!$this->getRequest()->isPost()){
    		return null;
    	}
    	$type = $this->getRequest()->getParam('lt', 'music');
    	$itemId = $this->getRequest()->getParam('i', null);
    	switch(strtolower($type)){
    		case 'music':
    			$mapper   = new Admin_Model_MusicVisitLogMapper();
    			$data['music_id'] = $itemId;
    			break;
    		case 'album':
    			$mapper   = new Admin_Model_AlbumVisitLogMapper();
    			$data['album_id'] = $itemId;
    			break;	 
    		case 'composer':
    			$mapper   = new Admin_Model_ComposerVisitLogMapper();
    			$data['composer_id'] = $itemId;
    			break; 
    		case 'lyricist':
    			$mapper   = new Admin_Model_LyricistVisitLogMapper();
    			$data['lyricist_id'] = $itemId;
    			break; 
    		case 'artist':
    			$mapper   = new Admin_Model_ArtistVisitLogMapper();
    			$data['artist_id'] = $itemId;
    			break;
    		case 'singer':
    			$mapper   = new Admin_Model_SingerVisitLogMapper();
    			$data['singer_id'] = $itemId;
    			break;	
    		case 'trailer':
    			$mapper   = new Admin_Model_TrailerVisitLogMapper();
    			$data['trailer_id'] = $itemId;
    			break;	 			   			
    	}
    	$data['remote_address'] = $this->getRequest()->getServer('REMOTE_ADDR');
    	$mapper->save($data);
    	//Zend_Debug::dump($mapper);
    	$this->getHelper('viewRenderer')->setNoRender();    	    	
    	
    }
    
    public function relatedSongsAction()
    {       	
    	if(!$this->getRequest()->isPost()){
    		return null;
    	}
    	$musicId = $this->getRequest()->getParam('i', null);
    	$albumId = $this->getRequest()->getParam('a', null);
    	$mapper   = new Admin_Model_MusicMapper();
    	$this->view->relatedSongs = $mapper->findAlbumSongs($albumId, $musicId);
    	
    }
    
	public function wikiInfoboxAction()
    {       	
    	$wikiName = $this->getRequest()->getParam('wikiName', null);
    	$Url = "http://en.wikipedia.org/w/api.php?format=json&action=query&titles={$wikiName}&prop=revisions&rvprop=content";
		$response = $this->fetchInfoFromWikiApi($Url);
		$response = Zend_Json_Decoder::decode($response);
		if(is_array($response)){
			foreach($response['query']['pages'] as $pageId => $page){
				$pageid = $page['pageid'];
				break;
			}
		}
		$Url = "http://en.wikipedia.org/w/api.php?format=json&action=parse&pageid={$pageid}";
		Zend_Debug::dump($Url);
		$response = $this->fetchInfoFromWikiApi($Url);
		$response = Zend_Json_Decoder::decode($response);
		Zend_Debug::dump($response);
    	$this->getHelper('viewRenderer')->setNoRender();	    	
    	
    }
    
    
	public function fetchInfoFromWikiApi($Url)
    {

		// is cURL installed yet?
		if (!function_exists('curl_init')){
			return null;
			//die('Sorry cURL is not installed!');
		}

		// OK cool - then let's create a new cURL resource handle
		$ch = curl_init();

		// Now set some options (most are optional)

		// Set URL to download
		curl_setopt($ch, CURLOPT_URL, $Url);

		// Include header in result? (0 = yes, 1 = no)
		curl_setopt($ch, CURLOPT_HEADER, 0);

		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:14.0) Gecko/20100101 Firefox/14.0.1');

		//curl_setopt($ch, CURLOPT_PROXY, '172.25.218.132:8085');

		// Should cURL return or print out the data? (true = return, false = print)
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Timeout in seconds
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		// Download the given URL, and return output
		$output = curl_exec($ch);
		// Check if any error occured

		if(!curl_errno($ch))
		{
			$info = curl_getinfo($ch);

			//Zend_Debug::dump($info);
		}
		// Close the cURL resource, and free system resources
		curl_close($ch);

		return $output;
    }   
 
}

