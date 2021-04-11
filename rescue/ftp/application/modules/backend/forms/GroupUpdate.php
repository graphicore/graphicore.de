<?php
class Backend_Form_GroupUpdate  extends Backend_Form_Abstract
{
    protected $_name = 'Group';
    protected $_hasI18n = False;
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_action = $translate->_('Update');
        parent::init();
    }
}
