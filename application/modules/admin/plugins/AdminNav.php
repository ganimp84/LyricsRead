<?php

class Admin_Plugin_AdminNav extends Zend_Controller_Plugin_Abstract {
	public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	if('admin' == $request->getModuleName()){ 
    		$layout= Zend_Controller_Action_HelperBroker::getStaticHelper('Layout');
    		$view = $layout->getView();
    		$config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/navigation.xml', 'nav');

    		$navigation = new Zend_Navigation($config);
    		$view->navigation($navigation);
    	}
    }
}