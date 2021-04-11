<?php
//used for Story too

class Backend_Form_Delete extends Backend_Form_Confirm
{
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_legend = $translate->_('Delete');
        parent::init();
    }
}