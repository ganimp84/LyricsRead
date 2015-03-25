<?php

class Site_Plugin_SiteConfig extends Zend_Controller_Plugin_Abstract {
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$siteConfigFile = APPLICATION_PATH . '/configs/site.ini';
		$iniParser = new Zend_Config_Ini($siteConfigFile);
		$siteConfigs = $iniParser->toArray();	
		Zend_Registry::set('site_config', $siteConfigs);
		
		$socialConfigFile = APPLICATION_PATH . '/configs/social.ini';
		$iniParser = new Zend_Config_Ini($socialConfigFile);
		$socialConfigs = $iniParser->toArray();	
		Zend_Registry::set('social_config', $socialConfigs);
	}
}