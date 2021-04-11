<?php
//will produce an Element <Input type="text"
require_once 'GC/DomForm/Element/AbstractInput.php';
require_once 'GC/DomForm/Interfaces/Boolean.php';
class GC_DomForm_Element_Radio extends GC_DomForm_Element_AbstractInput implements GC_DomForm_Interfaces_Boolean
{
    protected $_type = 'radio';
    protected $_checked = false;
    protected $_restoreChecked = false;
    protected $_whitelist = array('value','checked');
    public function __construct(array $description, GC_DomForm $parent)
    {
    //here is no room for the browser to set a not existant value to something the browser wants
        if(!array_key_exists('value',$description) || NULL === $description['value'])
        {
            $description['value'] = '';
        }
        parent::__construct($description,$parent);
    }
    public function restore()
    {
        $this->checked = $this->_restoreChecked;
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
}