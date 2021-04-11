<?php
//will produce an Element <Input type="text"

//      prevents from sending a password back
//      its stupid to send a password back to the user
//      password should not be stored in the DOM elemtent
//      that way it must be reenterd everytime but the user could save the password in her browser
require_once 'GC/DomForm/Element/AbstractInput.php';
class GC_DomForm_Element_Password extends GC_DomForm_Element_AbstractInput
{
    protected $_type = 'password';
    protected $_value = '';
    protected function _setValue($value)
    {
        $this->_value = ($value === NULL)? NULL : (string) $value;
    }
    protected function _getValue()
    {
        return $this->_value;
    }
    public function restore()
    {
        $this->_setValue('');
    }
}