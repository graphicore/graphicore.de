<?php
//used for Story too
class Backend_Form_UserPassword extends Backend_Form_Confirm
{
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_legend = $translate->_('Generate a new Password and send it to the User.');
        parent::init();
    }
}