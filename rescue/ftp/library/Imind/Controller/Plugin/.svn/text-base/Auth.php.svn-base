<?php
/**
 * 
 * Imind Library
 *
 * @category   Imind
 * @package    Imind_Controller
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * @category   Imind
 * @package    Imind_Controller
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class Imind_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract {
    
    /**
     * Check for authentication before the Front Controller dispatches
     *   the incoming request
     *
     * @param  Zend_Controller_Request_Abstract the request 
     * @return void
     */  
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        Zend_Loader::loadClass("Zend_Auth");
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity() && $request->getControllerName() != "auth" && ($request->getActionName() != "login" || $request->getActionName() != "slogin")) {
            $request->setControllerName("auth");
            $request->setActionName("login");
        }
        if ($auth->hasIdentity()) {
            if (class_exists("Doctrine")) {
                $user = Doctrine::getTable("User")->find($auth->getIdentity()->id);
            } else {
                $user = $auth->getIdentity();
            }
            
            if (!$this->_hasPermission($user,$request)) {
                $request->setControllerName("index");
                $request->setActionName("index");
            }
        }
    }
    
    /**
     * Checks the user's permission for this request
     * 
     * @param User
     * @param Zend_Controller_Request_Abstract
     * @return boolean
     */
    protected function _hasPermission($user,Zend_Controller_Request_Abstract $request) {
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        foreach ($user->role->resources as $resource) {
            $actions = split(",",$resource->actions);
            if ($controller === "index" || $controller === "auth" || $controller === "error") {
                return true;
            } elseif ($resource->controller === $controller
                && ($action === "index" || in_array($action,$actions)
                    || $actions[0] === "all")) {
                return true;    
            }
        }
        return false;
    }
}
