<?php
class GraphicoreAcl extends Zend_Acl
{
    public function __construct()
    {
        //$this->add(new Zend_Acl_Resource('default'));
        $this->add(new Zend_Acl_Resource('default_error'));
        $this->add(new Zend_Acl_Resource('default_index'));

        //$this->add(new Zend_Acl_Resource('backend'));
        $this->add(new Zend_Acl_Resource('backend_index'));
        $this->add(new Zend_Acl_Resource('backend_user'));
        $this->add(new Zend_Acl_Resource('backend_group'));
        //$this->add(new Zend_Acl_Resource('backend_static'));
        $this->add(new Zend_Acl_Resource('backend_diary'));
        $this->add(new Zend_Acl_Resource('backend_filter'));
        $this->add(new Zend_Acl_Resource('backend_page'));

        $this->addRole(new Zend_Acl_Role('guest'));
        $this->addRole(new Zend_Acl_Role('member'), 'guest');
        $this->addRole(new Zend_Acl_Role('admin'));

        // Guest may only view content
        $this->deny('guest');
        $this->allow('guest', 'backend_user', 'login');
        $this->allow('guest', 'backend_user', 'recover');
        $this->allow('member', 'backend_user', 'logout');
        $this->allow('member', 'backend_user', 'setup');

        $this->allow('guest', 'default_index');
        $this->allow('guest', 'default_error');

        $this->allow('member', 'backend_index', 'index');

        //can only update his own pwd!
        //should not be able to change his name -> admin
        //so there might be another action better...
        $this->allow('member', 'backend_page');
        $this->allow('member', 'backend_diary');
        $this->allow('member', 'backend_filter');




        if(APPLICATION_ENV === 'development')
        {
            //$this->add(new Zend_Acl_Resource('backend_index'));
            //$this->allow('guest', 'backend_index');
            //$this->allow('guest', 'backend_user');
            //$this->allow('guest', 'backend_group');
            //$this->allow('guest', 'backend_static');
        }
        else
        {
            $this->deny('admin', 'backend_group');
        }

        //$this->allow('guest', 'default_soap');


        $this->allow('admin'); // unrestricted access
        $this->deny('admin', 'backend_user', 'recover');
        //this is only interesting for a guest
        $this->deny('member', 'backend_user', 'recover');

//        $this->allow('admin', 'backend_user');


        // Add authoring ACL check
        //Zend_Auth::getInstance()
        //$auth = $this->allow('member', 'forum', 'update', new MyAcl_Forum_Assertion($auth));
        // NOTE: Dependency on auth object to allow getIdentity() for authenticated user object
    }
    public function check($role = null, $resource = null, $privilege = null)
    {
        // check for this or:
        // Zend_Acl_Exception Resource 'backend_fake' not found
        if (!$this->has($resource))
        {
            $resource = null;
        }
        return $this->isAllowed($role, $resource, $privilege);
    }
}
