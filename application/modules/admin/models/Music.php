<?php

class Admin_Model_Music
{
	protected $_music_id;
	protected $_music_title;
	protected $_lyrics;
	protected $_published;
	protected $_has_photo;
	protected $_image;
	protected $_video;
	protected $_album_id;
	protected $_created_on;
	protected $_modified_on;
	
	protected $_album;
	protected $_composers;
	protected $_singers;
	protected $_lyricists;
	protected $_artists;
	
	public function __construct(array $options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		}
	}

	public function __set($name, $value)
	{
		$method = 'set' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid music property');
		}
		$this->$method($value);
	}

	public function __get($name)
	{
				
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid music property');
		}
		return $this->$method();
	}

	public function setOptions(array $options)
	{
		$methods = get_class_methods($this);		
		foreach ($options as $key => $value) {
			$key = preg_replace("/_([a-z])/e", "strtoupper('\\1')", $key);
			$method = 'set' . ucfirst($key);
			if (in_array($method, $methods)) {
				$this->$method($value);
			}
		}
		return $this;
	}
	
	public function setMusicId($music_id)
	{
		$this->_music_id = (int) $music_id;
		return $this;
	}

	public function getMusicId()
	{
		return $this->_music_id;
	}	

	public function setMusicTitle($music_title)
	{		
		$this->_music_title = (string) $music_title;
		return $this;
	}

	public function getMusicTitle()
	{
		return $this->_music_title;
	}
	
	public function setLyrics($lyrics)
	{		
		$this->_lyrics = (string) $lyrics;
		return $this;
	}

	public function getLyrics()
	{
		return $this->_lyrics;
	}
		
	public function setHasPhoto($has_photo)
	{
		$this->_has_photo = (int) $has_photo;
		return $this;
	}

	public function getHasPhoto()
	{
		return $this->_has_photo;
	}
	
	public function setImage($image)
	{
		$this->_image = (string) $image;
		return $this;
	}

	public function getImage()
	{
		return $this->_image;
	}
	
	public function setVideo($video)
	{
		$this->_video = (string) $video;
		return $this;
	}

	public function getVideo()
	{
		return $this->_video;
	}
	
	public function setPublished($published)
	{
		$this->_published = (int) $published;
		return $this;
	}

	public function getPublished()
	{
		return $this->_published;
	}
	
	public function setAlbum(Admin_Model_Album $album)
	{
		$this->_album = $album;
		return $this;
	}

	public function getAlbum()
	{
		return $this->_album;
	}
	
	public function setComposers(array $composers)
	{
		$this->_composers = $composers;
		return $this;
	}

	public function getComposers()
	{
		return $this->_composers;
	}
	
	public function setSingers(array $singers)
	{
		$this->_singers = $singers;
		return $this;
	}

	public function getSingers()
	{
		return $this->_singers;
	}
		
	public function setLyricists(array $lyricists)
	{
		$this->_lyricists = $lyricists;
		return $this;
	}

	public function getLyricists()
	{
		return $this->_lyricists;
	}

	public function setArtists(array $artists)
	{
		$this->_artists = $artists;
		return $this;
	}

	public function getArtists()
	{
		return $this->_artists;
	}		
	
	public function setCreatedOn($ts)
	{
		$this->_created_on = $ts;
		return $this;
	}

	public function getCreatedOn()
	{
		return $this->_created_on;
	}	
	
}

