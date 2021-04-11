<?php
class Backend_Form_UserLogin extends GC_DomForm_Subset
{
    protected $_nsIsDefault = True;
    protected $_elements = array();
    public $recaptcha;
    public function init()
    {
        $this->_namespace = get_class($this);
        $translate = GC_Translate::get();
        $this->_elements = array(
            array(
                'type' => '!dropZone',
                'tag' => 'fieldset',
            ),
            array(
                'type' => '!child',
                'tag' => 'legend',
                'text' => $translate->_('Login'),
            ),
            array(
                'name' => 'name',
                'type' => 'Text',
                'value' => '',
                'label' => $translate->_('name').': ',
            ),
            array(
                'name' => 'password',
                'type' => 'Password',
                'value' => '',
                'label' => $translate->_('password').': ',
            ),
            'reCaptcha',
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'value' => $translate->_('Let me in'),
            ),
        );
        $this->recaptcha = new Backend_Form_Unit_ReCaptcha();
        $this->fill('reCaptcha', $this->recaptcha->elements);
    }
}
