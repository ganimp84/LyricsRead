<?php

require_once APPLICATION_PATH.'/modules/admin/controllers/AppAdminController.php';

class Admin_ImportController extends Admin_AppAdminController
{

    public function init()
    {
    	$this->setControllerId('import');
    	$this->_helper->layout->setLayout('admin');
    	parent::init();
    	
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    	$form = $this->getImportForm();
		$this->view->form = $form;
        // action body
    }

    public function albumAction()
    {
        // action body
    }
    
	public function getImportForm(){
    	$form = new Zend_Form();
    	$form->setAction(ADMIN_ACCESS_PATH . '/import/save');
    	$form->setAttrib('enctype', 'multipart/form-data');
    	$form->setAttrib('id', 'import_form');
    	
    	$import_type = new Zend_Form_Element_Select('import_type');
    	$import_type->setLabel('Type')
    				->setAttrib('id', 'import_type')
    				->setAttrib('name', 'import_type');
    	
    	$options = array('' => '-select-',
    					'album' => 'Album',
						'composer' => 'Composer',
    					'singer' => 'Singer',
    					'lyricist' => 'Lyricist',
						'artist' => 'Artist',
						'song' => 'Song');
    	$import_type->addMultiOptions($options);
    	
		$import_file = new Zend_Form_Element_File('import_file');
		$import_file->setLabel(null)
		->setRequired(true)
		->removeDecorator('HtmlTag');

		// creating object for submit button
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Import')
		->setAttrib('id', 'importbutton')
		->setAttrib('class', 'adminButton');

		$import_type->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'span', 'class' => 'element')),
		));
		
		// adding elements to form Object
		$form->addElements(array($import_type, $import_file, $submit));
    	return $form;
    }
    
    public function saveAction(){
    	if ($this->_request->isPost()) {
			$form = $this->getImportForm();
 			$formData = $this->_request->getPost();
 			$importPath = APPLICATION_PATH . "/../uploads/import";
 			if ($form->isValid($formData)) {

 				$import_type = $this->_request->getParam('import_type', 'album');
 				$importPath .= '/'.$import_type;
 				/* Uploading Document File on Server */
 				$upload = new Zend_File_Transfer_Adapter_Http();
 				$upload->setDestination($importPath);
 				try {
 					// upload received file(s)
 					$upload->receive();
 				} catch (Zend_File_Transfer_Exception $e) {
 					$e->getMessage();
 				}
 				
 				if($upload->isUploaded()){ 				
	 				$filename = $_FILES['import_file']['name'];
	 				$file = $importPath . '/'.$filename;
	 				$data = array();
	 				$duplicate = array();
	 				$dataStr = '';
	 				$duplicateStr = '';
	 				if (($handle = fopen($file, "r")) !== FALSE) {
	 					while (($content = fgetcsv($handle, 1000, ",")) !== FALSE) {
	 						switch($import_type){
	 							case 'album':
	 								$mapper = new Admin_Model_AlbumMapper();
			 						$row['album_name'] = trim($content[0]);
			 						$row['language_id'] = trim($content[1]);
									
			 						$res = $mapper->fetchRow($row['album_name']);			
			 						if($res) {
			 							$duplicate[] = $row;
			 						} else {
			 							$data[] = $row;
			 						}			
			 									 						
		 						break;
		 						
		 						case 'lyricist':
	 								$mapper = new Admin_Model_LyricistMapper();
			 						$info = trim($content[0]);
			 						$infoArray = explode(',', $info);
			 						foreach($infoArray as $detail){
			 							$row['lyricist_name'] = trim($detail);
			 							$res = $mapper->fetchRow($row['lyricist_name']);	
					 					if($res) {
				 							$duplicate[] = $row;
				 						} else {
				 							$data[] = $row;
				 						}
			 						}			 						
		 						break;
		 						
		 						case 'singer':
	 								$mapper = new Admin_Model_SingerMapper();
			 						$info = trim($content[0]);
			 						$infoArray = explode(',', $info);
			 						
			 						foreach($infoArray as $detail){
			 							$row['singer_name'] = trim($detail);
			 							$res = $mapper->fetchRow($row['singer_name']);
			 							
					 					if($res) {
				 							$duplicate[] = $row;
				 							$duplicateStr .= $row['singer_name']."\r\n";
				 						} else {
				 							$data[] = $row;
				 							$dataStr .= $row['singer_name']."\r\n";
				 						}
			 						}			 						
		 						break;
	 						}
	 						//if(count($data) > 20) break;
	 						
	 					}
	 					
	 					$mapper->insertMany($data);
	 				}
 				}
 			}
    	}
    	
    	//Zend_Debug::dump($dataStr, "Data:", true);
	 	//Zend_Debug::dump($duplicateStr, "Duplicate:", true);exit;
    	$this->_redirect(ADMIN_ACCESS_PATH . '/import/index');
    	
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



