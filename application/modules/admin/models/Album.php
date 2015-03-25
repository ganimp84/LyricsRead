<?php

class Admin_Model_Album
{
	protected $_album_id;
	protected $_album_name;
	protected $_language_id;	
	protected $_locked;
	protected $_featured;
	protected $_has_photo;
	protected $_image;
	protected $_created_on;
	protected $_modified_on;
	
	protected $_language;

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
			throw new Exception('Invalid album property');
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid album property');
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
	
	public function setAlbumId($album_id)
	{
		$this->_album_id = (int) $album_id;
		return $this;
	}

	public function getAlbumId()
	{
		return $this->_album_id;
	}	

	public function setAlbumName($album_name)
	{		
		$this->_album_name = (string) $album_name;
		return $this;
	}

	public function getAlbumName()
	{
		return $this->_album_name;
	}

	public function setLanguageId($language_id)
	{
		$this->_language_id = (int) $language_id;
		return $this;
	}

	public function getLanguageId()
	{
		return $this->_language_id;
	}
	
	public function setLocked($locked)
	{
		$this->_locked = (int) $locked;
		return $this;
	}

	public function getLocked()
	{
		return $this->_locked;
	}
	
	public function setFeatured($featured)
	{
		$this->_featured = (int) $featured;
		return $this;
	}

	public function getFeatured()
	{
		return $this->_featured;
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
	
	public function setCreatedOn($ts)
	{
		$this->_created_on = $ts;
		return $this;
	}

	public function getCreatedOn()
	{
		return $this->_created_on;
	}
	
	/* Parent Objects */
	
	public function setLanguage(Admin_Model_Language $language)
	{
		$this->_language = $language;
		return $this;
	}

	public function getLanguage()
	{
		return $this->_language;
	}
	
}
