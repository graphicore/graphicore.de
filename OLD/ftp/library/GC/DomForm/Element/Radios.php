<?php
require_once 'GC/SetterGetter/Abstract.php';
require_once 'GC/DomForm/Interfaces/Multiplier.php';
class GC_DomForm_Element_Radios extends GC_SetterGetter_Abstract implements GC_DomForm_Interfaces_Multiplier
{
    protected $_parent;
    //don't mess around with this list
    public $radios = array();
    protected $_whitelist = array('value','messageBox');
    protected $_silentGetterFail = array('DOM');

    protected $_messageBox;
    protected $_messageBoxDrop;
    //this is ONLY for $this->addElement()!!!
    protected $_defaultCheckedElement = False;
    public function __construct(array $description, GC_DomForm $parent)
    {
        $this->_parent = $parent;
        
        /*works best for now*/
        $this->_messageBoxDrop = $this->_parent
            ->dropZone
            ->appendChild(new DOMText(''));
    }
    public function addElement(array $description)
    {
        $element = $this->_parent->makeElement($description);
        if($element instanceof GC_DomForm_Element_Radio)
        {
            //the first radio should be checked by default
            if(False === $_defaultCheckedElement)
            {
                $element->checked = True;
                $this->_defaultCheckedElement = $element;
            }
            //if a latter is checked by default from the description array
            // uncheck the the last default checked element
            else if($element->checked)
            {
                $this->_defaultCheckedElement->checked = False;
                $this->_defaultCheckedElement = $element;
            }
            $this->radios[] = $element;
        }
        else
        {
            // this class does only care about GC_DomForm_Element_Radio
            // others are useless here and not managed!
            require_once 'GC/DomForm/Element/Exception.php';
            throw new GC_DomForm_Element_Exception('elements must be instance of GC_DomForm_Element_Radio');
        }
        
        /*
         *is never reached
         
        if(!$this->_messageBoxDrop)
        {
            $this->_messageBoxDrop = $element->DOM;
        }
        */
    }
    public function restore()
    {
        //uncheck all
        foreach($this->radios as $element)
        {
            $element->checked = False;
        }
        //check the last element addElement() found as checked
        if(FALSE !== $this->_defaultCheckedElement)
        {
            $this->_defaultCheckedElement->checked = True;
        }

    }
    public function possibleVal($value){
        if(NULL === $value)
        {
            if(FALSE === $this->_defaultCheckedElement)
            {
                return True;
            }
            //must be NULL because if at least one element was added we'd have a _defaultCheckedElement
            return False;
        }
        foreach($this->radios as $radio)
        {
            if($radio->value === $value)
            {
                //Found a radio that could have caused that value
                return True;
            }
        }
        //no radio had the value
        return False;
    }
    protected function _setValue($value)
    {
        $value = (string) $value;
        foreach($this->radios as $radio)
        {
            //will uncheck all radios where $radio->value !== $value
            $radio->checked = ($radio->value === $value);
        }
    }
    protected function _getValue()
    {
        $value = NULL;
        //the last checked is the value
        foreach($this->radios as $key => $radio)
        {
            if($radio->checked)
            {
                $value = $radio->value;
            }
        }
        return $value;
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
            $this->_messageBox = new GC_DomForm_Element_MessageBox($this->_parent);
            $this->_messageBoxDrop->parentNode->insertBefore($this->_messageBox->DOM, $this->_messageBoxDrop);
        }
        else
        {
            $this->_messageBox = $this->_parent->messageBox;
        }
        return $this->_messageBox;
    }
}
