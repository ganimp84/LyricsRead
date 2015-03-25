<?php

class Site_Plugin_SiteNav extends Zend_Controller_Plugin_Abstract {
	public function preDispatch(Zend_Controller_Request_Abstract $request)
    {    	
    	if('site' == $request->getModuleName() || 'default' == $request->getModuleName()){ 
    		$layout= Zend_Controller_Action_HelperBroker::getStaticHelper('Layout');
    		$view = $layout->getView();
    		$config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/site_navigation.xml', 'nav');

    		$navigation = new Zend_Navigation($config);
    		$view->navigation($navigation);
    	}
    }
}