<?php

class Admin_Plugin_AdminConfig extends Zend_Controller_Plugin_Abstract {
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$adminConfigFile = APPLICATION_PATH . '/configs/admin.ini';
		$iniParser = new Zend_Config_Ini($adminConfigFile);
		$adminConfigs = $iniParser->toArray();	
		Zend_Registry::set('admin_config', $adminConfigs);
	}
}