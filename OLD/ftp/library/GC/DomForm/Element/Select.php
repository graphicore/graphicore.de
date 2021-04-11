<?php
require_once 'GC/DomForm/Element/Abstract.php';
/**
 *
 * array(
 *             'name' => 'selectMultiple',
 *             'type' => 'Select',
 *             'label' => 'select many',
 *             'multiple' => true,//is here a multiple selection possible, defaults to False
 *             'options' => array(
 *                 array(1,'eins'),//string value,[string option]
 *                 array('zwei',2),
 *                 array(1,'one'),
 *                 array('drei'),
 *              ),
 *              'value' => array('drei','1')//[default selected values]
 *          ),
 *
 */
class GC_DomForm_Element_Select extends GC_DomForm_Element_Abstract
{
    protected $_tag = 'select';
    protected $_optionTag = 'option';
    protected $_optGroupTag = 'optgroup';
    protected $_hasBooleanOptions = False;
    protected $_insideOfOptgroup = False;
    protected $_BackupDOMElement;
    protected $_restoreValue;
    protected $_multiple = False;
    protected $_options = array();
    protected function _makeElement(array $description)
    {
        $this->DOMElement = $this->_parent->DOMcreateElement($this->_tag);
        if($this->_multiple = (array_key_exists('multiple',$description) && $description['multiple']))
        {
            $this->DOMElement->setAttribute('multiple','multiple');
        }
        if(array_key_exists('size',$description))
        {
            $this->DOMElement->setAttribute('size',$description['size']);
        }

        if(array_key_exists('hasBooleanOptions',$description) && $description['hasBooleanOptions'])
        {
            $this->_hasBooleanOptions = True;
        }

        $this->_setOptions($description['options']);
        $this->_setName();
        $this->_setValue($description['value']);
        $this->_restoreValue = $this->_getValue();
        if(!$this->_multiple && Null === $this->_restoreValue)
        {
            $this->_selectOption($this->_options[0], True);
            $this->_restoreValue = $this->_options[0]->getAttribute('value');
        }
    }
    protected function _setOptions(array $options)
    {
        foreach($options as $option)
        {
            $option = is_array($option) ? $option : array($option);
            if(array_key_exists('optgroup',$option))
            {
                $this->_makeOptgroup($option);
                continue;
            }
            $this->_makeOption($option);
        }
        if($this->_insideOfOptgroup)
        {
            $this->DOMElement = $this->_BackupDOMElement;
            $this->_insideOfOptgroup = False;
        }
    }
    protected function _makeOptgroup($option)
    {
        if(!$this->_BackupDOMElement)
        {
            $this->_BackupDOMElement = $this->DOMElement;
        }
        //reset
        if($this->_insideOfOptgroup)
        {
            $this->DOMElement = $this->_BackupDOMElement;
            $this->_insideOfOptgroup = False;
        }
        //just close the group, what already happend
        if($option['optgroup'] === False)
        {
            return;
        }
        $this->_insideOfOptgroup = True;
        $this->DOMElement = $this->_parent->DOMcreateElement($this->_optGroupTag);
        $this->DOMElement->setAttribute('label', (string) $option['optgroup']);
        $this->_BackupDOMElement->appendChild($this->DOMElement);
    }
    protected function _makeOption(array $option)
    {
        $newOption = $this->_parent->DOMcreateElement($this->_optionTag);
        //<option value="Wert">Eintrag</option>
        if(empty($option))
        {
            $option = array('');
        }
        $option[0] = (string)$option[0];
        $newOption->setAttribute('value',$option[0]);
        $info = isset($option[1]) ? $option[1] : $option[0];
        $newOption->appendChild(new DOMText($info));
        $this->DOMElement->appendChild($newOption);
        $this->_options[] = $newOption;
    }
    public function getName()
    {
        $array = $this->_multiple? '[]' : '';
        return parent::getName().$array;
    }
    protected function _setName()
    {
        $this->DOMElement->setAttribute('name', $this->getName());
    }
    protected function _setValue($value)
    {
        if( $this->_hasBooleanOptions && is_bool($value) )
        {
            $value = ($value) ? GC_DomForm::TRUEVAL : GC_DomForm::FALSEVAL;
        }

        if($this->_multiple && (!is_array($value) || empty($value)))
        //unselect all
        {
            $value = Array(False);
        }
        foreach($this->_options as $option)
        {
            $val = $option->getAttribute('value');
            $selected = $this->_multiple
                ? in_array($val,$value,True)
                : ($val === $value);
            if($selected && $this->_multiple)
            {
                //delete the maching element
                //  because if 2 or more elements have the same value
                //  but not all were selected we don't want to return
                //  all, only the selected amount
                //
                //  it isn't easy to determine which elements (with the same value) where selected
                //  a hint would be the order in the values array compared with the order of the options
                //  but this is complex and not really worth doing it and would not work totally reliable (if only one element was selected we can't know which one it was)

                //array_search will return a valid key because in_array($val,$value,True) made $selected True
                unset($value[array_search($val,$value,True)]);
            }
            $this->_selectOption($option, $selected);
        }
    }
    public function possibleVal($value)
    {
        if($this->_multiple)
        {
            if(Null === $value)
            {
                //with multiple selection its possible to select none
                return True;
            }
            //it must be an array
            //it wouldn't be empty, instead it would be Null!
            if(!is_array($value) || empty($value))
            {
                //if its multiple its an array
                return False;
            }
            foreach($this->_options as $option){
                $key = array_search($option->getAttribute('value'),$value,True);
                if(False === $key)
                {
                    continue;
                }
                unset($value[$key]);
                if(empty($value))
                {
                    return True;
                }
            }
            //not all values came from these options
            return False;
        }
        //$this->_multiple == False
        if(!is_string($value))
        {
            return False;
        }
        foreach($this->_options as $option)
        {
            if ($value === $option->getAttribute('value'))
            {
                //found one
                return True;
            }
        }
        //found none
        return False;
    }
    protected function _selectOption(DOMNode $option, $selected)
    {
        if($selected)
        {
            $option->setAttribute('selected','selected');
        }
        else
        {
            $option->removeAttribute('selected');
        }
    }

    protected function _getValue()
    {
        $value = $this->_multiple ? array() : NULL;
        foreach($this->_options as $option)
        {
            if($this->_multiple && $option->hasAttribute('selected'))
            {
                $value[] = $option->getAttribute('value');
            }
            else if($option->hasAttribute('selected'))
            {
                $value = $option->getAttribute('value');
            }
        }
        $value = (!is_string($value) && empty($value)) ? NULL : $value;
        return $value;
    }
    public function restore()
    {
        $this->_setValue($this->_restoreValue);
    }
}