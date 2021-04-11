<?php
//this is a hack to make the easiest submit button possible
//it will never be checked
require_once 'GC/DomForm/Element/AbstractInput.php';
require_once 'GC/DomForm/Interfaces/Boolean.php';
class GC_DomForm_Element_Reset extends GC_DomForm_Element_AbstractInput implements GC_DomForm_Interfaces_Boolean
{
    protected $_type = 'reset';
    protected $_whitelist = array('value','checked');
    //this is a workaround to make this appear to be a boolean
    //no value will be set but checked if the value was submitted
    protected function _setChecked($value){}
    protected function _getChecked(){return False;}
    protected function _getValue(){return Null;}
    //don't set a name => no value will be submitted
    protected function _setName(){}
    public function restore(){}
    public function possibleVal($value){
        return (Null === $value);
    }
}