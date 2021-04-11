<?php
class GC_Controller_Action extends Zend_Controller_Action
{
    public function __construct(
        Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = array())
    {
        $this->setLang($request->getParam('lang'));
        //call parent::construct with all arguments
        $args = func_get_args();
        if(version_compare(PHP_VERSION, '5.3.0') < 0)
        {
            call_user_func_array(array($this, 'parent::__construct'), $args);
        }
        else
        {
            call_user_func_array('parent::__construct', $args);
        }
    }
    public function setLang($lang)
    {
        //set the lang as Zend::Locale or use some Fallback
        GC_I18n::setLang($lang);
        //give all routers a lang variable
        Zend_Controller_Front::getInstance()
            ->getRouter()
            ->setGlobalParam('lang', Zend_Registry::getInstance()->Zend_Locale->toString());
    }
    public function getLang()
    {
        //get the lang of Zend::Locale or use some Fallback
        //Zend_Registry::getInstance()->Zend_Locale->toString();
        return GC_I18n::getLang();
    }
}
