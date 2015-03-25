<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initMagiicQuotesHandler()
	{
		if (get_magic_quotes_gpc()) {
			$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
			while (list($key, $val) = each($process)) {
				foreach ($val as $k => $v) {
					unset($process[$key][$k]);
					if (is_array($v)) {
						$process[$key][stripslashes($k)] = $v;
						$process[] = &$process[$key][stripslashes($k)];
					} else {
						$process[$key][stripslashes($k)] = stripslashes($v);
					}
				}
			}
			unset($process);
		}
	}

	protected function _initRewrite() {
		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();
			
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini');
		//Zend_Debug::dump($config);
		$router->addConfig($config,'routes');
	}
	/**
	 * Loads app-wide constants from ini file
	 */
	protected function _initDefineConstants()
	{
		$constantsFile = APPLICATION_PATH . '/configs/constants.ini';
		$iniParser = new Zend_Config_Ini($constantsFile);
		$constants = $iniParser->toArray();

		foreach ($constants as $constName => $constantVal) {
			if (defined($constName)) {
				throw new Zend_Exception('Constant ' . $constName . ' is already defined.');
			}
			define($constName, $constantVal);
		}
	}

	protected function _initCommonConfigs()
	{
		$commonConfigFile = APPLICATION_PATH . '/configs/common.ini';
		$iniParser = new Zend_Config_Ini($commonConfigFile);
		$commonConfigs = $iniParser->toArray();
		Zend_Registry::set('common_config', $commonConfigs);
	}


}

