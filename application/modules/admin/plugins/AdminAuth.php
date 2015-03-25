<?php

class Admin_Plugin_AdminAuth extends Zend_Controller_Plugin_Abstract {
	public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	$adminAuth = false;
    	$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$user = $auth->getIdentity();
			if($user->type == 'admin') $adminAuth = true;
		}
		
		if(!$adminAuth)
		$this->_response->setRedirect(ADMIN_ACCESS_PATH .'/login');
    }
}