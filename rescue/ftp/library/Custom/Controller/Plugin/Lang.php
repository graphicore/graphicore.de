<?php
class Custom_Controller_Plugin_Lang extends Zend_Controller_Plugin_Abstract
{
    public function __construct()
    {}
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        GC_I18n::setLang($request->getParam('lang'));
        return True;
    }
}
