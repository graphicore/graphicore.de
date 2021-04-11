<?php
class Backend_Form_Unit_UserSetup extends GC_DomForm_Subset
{
    protected $_elements = array();
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_elements = array(
            array(
                'name' => 'password_old',
                'type' => 'Password',
                'value' => '',
                'label' => $translate->_('old password').': ',
            ),
            array(
                'name' => 'password',
                'type' => 'Password',
                'value' => '',
                'label' => $translate->_('new password').': ',
            ),
            array(
                'name' => 'password_confirm',
                'type' => 'Password',
                'value' => '',
                'label' => $translate->_('confirm the new password').': ',
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