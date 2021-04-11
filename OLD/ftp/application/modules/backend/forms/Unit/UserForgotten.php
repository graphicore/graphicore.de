<?php
class Backend_Form_Unit_UserForgotten extends GC_DomForm_Subset
{
    protected $_elements = array();
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_elements = array(
            array(
                'name' => 'name',
                'type' => 'Text',
                'value' => '',
                'label' => $translate->_('username').': ',
            ),
            array(
                'name' => 'email',
                'type' => 'Text',
                'value' => '',
                'label' => $translate->_('email').': ',
            ),
        );
    }
}