<?php
class Formation_Login
{
    static public function logout()
    {
        if(Zend_Session::sessionExists())
        {
            Zend_Auth::getInstance()->clearIdentity();
        }
        Zend_Registry::getInstance()->user = False;
        Zend_Registry::getInstance()->userName = False;
        Zend_Registry::getInstance()->userRole = 'guest';
    }
    static public function login(Formation_Login_Interfaces_User $user)
    {
        if (Zend_Auth::getInstance()->hasIdentity()
            && Zend_Auth::getInstance()->getIdentity() === $user->getIdentity())
        {
            //if(Zend_Session::sessionExists())
            //{
            //    Zend_Session::regenerateId();
            //}
            Zend_Registry::getInstance()->user = $user;
            Zend_Registry::getInstance()->userName = $user->getIdentity();
            $role = $user->getUserRole();
            Zend_Registry::getInstance()->userRole = ($role) ? $role : 'guest';
        }
        //else
        //{
        //    throw new GC_Exception('identity of user is not confirmed'.$plusLog);
        //}
    }
}