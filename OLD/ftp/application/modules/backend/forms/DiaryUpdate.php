<?php
class Backend_Form_DiaryUpdate extends Backend_Form_Abstract
{
    protected $_name = 'Diary';
    protected $_hasI18n = True;
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_action = $translate->_('Update');
        parent::init();
    }
}