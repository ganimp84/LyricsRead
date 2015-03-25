<?php 

class Site_Bootstrap extends Zend_Application_Module_Bootstrap {
		
	protected function _initPlugins(){
		$front = Zend_Controller_Front::getInstance();
		$front->registerPlugin(new Site_Plugin_SiteNav());
		$front->registerPlugin(new Site_Plugin_SiteConfig());		
	}
}

