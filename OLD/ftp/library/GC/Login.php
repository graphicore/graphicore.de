<?php
class GC_Login
{
    static public function logout()
    {
        if(Zend_Session::sessionExists())
        {
            Zend_Auth::getInstance()->clearIdentity();
        }
        Zend_Registry::getInstance()->userRole = 'guest';
        Zend_Registry::getInstance()->userData = False;
    }
    static public function login(GC_Login_Interfaces_User $user)
    {
        if (Zend_Auth::getInstance()->hasIdentity()
            && Zend_Auth::getInstance()->getIdentity() === $user->getIdentity())
        {
            if(Zend_Session::sessionExists()){
                Zend_Session::regenerateId();
            }
            Zend_Registry::getInstance()->userData = $user->getUserData();
            Zend_Registry::getInstance()->userRole = $user->getUserRole();
        }
        else
        {
            throw new GC_Exception('identity of user is not confirmed');
        }
    }
}