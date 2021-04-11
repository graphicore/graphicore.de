<?php
//formerly known as: As_Controller_Plugin_ErrorHandler
//found at http://www.zfforums.com/zend-framework-components-13/model-view-controller-mvc-21/predispatch-zend_controller_plugin_errorhandler-170.html
class GC_Controller_Plugin_NotFound extends Zend_Controller_Plugin_ErrorHandler
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $frontController = Zend_Controller_Front::getInstance();
        $dispatcher = $frontController->getDispatcher();

        if ($frontController->getParam('noErrorHandler') || $this->_isInsideErrorHandlerLoop) {
            return;
        }

        $error = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);


        if (!$dispatcher->isDispatchable($request)) {
            $error->type = self::EXCEPTION_NO_CONTROLLER;
        } elseif (!$this->isProperAction($dispatcher, $request)) {
            $error->type = self::EXCEPTION_NO_ACTION;
        }
        if (isset($error->type)) {
            $this->_isInsideErrorHandlerLoop = true;

            $error->request = clone $request;
            $request->setParam('error_handler', $error)
                    ->setModuleName($this->getErrorHandlerModule())
                    ->setControllerName($this->getErrorHandlerController())
                    ->setActionName($this->getErrorHandlerAction());
        }
    }

    public function isProperAction($dispatcher, $request)
    {
        $className = $dispatcher->loadClass($dispatcher->getControllerClass($request));
        $actionName = $request->getActionName();
        if (empty($actionName)) {
            $actionName = $dispatcher->getDefaultAction();
        }
        $methodName = $dispatcher->formatActionName($actionName);

        $class = new ReflectionClass($className);
        if ($class->hasMethod($methodName)) {
            return true;
        }
        return false;
    }
}