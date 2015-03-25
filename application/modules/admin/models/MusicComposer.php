<?php

class Admin_Model_MusicComposer
{
	protected $_music_id;
	protected $_composer_id;
	
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
		$method = 'get' . $name;
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

	public function setComposerId($composer_id)
	{		
		$this->_composer_id = (string) $composer_id;
		return $this;
	}

	public function getComposerId()
	{
		return $this->_composer_id;
	}
}

