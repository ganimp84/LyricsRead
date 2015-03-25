<?php

require_once APPLICATION_PATH.'/modules/site/controllers/AppSiteController.php';

class ErrorController extends Site_AppSiteController
{
	public function init()
    {
    	parent::init();        
    }
    
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
        $content = '';
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                 // ... get some output to display...
                $content .= "<h1>Page not found!</h1>" . PHP_EOL;
                $content .= "<span>[#404]</span><br />" . PHP_EOL;
                $content .= "<p>Sorry, but the page you are looking for has not been found. Try checking the URL for errors, then hit the refresh button on your browser.</p>";
                $content .= "<h1>Alternate options</h1>" . PHP_EOL;
                $content .= "<div class='clear'></div>";
                $content .= "<div class='error-alt'><ol>";
                $content .= "<li>Click here <a href='/' title='Home page'> <img src='/img/home.png' alt='Home page' /></a> to vist our home page";
                $content .= "<li><a href='mailto:webmaster@lyricsread.com' title='Contact webmaster'>Click here to contact our webmaster</a></li>";
                $content .= "</ol></div>";
                $this->view->message = $content;
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }
        
        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->log('Request Parameters', $priority, $errors->request->getParams());
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }


}

