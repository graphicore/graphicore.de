<?php
class Common_View_Helper_Title extends Zend_View_Helper_Abstract
{
    public function title($data = '')
    {
        //$translate = GC_Translate::get();
        if(is_string($data))
        {
            $data = array($data);
        }
        if(!is_array($data))
        {
            $data = array();
        }
        array_unshift($data, Zend_Registry::getInstance()->config->system->title);
        return join(array_reverse($data) , ' | ');
    }
}
