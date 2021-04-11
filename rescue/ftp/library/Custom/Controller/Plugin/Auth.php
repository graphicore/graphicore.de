<?php
class Custom_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    private $_acl;
    private $_redirects = array(
        'noauth' => array(
            'module'        => 'default',
            'controller'    => 'error',
            'action'        => 'privileges'
        ),
        'noacl' => array(
            'module'        => 'default',
            'controller'    => 'error',
            'action'        => 'privileges'
        ),
    );
    private $_usersModel = 'Backend_Model_User';
    //public function __construct($acl)
    //{
    //    $this->_acl = $acl;
    //}
    public function __construct(Zend_Acl $acl = Null){
        if($acl)
        {
            $this->_acl = $acl;
        }
        else
        {
            $this->_acl = Zend_Registry::getInstance()->acl;
        }
        if(!($this->_acl instanceof Zend_Acl))
        {
            throw new Custom_Controller_Exception('The Acl Implementation must be an instance of Zend_Acl');
        }

    }
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {

        $auth = False;
        Zend_Registry::getInstance()->userRole = False;
        //don't wake sleeping dogs...
        //if there is no session we dont want to start one
        if(Zend_Session::sessionExists())
        {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity())
            {
                $model = new $this->_usersModel();
                $user = $model->findOneByName($auth->getIdentity());
                if($user)
                {
                    Formation_Login::login($user);
                }
            }
        }

        //Currently roles come from the Database but the acl is generated in a file
        //if the role is not in the acl we log out the user
        if(!Zend_Registry::getInstance()->userRole
        || !$this->_acl->hasRole(Zend_Registry::getInstance()->userRole)
        )
        {
            Formation_Login::logout();
        }
        $role = Zend_Registry::getInstance()->userRole;

        //i have got acl from bootstrap ini
        //$this->_getAcl($role);
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        $resource = $module.'_'.$controller;
        if (!$this->_acl->has($resource))
        {
            $resource = null;
        }

        if (!$this->_acl->isAllowed($role, $resource, $action))
        {
            //if a user with identity tries to access a resource where she has no access
            $redirect = $this->_redirects['noacl'];
            if (!$auth || !$auth->hasIdentity())
            {
                //if a user without identity tries to access a resource where she has no access
                $redirect = $this->_redirects['noauth'];
            }
            $request->setModuleName($redirect['module']);
            $request->setControllerName($redirect['controller']);
            $request->setActionName($redirect['action']);
        }
    }
}
