<?php
class Common_View_Helper_HeaderSignUrl extends Zend_View_Helper_Abstract
{
    public function headerSignUrl()
    {
        //this might be subject of frequent change
        $request = Zend_Controller_Front::getInstance()->getRequest();
        return $request->getBaseUrl().'/images/box_content_'.GC_I18n::getLang().'.jpg';
    }
}
