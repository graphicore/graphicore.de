<?php
class GC_DomForm_Subset extends GC_DomForm_Mixer implements arrayaccess
{
    protected $_whitelist = array('namespace','elements','namespaceI18n');
    public function offsetSet($offset, $value) {
        $this->_elements[$offset] = $value;
    }
    public function offsetExists($offset) {
        return isset($this->_elements[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->_elements[$offset]);
    }
    public function offsetGet($offset) {
        $this->makeNamespace($offset);
        return isset($this->_elements[$offset]) ? $this->_elements[$offset] : null;
    }
    protected function _setElements(array $var)
    {
        $this->_elements = $var;
    }
    protected function _getElements()
    {
        $this->makeNamespaces();
        return $this->_elements;
    }
    public function setElements($var)
    {
        $this->elements = $var;
        return $this;
    }
    public function makeNamespaces()
    {
        //these types will not get a namespace here
        $forbiddenTypes = array();
        foreach (array_keys($this->_elements) as $key)
        {
            $this->makeNamespace($key);
        }
        return $this;
    }
    public function makeNamespace($key)
    {
        //these types will not get a namespace here
        $forbiddenTypes = array();
        if( !array_key_exists($key,$this->_elements)
            || !is_array($this->_elements[$key])
            //namespaces will not be overwritten
            || (array_key_exists('namespace',$this->_elements[$key])
                && is_string($this->_elements[$key]['namespace'])
                && !empty($this->_elements[$key]['namespace'])
                )
            //without a name the element needs no namespace
            || !array_key_exists('name',$this->_elements[$key])
            || !is_string($this->_elements[$key]['name'])
            //elements without type are no Form Elements
            || !array_key_exists('type',$this->_elements[$key])
            //types beginning with ! are no Form Elements
            || (is_string($this->_elements[$key]['type'])
                && $this->_elements[$key]['type'][0] === '!')
            //these types will not get a namespace
            || in_array($this->_elements[$key]['type'],$forbiddenTypes,True)
        )
        {
            return $this;
        }

        $this->_elements[$key]['namespace'] = $this->namespace;
        return $this;
    }
    /**
     * Sets values to the predefined elements
     * Not advanced yet... as can be seen with the forbidden types
     * Sets also the default Namesapce to all elements without namespace
     *
     *@param array $values array([string namespace => elements array]
     *@param string|Null $singleNs
     *@return void
     */
    public function setDefaults(array $values, $singleNs = NULL)
    {

        //this will not work with checkboxes and radios!
        //they'll all have the same name!
        $forbiddenTypes = array('Radios','Radio','Checkboxes','Checkbox');
        if($singleNs !== NULL)
        {
            $singleNs = (is_string($singleNs) && !empty($singleNs)) ? $singleNs : FALSE;
            if(!$singleNs)
            {
                throw new Exception('Wrong data for $singleNs: A namespace must be a not empty string'.$singleNs);
            }
            $values = (!array_key_exists($singleNs, $values)) ? array($singleNs => $values) : $values;
        }
        foreach($this->_elements as $key => $element)
        {
            if(!is_array($element))
            //we might want to replace some elements later so we don't throw an exception here
            {
                continue;
            }
            //types beginning with ! are no Form Elements
            if(array_key_exists('type',$element)
                && is_string($element['type'])
                && $element['type'][0] === '!')
            {
                continue;
            }
            //without a name the element needs no value
            if(!array_key_exists('name',$element) || !is_string($element['name']))
            {
                continue;
            }
            $elementNS =
                (array_key_exists('namespace',$element)
                && is_string($element['namespace'])
                && !empty($element['namespace']))
                    ? $element['namespace']
                    : $this->namespace;

            $this->_elements[$key]['namespace'] = $elementNS;
            if($singleNs && $singleNs !== $elementNS)
            {
                continue;
            }

            if(!array_key_exists($elementNS,$values)
                || !is_array($values[$elementNS])
                || !array_key_exists($element['name'],$values[$elementNS]))
            {
                continue;
            }
            //don't try to set one of these
            if(in_array($element['type'],$forbiddenTypes,True)){
                throw new Exception('don\'t set these types here: '.implode(', ',$forbiddenTypes));
            }
            if($element['type'] === 'Select')
            {
                $this->_elements[$key]['options'] = $values[$elementNS][$element['name']];
                continue;
            }
            $this->_elements[$key]['value'] = $values[$elementNS][$element['name']];
        }
        return $this;
    }
}