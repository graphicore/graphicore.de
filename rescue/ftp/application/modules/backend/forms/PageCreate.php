<?php
class Backend_Form_PageCreate  extends Backend_Form_Abstract
{
    protected $_name = 'Page';
    protected $_hasI18n = True;
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_action = $translate->_('Create');
        parent::init();
    }
}