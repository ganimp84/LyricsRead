<?php

require_once APPLICATION_PATH.'/modules/admin/controllers/AppAdminController.php';

class Admin_UploadController extends Admin_AppAdminController
{

    public function init()
    {
    	$this->setControllerId('upload');
    	$this->_helper->layout->setLayout('admin');
    	parent::init();
    	
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    	$form = $this->getUploadForm();
		$this->view->form = $form;
        // action body
    }

    public function albumAction()
    {
        // action body
    }
    
	public function getUploadForm(){
    	$form = new Zend_Form();
    	$form->setAction(ADMIN_ACCESS_PATH . '/upload/save');
    	$form->setAttrib('enctype', 'multipart/form-data');
    	$form->setAttrib('id', 'upload_form');
    	
    	$url_type = new Zend_Form_Element_Select('url_type');
    	$url_type->setLabel('Type')
    				->setAttrib('id', 'url_type')
    				->setAttrib('name', 'url_type');
    	
    	$options = array('' => '-select-',
    					'image' => 'Image',
						'video' => 'video',);
    	$url_type->addMultiOptions($options);
    	
		$upload_file = new Zend_Form_Element_File('upload_file');
		$upload_file->setLabel(null)
		->setRequired(true)
		->removeDecorator('HtmlTag');

		// creating object for submit button
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Upload')
		->setAttrib('id', 'uploadbutton')
		->setAttrib('class', 'adminButton');

		$url_type->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'span', 'class' => 'element')),
		));
		
		// adding elements to form Object
		$form->addElements(array($url_type, $upload_file, $submit));
    	return $form;
    }
    
    public function saveAction(){
    	if ($this->_request->isPost()) {
			$form = $this->getUploadForm();
 			$formData = $this->_request->getPost();
 			$uploadPath = APPLICATION_PATH . "/../uploads/upload";
 			if ($form->isValid($formData)) {

 				$url_type = $this->_request->getParam('url_type', 'video');
 				/* Uploading Document File on Server */
 				$upload = new Zend_File_Transfer_Adapter_Http();
 				$upload->setDestination($uploadPath);
 				try {
 					// upload received file(s)
 					$upload->receive();
 				} catch (Zend_File_Transfer_Exception $e) {
 					$e->getMessage();
 				}
 				
 				if($upload->isUploaded()){ 				
	 				$filename = $_FILES['upload_file']['name'];
	 				$file = $uploadPath . '/'.$filename;
	 				$data = array();
	 				$duplicate = array();
	 				$dataStr = '';
	 				$duplicateStr = '';
	 				if (($handle = fopen($file, "r")) !== FALSE) {
	 					$records = array();
	 					while (($content = fgetcsv($handle, 1000, ",")) !== FALSE) {
	 						$mapper = new Admin_Model_MusicMapper();			 				
			 				switch($url_type){
	 							case 'image':
	 								$row['image'] = trim($content[3]);
	 							break;		 						
		 						case 'video':			 				
		 							$row['video'] = trim($content[3]);
		 						break;
	 						}
	 						$records[$content[0]] = $row;
	 						
	 						//if(count($data) > 20) break;
	 						
	 					}
	 					
	 					$mapper->updateMany($records);
	 				}
 				}
 			}
    	}
    	
    	//Zend_Debug::dump($records, "Data:", true);exit;
	 	//Zend_Debug::dump($duplicateStr, "Duplicate:", true);exit;
    	$this->_redirect(ADMIN_ACCESS_PATH . '/upload/index');
    	
    }
    
	public function getSearchOptions(){	
    	return $this->searchOptions;	
    }    
    
    public function getOrderbyOptions(){
    	return $this->orderbyOptions;
    }
    
    public function getUpdateOneFields(){    	
    	return $this->updateOneFields;
    }


}



