<?php
//will produce an Element <Input type="text"
require_once 'GC/DomForm/Element/Abstract.php';
class GC_DomForm_Element_Textarea extends GC_DomForm_Element_Abstract
{
    protected $_tag = 'textarea';
    protected $_restoreValue;
    protected $_value;
    //some attributes are required here, rows and cols for validation..
    protected $_attributes = array(
        'rows'=>'40','cols'=>'100'
    );
    //abstract protected $_type;
    protected function _makeElement(array $description)
    {
        $this->DOMElement = $this->_parent->DOMcreateElement($this->_tag);
        $this->_setValue($description['value']);
        $this->_restoreValue = $description['value'];
        $this->_attributes['name'] = $this->getName();

        if(array_key_exists('class', $description) && is_string($description['class']))
        {
            $this->_attributes['class'] = $description['class'];
        }
/*
        if( array_key_exists('setId', $description) && $description['setId'])
        {

            //[ and ] are not allowed in ids

            $this->_attributes['id'] = preg_replace('/[\[\]]/', '_', $this->_attributes['name']);
        }
*/
        $this->_setAttributes($this->_attributes);
    }
    public function restore()
    {
        $this->_setValue($this->_restoreValue);
    }
    protected function _eraseChildNodes()
    {
        while(NULL !== $this->DOMElement->firstChild)
        {
            $this->DOMElement->removeChild($this->DOMElement->firstChild);
        }
    }
    protected function _setValue($value)
    {
        $this->_value = $value;
        $value = (string) $this->_value;
        $this->_eraseChildNodes();
        $this->DOMElement->appendChild(new DOMText($value));
    }
    protected function _getValue()
    {
        $this->DOMElement->normalize();
        if($this->_value === NULL)
        {
            return;
        }
        return $this->DOMElement->firstChild->nodeValue;
    }
    public function possibleVal($value){
        return is_string($value);
    }
}