<?php
interface Formation_Login_Interfaces_User
{
    /*
     * returns an Zend_Auth_Adapter that means implementing Zend_Auth_Adapter_Interface
     */
    public static function getAuthAdapter();
    /*
     * returns a string with username or null
     */
    public function getIdentity();
    /*
     * returns a string with username or null
     * might change when acl gets changed
     */
    public function getUserRole();
    /*
     * returns the salted end hashed password
     */
    public function saltPassword($password, $salt);
}
