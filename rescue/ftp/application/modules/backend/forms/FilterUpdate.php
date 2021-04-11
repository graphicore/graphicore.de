<?php
class Backend_Form_FilterUpdate extends Backend_Form_Abstract
{
    protected $_name = 'Filter';
    protected $_hasI18n = True;
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_action = $translate->_('Update');
        parent::init();
    }
}