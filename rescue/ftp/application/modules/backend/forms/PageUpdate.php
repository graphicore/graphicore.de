<?php
class Backend_Form_PageUpdate extends Backend_Form_Abstract
{
    protected $_name = 'Page';
    protected $_hasI18n = True;
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_action = $translate->_('Update');
        parent::init();
    }
}