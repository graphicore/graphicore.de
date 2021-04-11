<?php
interface GC_DomForm_Interfaces_Multiplier
{
    public function addElement(array $description);
    public function restore();
    public function possibleVal($value);
    public function setMessages($messages);
}
//Conventions
//
//
//protected $_whitelist = array('value');
//protected $_silentGetterFail = array('DOM');
//protected function _setValue($value)
//protected function _getValue()
//see the description in GC_DomForm_Interfaces_Boolean