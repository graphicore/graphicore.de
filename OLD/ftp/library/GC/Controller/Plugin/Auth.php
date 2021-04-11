<?php
class GC_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
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
    private $_usersModel = 'Gcbackend_Model_Users';
    //public function __construct($acl)
    //{
    //    $this->_acl = $acl;
    //}
    public function __construct(){}
    protected function _getAcl($role)
    {
        $this->_acl = new Zend_Acl();
        //this is too expensive, but as an proof of concept ok
        if($role === 'admin')
        {
            //someone has to fix it, right?
            $resources = Doctrine::getTable('AclResource')->findAll(Doctrine::HYDRATE_ARRAY);
            foreach($resources as $resource)
            {
                $this->_acl->add(new Zend_Acl_Resource($resource['name']));
            }
            $this->_acl->addRole(new Zend_Acl_Role('admin'));
            $this->_acl->allow($role); // unrestricted access
        }
        else
        {

            $roleTable = Doctrine::getTable('AclRole');
            $found = $roleTable->findOneByName($role);
            if(!$found)
            {
                $this->_acl->addRole(new Zend_Acl_Role($role));
                $this->_acl->deny($role);//deny all
                return;
            }
            $q = Doctrine_Query::create()
                ->select('r.name, p.name, x.action, y.name '
                 //
                 // p.name,
                 //  y.name'
                   )
                ->from('AclRole r')
                ->leftJoin('r.AclPrivileges p')
                ->leftJoin('p.AclRule x')
                ->leftJoin('p.AclResource y')
                ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
                ;
            $rolesTreeObj = Doctrine::getTable('AclRole')->getTree();
            $rolesTreeObj->setBaseQuery($q);
            $rootColumnName = $rolesTreeObj->getAttribute('rootColumnName');
            $rolesTree = $rolesTreeObj->fetchTree($found->$rootColumnName);
            $rolesTreeObj->resetBaseQuery();
            //throw new Exception(GC_Debug::Dump($rolesTree));
            $log = array();
            foreach($rolesTree as $node)
            {
                if($node['level'] == 0)
                {
                     // no access for the root of the tree without explicit allow
                     // this feels more secoure and will be inherited by the following roles
                    $this->_acl->addRole(new Zend_Acl_Role($node['name']));
                    $this->_acl->deny($role);
                }
                else
                {
                    $this->_acl->addRole(new Zend_Acl_Role($node['name']), $lastname);
                }
                //next will inherit from this name
                $lastname = $node['name'];
                foreach($node['AclPrivileges'] as $privilege)
                {
                    if(!$this->_acl->has($privilege['AclResource']['name']))
                    {
                        $this->_acl->add(new Zend_Acl_Resource($privilege['AclResource']['name']));
                    }
                    $log[] = $this->_aclAction(
                        $privilege['AclRule'][0]['action'],
                        $node['name'],
                        $privilege['AclResource']['name'],
                        $privilege['name']
                    );
                }
            }
            //throw new Exception(join('<br />',$log));
        }
    }
    protected function _aclAction($action, $role, $resource, $privilege)
    {
        $action = ('a' === $action) ? 'allow' : 'deny';
        $privilege = ($privilege === '_all') ? Null : $privilege;
        $this->_acl->$action($role, $resource, $privilege);
        $log = array($action, $role, $resource, $privilege);
        return join(', ',$log);
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
                $user = new $this->_usersModel();
                if($user->getUser($auth->getIdentity()))
                {
                    GC_Login::login($user);
                }
            }
        }
        if(!Zend_Registry::getInstance()->userRole)
        {
            GC_Login::logout();
        }
        
        
        $role = Zend_Registry::getInstance()->userRole;
        $this->_getAcl($role);
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $module = $request->getModuleName();
        $resource = $module.'_'.$controller;
        if (!$this->_acl->has($resource)) {
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
