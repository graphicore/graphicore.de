<?php
Class GC_Appinfo_Basics extends GC_SetterGetter_Abstract
{
    protected $_get = 'get';
    protected $_set = 'set';
    protected $_whitelist = array('dispatcher');
    protected $_dispatcher;

    public function setDispatcher(Zend_Controller_Dispatcher_Interface $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
        return $this;
    }
    public function getDispatcher()
    {
        return $this->_dispatcher;
    }
}