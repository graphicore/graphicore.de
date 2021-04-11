<?php
// this is a not so strict input field!
// value can be set with _setValue
// value is only checked for type with possibleVal()
// it's meant for dynamic filling after wasSent of form was called
require_once 'GC/DomForm/Element/AbstractInput.php';
class GC_DomForm_Element_HiddenSlacky extends GC_DomForm_Element_AbstractInput
{
    protected $_type = 'hidden';
    public function possibleVal($value)
    {
        return (is_string($value) || Null === $value);
    }
    protected function _getMessageBox()
    {
        $this->_messageBox = $this->_parent->messageBox;
        return $this->_messageBox;
    }
}