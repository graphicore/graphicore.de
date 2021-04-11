<?php
class Backend_Form_UserForgotten extends GC_DomForm_Subset
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
                'text' => $translate->_('Request a new password.'),
            ),
            'user',
            'reCaptcha',
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'value' => $translate->_('Give me that.'),
            ),
        );

        $this->recaptcha = new Backend_Form_Unit_ReCaptcha();
        $this->fill('reCaptcha', $this->recaptcha->elements);

        $type = new Backend_Form_Unit_UserForgotten();
        $type->namespace = $this->namespace;
        $this->fill('user',$type->elements);
    }
}
