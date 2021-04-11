<?php
////this is a strict input field! value can be set only once and must be set
require_once 'GC/DomForm/Element/AbstractInput.php';
class GC_DomForm_Element_Hidden extends GC_DomForm_Element_AbstractInput
{

    protected $_type = 'hidden';

    public function restore(){}
    protected function _setValue($value)
    {
        //protect GC_DomForm_Interfaces_Boolean from loosing the value
        //experimanetal
        if($this->DOMElement->hasAttribute('value'))
        {
            require_once 'GC/DomForm/Element/Exception.php';
            throw new GC_DomForm_Element_Exception ('Value can be set only once! Since this is a strict hidden field');
        }
        $value = (string) $value;
        $this->DOMElement->setAttribute('value',$value);
    }
    public function possibleVal($value)
    {
        return ($value === $this->DOMElement->getAttribute('value'));
    }
    protected function _getMessageBox()
    {
        $this->_messageBox = $this->_parent->messageBox;
        return $this->_messageBox;
    }
}