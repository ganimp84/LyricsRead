<?php

abstract class Admin_AppAdminController extends Zend_Controller_Action
{

	protected $_controller_id = null;
	protected $searchTitle = null;
	protected $searchOptions = null;
    protected $orderbyOptions = null;
    protected $updateOneFields = null;
    protected $commonConfig = null;
    
    public function __call($method, array $arguments = array()){
    	if (@preg_match('/(.+)(Action)$/', $method, $match)) {
    		$updateOneFields = $this->getUpdateOneFields();
    		if(array_key_exists($match[1], $updateOneFields)){
	    		$fields = $updateOneFields[$match[1]];	
	    		$this->updateOne($fields);
    		}
        }
        $this->_redirect(ADMIN_ACCESS_PATH . '/' . $this->_controller_id.'/view');    	
    }
	
	public function init()
	{	
		$this->verifyWebMasterLogin();
				
		$action = $this->getRequest()->getActionName();
    	$id = $this->_controller_id.'_'.$action;
    	
		$layout = Zend_Layout::getMvcInstance();
		$view = $layout->getView(); 
    	$activeNav = $view->navigation()->findById($id);    	 	
    	$activeNav->active = true;
    	
    	$this->view->assign('adminPath', ADMIN_ACCESS_PATH);
    	
    	if(!$this->commonConfig){
    		$this->commonConfig = Zend_Registry::get('common_config');
    	}
    	$this->view->assign('commonConfig', $this->commonConfig);
    	$this->_helper->layout->setLayout('admin');
    	if ($this->getRequest()->isGet()) {
			$overlay = $this->getRequest()->getParam('overlay', null);
			if($overlay && $overlay == '1'){		
				$this->_helper->layout()->disableLayout();
			}
		}
				
		$this->view->overlay = $overlay; 
		//Zend_Debug::dump($view, $label = 'Options:', $echo = true);exit;
	}
	
	public function verifyWebMasterLogin(){
		$auth		= Zend_Auth::getInstance();
		
		if(!$auth->hasIdentity()){
	  		$this->_redirect(ADMIN_ACCESS_PATH . '/login');
		}
		$user		= $auth->getIdentity();
		//Zend_Debug::dump($user, $label = 'Auth Result:', $echo = true);exit;
		if($user->type != 'admin'){
	  		$this->_redirect(ADMIN_ACCESS_PATH . '/login');
		}		
		
		$first_name	= $user->first_name;
		$username	= $user->username;
		$request = $this->getRequest();
		$logoutUrl  = $request->getBaseURL(). ADMIN_ACCESS_PATH . '/logout';
		
		$this->view->assign('user', $user);
		$this->view->assign('username', $first_name);
		$this->view->assign('urllogout',$logoutUrl);
	}

	public function indexAction()
	{
		// action body
	}
	
	public function setControllerId($_controller_id){
		$this->_controller_id = $_controller_id;
	}
	
	public function getControllerId(){
		return $this->_controller_id;
	}	

	public function getSubmittedFilters(){
		$request = $this->getRequest();
		$searchOptions = $this->getSearchOptions();
		$filters = array();
		foreach($searchOptions as $field => $option){
			switch($option['type']){
				case 'text':
					$filters[$field] = $this->getTextFilter($field);
					break;
				case 'radio':
				case 'select':	
					$filters[$field] = $this->getRadioFilter($field);
					break;
				case 'autocomplete':
					$autoFieldId = $this->getRequest()->getParam($field, 0);
					if(empty($autoFieldId)){
						$filters[$field] = null;
						break;
					} 
					
					//Zend_Debug::dump(empty($autoFieldId), $label = 'Options:', $echo = true);exit;
					
					if(!empty($option['alias']))
						$field = $option['alias'].'.'.$field;
					$filters[$field] = array("$field = ?", $autoFieldId);
					
					break;	
			}
		}
		//Zend_Debug::dump($filters, $label = 'Options:', $echo = true);exit;
		return $filters;
	}

	public function getTextFilter($field){
		$stype = $this->getRequest()->getParam('stype_'.$field, 'start');
		$search = $this->getRequest()->getParam($field, '');
		if(empty($stype) || empty($search)){
			return null;
		}
		switch($stype){
			case 'start':
				return array("$field LIKE ?", "$search%");
			case 'end':
				return array("$field LIKE ?", "%$search");
			case 'contains':
				return array("$field LIKE ?", "%$search%");
			case 'exact':
				return array("$field = ?", $search);
		}
	}

	public function getRadioFilter($field){
		$search = $this->getRequest()->getParam($field, '-1');
		if($search == -1){
			return null;
		} else {
			return array("$field = ?" , $search);
		}
	}
	
	public function getSubmittedOrderby(){
    
    	$request = $this->getRequest();
    	$orderbyOptions = $this->getOrderbyOptions();
    	$order = array();    	
    	if(is_array($orderbyOptions)) {
    		foreach($orderbyOptions as $order_field => $option){
    			$field = $this->getRequest()->getParam($order_field, '');
    			$sort_type = $this->getRequest()->getParam('sorttype_'.$order_field, '');
    			if(!empty($field)){
    				$order[] = "$field $sort_type";
    			}
    		}
    	}
    	
    	return $order;    
    }    
    
    public function viewAction(){
    	$this->view->searchAction = ADMIN_ACCESS_PATH . '/'.$this->_controller_id.'/view';
    	$this->view->searchTitle = $this->searchTitle;
    	$this->view->addScriptPath(APPLICATION_PATH . '/modules/default/views/scripts');
    }
    
    public function updateOne($fields){
   		$request = $this->getRequest();
   		
    	if ($request->isGet() && is_array($fields)) {
    		$data = array();
    		foreach($fields as $field){
	    		$data[$field] = $request->getParam($field);
    		}
    		
    		if($data[$fields[1]] != '1' && $data[$fields[1]] != 0)
    			$data[$fields[1]] = '0';
    		
    		//Zend_Debug::dump($data, $label = 'Options:', $echo = true);exit;
    		$mapperName = 'Admin_Model_'.ucfirst($this->_controller_id).'Mapper';
			$mapper  = new $mapperName();
			$mapper->save($data);			 
    	}
    	$this->_redirect(ADMIN_ACCESS_PATH . '/' . $this->_controller_id.'/view');
    }
    
    public function updateManyAction()
    {
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$select_action = $request->getParam('select_action', '');
    		$items_selected = $request->getParam($this->_controller_id.'_selected', '');
    		list($field, $value) = explode('__', $select_action);
    		if($value != '0' && $value != '1')
    			$value = '0';
    		
    		$data = array( $field => $value);
    		//Zend_Debug::dump($data, $label = 'Options:', $echo = true);exit;	
    		$mapperName = 'Admin_Model_'.ucfirst($this->_controller_id).'Mapper';
			$mapper  = new $mapperName();
    		$mapper->saveMany($data, $items_selected);	
    			
    	}
    	$this->_redirect(ADMIN_ACCESS_PATH . '/' . $this->_controller_id.'/view');
    } 

    abstract protected function getSearchOptions();
    
    abstract protected function getOrderbyOptions();
    
    abstract protected function getUpdateOneFields();

}

