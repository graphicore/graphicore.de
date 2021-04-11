<?php
//will produce an Element <Input type="text"
require_once 'GC/DomForm/Element/Abstract.php';
abstract class GC_DomForm_Element_AbstractInput extends GC_DomForm_Element_Abstract
{
    protected $_tag = 'input';
    //abstract protected $_type;
    protected $_restoreValue;
    protected function _makeElement(array $description)
    {
        $this->DOMElement = $this->_parent->DOMcreateElement($this->_tag);

        //stuff like value and type will be set later and overwrite bad attributes
        if(array_key_exists('attributes', $description) && is_array($description['attributes']))
        {
            $this->_setAttributes($description['attributes']);
        }

        $this->DOMElement->setAttribute('type',$this->_type);
        if($this instanceof GC_DomForm_Interfaces_Boolean)
        {
            if(array_key_exists('checked',$description))
            {
                $this->checked = $description['checked'];
                $this->_restoreChecked = $this->checked;
            }
        }
        else
        {
            $this->_restoreValue = $description['value'];
        }
        $this->_setValue($description['value']);
        $this->_setName();
    }
    public function restore()
    {
        $this->_setValue($this->_restoreValue);
    }
    protected function _setName()
    {
        $this->DOMElement->setAttribute('name',$this->getName());
    }
    protected function _setValue($value)
    {
        //protect GC_DomForm_Interfaces_Boolean from loosing the value
        //experimanetal
        if(($this instanceof GC_DomForm_Interfaces_Boolean)
            && $this->DOMElement->hasAttribute('value'))
        {
            require_once 'GC/DomForm/Element/Exception.php';
            throw new GC_DomForm_Element_Exception ('Value can be set only once! Since this is boolean field');
        }
        if($value === Null)
        {
            $this->DOMElement->removeAttribute('value');
            return;
        }
        $this->DOMElement->setAttribute('value',(string) $value);
    }
    protected function _getValue()
    {
        if(!$this->DOMElement->hasAttribute('value'))
        {
            return Null;
        }
        return $this->DOMElement->getAttribute('value');
    }
    public function possibleVal($value){
        return is_string($value);
    }
}
