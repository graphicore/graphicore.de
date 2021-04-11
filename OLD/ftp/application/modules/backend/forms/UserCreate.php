<?php
class Backend_Form_UserCreate extends Backend_Form_Abstract
{
    protected $_name = 'User';
    protected $_hasI18n = False;
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_action = $translate->_('Create');
        parent::init();
    }
}