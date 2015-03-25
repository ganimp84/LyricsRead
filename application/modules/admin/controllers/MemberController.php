<?php

class Admin_MemberController extends Zend_Controller_Action
{

	public function init()
	{
		$this->_helper->layout->setLayout('admin-public');
	}

	public function indexAction()
	{
		// action body
	}

	public function loginAction()
	{
		$request = $this->getRequest();
		$this->view->assign('action', ADMIN_ACCESS_PATH. "/auth");
		$this->view->assign('title', 'Webmaster Login:');
		$this->view->assign('username', 'User Name');
		$this->view->assign('password', 'Password');
	}

	public function authAction()
	{
		$request 	= $this->getRequest();
		$registry 	= Zend_Registry::getInstance();
		$auth		= Zend_Auth::getInstance();

		$DB = $registry['DB'];

		$authAdapter = new Zend_Auth_Adapter_DbTable($DB);
		$authAdapter->setTableName('member')
		->setIdentityColumn('username')
		->setCredentialColumn('password');

		// Set the input credential values
		$uname = $request->getParam('username');
		$paswd = $request->getParam('password');
		$authAdapter->setIdentity($uname);
		$authAdapter->setCredential(md5($paswd));

		// Perform the authentication query, saving the result
		$result = $auth->authenticate($authAdapter);
		//Zend_Debug::dump($result, $label = 'Auth Result:', $echo = true);exit;
		if($result->isValid()){
			//print_r($result);
			$data = $authAdapter->getResultRowObject(null,'password');
			$auth->getStorage()->write($data);
			$this->_redirect(ADMIN_ACCESS_PATH . '/album/view');
		}else{
			$this->_redirect(ADMIN_ACCESS_PATH .'/login');
		}
	}

	public function logoutAction()
	{
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		$this->_redirect(ADMIN_ACCESS_PATH . '/login');
	}

}





