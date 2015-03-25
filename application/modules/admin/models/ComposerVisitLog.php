<?php

class Admin_Model_ComposerVisitLog
{
	protected $_visit_id;
	protected $_composer_id;
	protected $_remote_address;
	protected $_visit_time;
	
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
			throw new Exception('Invalid artist property');
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid artist property');
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
	
	public function setVisitId($visit_id)
	{
		$this->_visit_id = (int) $visit_id;
		return $this;
	}

	public function getVisitId()
	{
		return $this->_visit_id;
	}	

	public function setComposerId($composer_id)
	{		
		$this->_composer_id = (int) $composer_id;
		return $this;
	}

	public function getComposerId()
	{
		return $this->_composer_id;
	}

	public function setRemoteAddress($remote_address)
	{		
		$this->_remote_address = (string) $remote_address;
		return $this;
	}

	public function getRemoteAddress()
	{
		return $this->_remote_address;
	}
	
	public function setVisitTime($visit_time)
	{
		$this->_visit_time = (int) $visit_time;
		return $this;
	}

	public function getVisitTime()
	{
		return $this->_visit_time;
	}
	
}

