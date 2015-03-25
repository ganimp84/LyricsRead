<?php

class Admin_Model_Trailer
{
	protected $_trailer_id;
	protected $_trailer_title;
	protected $_video;
	protected $_album_id;
	protected $_published;
	
	protected $_album;

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
			throw new Exception('Invalid trailer property');
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid trailer property');
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
	
	public function setTrailerId($trailer_id)
	{
		$this->_trailer_id = (int) $trailer_id;
		return $this;
	}

	public function getTrailerId()
	{
		return $this->_trailer_id;
	}
	
	public function setTrailerTitle($trailer_title)
	{
		$this->_trailer_title = (string) $trailer_title;
		return $this;
	}

	public function getTrailerTitle()
	{
		return $this->_trailer_title;
	}
	
	public function setAlbumId($album_id)
	{
		$this->_album_id = (int) $album_id;
		return $this;
	}

	public function getAlbumId()
	{
		return $this->_album_id;
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
		
	public function setVideo($video)
	{
		$this->_video = (string) $video;
		return $this;
	}

	public function getVideo()
	{
		return $this->_video;
	}
	
	/* Parent Objects */
	
	public function setAlbum(Admin_Model_Album $album)
	{
		$this->_album = $album;
		return $this;
	}

	public function getAlbum()
	{
		return $this->_album;
	}
	
}
