<?php

class ErrorController extends GC_Controller_Action
{
    public function init()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            $this->view->isJson = True;
        }
        $this->_helper->layout->setLayout('frontend');
    }
    public function errorAction()
    {
        $translate = GC_Translate::get();
        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:

                // 404 error -- controller or action not found
                $errCode = 404;
                $message = $translate->_('Page not found');
                break;
            default:
                // application error
                $errCode = 500;
                $message = $translate->_('Application error');
                break;
        }
        $this->getResponse()->setHttpResponseCode($errCode);
        $this->view->errorCode =  $errCode;
        $this->view->message = $message;
        $this->view->titleData = array($message, $errCode);
        if(isset($errors->exception) && $errors->exception instanceof Exception)
        {
            $this->view->exception = $errors->exception;
        }
        else
        {
            $this->view->exception = new Exception($message, $errCode);
        }
        $this->view->resourceType = 'error';
        $this->view->request = $errors->request;
    }
    public function privilegesAction()
    {
        $translate = GC_Translate::get();
        $errCode = 403;
        $this->getResponse()->setHttpResponseCode($errCode);
        $this->view->errorCode =  $errCode;
        $message = $translate->_('Forbidden');
        $this->view->message = $message;
        $this->view->titleData = array($message, $errCode);
        $this->view->exception = new Exception($message, $errCode);
        if(isset(Zend_Registry::getInstance()->userData))
        {
            $this->view->userData = Zend_Registry::getInstance()->userData;
        }
        $this->view->request   =  $this->getRequest();
        $this->view->resourceType = 'error';
        $this->render('error');
    }
}