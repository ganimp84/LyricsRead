<?php

class Admin_Model_Lyricist
{
	protected $_lyricist_id;
	protected $_lyricist_name;
	protected $_locked;
	protected $_has_photo;
	protected $_image;
	protected $_created_on;
	protected $_modified_on;
	
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
			throw new Exception('Invalid lyricist property');
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid lyricist property');
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
	
	public function setLyricistId($lyricist_id)
	{
		$this->_lyricist_id = (int) $lyricist_id;
		return $this;
	}

	public function getLyricistId()
	{
		return $this->_lyricist_id;
	}	

	public function setLyricistName($lyricist_name)
	{		
		$this->_lyricist_name = (string) $lyricist_name;
		return $this;
	}

	public function getLyricistName()
	{
		return $this->_lyricist_name;
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
	
}

