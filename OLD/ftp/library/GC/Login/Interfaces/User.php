<?php
interface GC_Login_Interfaces_User
{
    /**
     * returns an Zend_Auth_Adapter that means implementing Zend_Auth_Adapter_Interface
     **/
    public function getAuthAdapter();
    /**
     * returns itself or false if user was not found
     **/
    public function getUser($username);
    /**
     * returns a string with username or null
     **/
    public function getIdentity();
    /**
     * returns a string with username or null
     * //might change when acl gets changed
     **/
    public function getUserRole();
    //returns an array with the usable part of the userdata, not salt and password, but role and username
    //not shure whats all needed here
    public function getUserData();
}
