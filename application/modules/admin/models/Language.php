<?php

class Admin_Model_Language
{
	protected $_language_id;	
	protected $_language_name;
	protected $_language_code;
	protected $_published;

	
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
	
	public function setLanguageId($language_id)
	{
		$this->_language_id = (int) $language_id;
		return $this;
	}

	public function getLanguageId()
	{
		return $this->_language_id;
	}	

	public function setLanguageName($language_name)
	{		
		$this->_language_name = (string) $language_name;
		return $this;
	}

	public function getLanguageName()
	{
		return $this->_language_name;
	}

	public function setLanguageCode($language_code)
	{
		$this->_language_code = $language_code;
		return $this;
	}

	public function getLanguageCode()
	{
		return $this->_language_code;
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

}

