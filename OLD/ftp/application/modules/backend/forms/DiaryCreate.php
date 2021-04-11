<?php
class Backend_Form_DiaryCreate  extends Backend_Form_Abstract
{
    protected $_name = 'Diary';
    protected $_hasI18n = True;
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_action = $translate->_('Create');
        parent::init();
    }
}