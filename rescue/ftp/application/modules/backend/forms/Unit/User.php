<?php
class Backend_Form_Unit_User extends GC_DomForm_Subset
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
                'name' => 'password',
                'type' => 'Password',
                'value' => '',
                'label' => $translate->_('password').': ',
            ),
            array(
                'name' => 'password_confirm',
                'type' => 'Password',
                'value' => '',
                'label' => $translate->_('confirm the password').': ',
            ),
            array(
                'name' => 'email',
                'type' => 'Text',
                'value' => '',
                'label' => $translate->_('email').': ',
            ),
            array(
                'name' => 'dcGroupId',
                'type' => 'Select',
                'label' => $translate->_('select a group'),
                'options' => array(
                    array('', $translate->_('none')),
                ),
            ),
        );
    }
}