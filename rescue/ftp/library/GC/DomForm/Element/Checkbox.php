<?php
//will produce an Element <Input type="text"
require_once 'GC/DomForm/Element/AbstractInput.php';
require_once 'GC/DomForm/Interfaces/Boolean.php';
class GC_DomForm_Element_Checkbox extends GC_DomForm_Element_AbstractInput implements GC_DomForm_Interfaces_Boolean
{
    protected $_type = 'checkbox';
    protected $_checked = false;
    protected $_restoreChecked = false;
    protected $_isArray = false;//is no array by default, but used via GC_DomForm_Element_Checkboxes that will set it an array
    protected $_arrayKey = '';

    protected $_whitelist = array('value','checked','arrayKey');

    // empty string '' is like NULL but easier to check for foreign logic: NULL === $checkbox->arrayKey
    //      important: normally isset(NULL) would return false, but with this getter php seems not to check if the value is null but if the var is declared
    //          //isset($checkbox->arrayKey) will always return false
    protected function _getArrayKey()
    {
        if($this->_arrayKey === '')
        {
            return NULL;
        }
        return $this->_arrayKey;
    }
    protected function _setArrayKey($val)
    {
        $this->_arrayKey = (string) $val;
    }
    public function __construct(array $description, GC_DomForm $parent)
    {
        if(array_key_exists('isArray',$description))
        {
            $this->_isArray = $description['isArray'];
        }
        //a set arrayKey implies that this checkbox shall serve values as array
        if(array_key_exists('arrayKey',$description))
        {
            $this->arrayKey = $description['arrayKey'];
            $this->_isArray = true;
        }
        //here is no room for the browser to set a not existant value to something the browser wants
        if(!array_key_exists('value',$description) || NULL === $description['value'])
        {
            $description['value'] = '';
        }
        parent::__construct($description,$parent,$dropzone);
    }
    public function restore()
    {
        $this->checked = $this->_restoreChecked;
    }
    public function getName()
    {
            return parent::getName().$this->getArraySuffix();
    }
    public function getArraySuffix()
    {
        if($this->_isArray)
        {

            //if not set to another value $this->_arrayKey is '' and this will return ''
            return '['.$this->_arrayKey.']';
        }
        return '';
    }
    protected function _setChecked($value)
    {
        if($value)
        {
            $this->_checked = True;
            $this->DOMElement->setAttribute('checked','checked');
        }
        else
        {
            $this->_checked = False;
            $this->DOMElement->removeAttribute('checked');
        }
    }
    protected function _getChecked()
    {
        return $this->_checked;
    }
    public function possibleVal($value)
    {
        //not set is valid
        if(NULL === $value){
            return True;
        }
        return ($this->_getValue() === $value);
    }
}