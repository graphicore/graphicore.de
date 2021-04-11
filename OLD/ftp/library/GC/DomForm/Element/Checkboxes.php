<?php
require_once 'GC/SetterGetter/Abstract.php';
require_once 'GC/DomForm/Interfaces/Multiplier.php';
class GC_DomForm_Element_Checkboxes extends GC_SetterGetter_Abstract implements GC_DomForm_Interfaces_Multiplier
{
    const CheckboxType = 'Checkbox';
    const TypeKey = 'type';
    const IsArrayKey = 'isArray';

    protected $_parent;
    public $checkboxes = array();
    protected $_whitelist = array('value','messageBox');
    protected $_silentGetterFail = array('DOM');

    protected $_messageBox;
    protected $_messageBoxDrop;

    public function __construct(array $description, GC_DomForm $parent)
    {
        //not needed yet
        //$this->_dropZone = $dropzone;
        $this->_parent = $parent;
    }
    public function addElement(array $description)
    {
        if(array_key_exists(self::TypeKey,$description)
            &&  self::CheckboxType === $description[self::TypeKey])
        {
            $description[self::IsArrayKey] = True;
        }
        $element = $this->_parent->makeElement($description);
        if($element instanceof GC_DomForm_Element_Checkbox)
        {
            if(NULL === $element->arrayKey)
            {
                $this->checkboxes[] = $element;
            }
            else
            {
                $this->checkboxes[$element->arrayKey] = $element;
            }

        }
        else
        {
            // this class does only care about GC_DomForm_Element_Checkbox
            // others are useless here and not managed!
            require_once 'GC/DomForm/Element/Exception.php';
            throw new GC_DomForm_Element_Exception('elements must be instance of GC_DomForm_Element_Checkbox');
        }
        if(!$this->_messageBoxDrop)
        {
            $this->_messageBoxDrop = $element->DOM;
        }
    }
    public function restore()
    {
        foreach($this->checkboxes as $checkbox)
        {
            $checkbox->restore();
        }
    }
    protected function _setValue($values)
    {
        if($values === NULL)
        {
            $values = array();
        }
        if(!is_array($values))
        {
            $values = array((string) $values);
        }
        foreach($this->checkboxes as $key => $element)
        {
            $key = array_search($element->value, $values, True);
            //will uncheck all checkboxes where $checkbox->value !== $value
            if($element->checked = (False !== $key))
            {
                //one value in values can only check one box
                unset($values[$key]);
            }
        }
    }
    protected function _getValue()
    {
        $values = array();
        foreach($this->checkboxes as $element)
        {
            if($element->checked)
            {
                if(NULL === $element->arrayKey)
                {
                    $values[] = $element->value;
                }
                else
                {
                    $values[$element->arrayKey] = $element->value;
                }

            }
        }
        return empty($values) ? NULL : $values;
    }
    public function possibleVal($values){
        //not set is valid
        if(NULL === $values){
            return True;
        }

        //empty is not possible here, empty would mean values is NULL (not set at all)
        if(!is_array($values) || empty($values) || 0 === count($this->checkboxes))
        {
            return False;
        }
        foreach($this->checkboxes as $element)
        {
            $key = array_search($element->value, $values, True);
            //value was not found
            if($key === False)
            {
                continue;
            }
            //key is set in element
            //  this keyed element could have set this value
            if($key === $element->arrayKey){
                unset($values[$key]);
            }
            else if(is_numeric($key) && NULL === $element->arrayKey)
            {
                unset($values[$key]);
            }
            //all values could have been set by an element
            if(empty($values))
            {
                return True;
            }
        }
        //not all values could have been set by an element
        return False;
    }
    public function setMessages($messages)
    {
        $this->messageBox->setMessages($messages);
    }
    protected function _setMessageBox(){}
    protected function _getMessageBox()
    {
        if(!$this->_messageBox && $this->_messageBoxDrop)
        {
            require_once 'GC/DomForm/Element/MessageBox.php';
            $this->_messageBox = new GC_DomForm_Element_MessageBox($this);
            $this->_messageBoxDrop->parentNode->insertBefore($this->_messageBox->DOM, $this->_messageBoxDrop);
        }
        else
        {
            $this->_messageBox = $this->_parent->_messageBox;
        }
        return $this->_messageBox;
    }
}