<?php
class GC_DomForm_Mixer extends GC_SetterGetter_Abstract
{
    const DOMFORMCLASS = 'GC_DomForm';
    protected $_elements = array();
    protected $_namespace = GC_DomForm::DEFAULT_NS;
    protected $_namespaceI18n = Null;

    protected $_nsIsDefault = False;
    protected $_whitelist = array('namespace','namespaceI18n');

    protected $_options = array();

    public $formClass = GC_DomForm_Mixer::DOMFORMCLASS;
    public $form;
    public function __construct(array $options = Null)
    {
        if($options)
        {
            $this->_options = $options;
        }
        //just to check that the namespace is ok
        $this->_setNamespace($this->_namespace);
        $this->init();
    }
    public function init(){}
    protected function _setNamespace($var)
    {
        if(!is_string($var) || empty($var))
        {
            throw new GC_DomForm_Exception('namespace needs to be a not empty string');
        }
        $this->_namespace = $var;
    }
    protected function _getNamespace()
    {
        return $this->_namespace;
    }
    protected function _setNamespaceI18n($var)
    {
        if(Null !== $var || (!is_string($var) || empty($var)))
        {
            throw new GC_DomForm_Exception('namespaceI18n needs to be a not empty string or Null');
        }
        $this->_namespaceI18n = $var;
    }
    protected function _getNamespaceI18n()
    {
        return ($this->_namespaceI18n === Null)
            ? $this->_namespace.GC_DomForm::I18NNAMESPACESUFFIX
            : $this->_namespaceI18n
        ;
    }
    /* public function setNamespace($var)
    {
        $this->namespace = $var;
        return $this;
    } */

    /**
     * Takes multiple arrays of Elements and merges them into $this->elements
    */
    public function add()
    {
        $this->merge(func_get_args());
        return $this;
    }
    /**
     * Takes an array of arrays of Elements and merges them into $this->elements
    */
    public function merge(array $input)
    {
        array_unshift($input,$this->_elements);
        $this->_elements = call_user_func_array('array_merge',$input);
        return $this;
    }

    /**
     * remove element named $name
     *
     *
     * NOT FOOL PROOF, Test this before you use it
     * it could make probelems to remove some elements like
     * $forbiddenTypes = array('Radios','Radio','Checkboxes','Checkbox');
     */
    public function remove($name, $namespace)
    {
        //this will not work with checkboxes and radios!
        //they'll all have the same name!
        $forbiddenTypes = array('Radios','Radio','Checkboxes','Checkbox');
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
            //without a name the element can't be removed!
            if(!array_key_exists('name',$element) || !is_string($element['name']))
            {
                continue;
            }

            //don't try to delet one of these
            //or you could but should find out how, i did not try it yet
            //first approach should be to comment out the "return true;" after "unset($this->_elements[$key]);"
            if(in_array($element['type'],$forbiddenTypes,True))
            {
                throw new DomForm_Exception('don\'t delete these types here: '.implode(', ',$forbiddenTypes));
            }

            $elementNS =
                (array_key_exists('namespace',$element)
                && is_string($element['namespace'])
                && !empty($element['namespace']))
                    ? $element['namespace']
                    : $this->namespace;
            if($elementNS !== $namespace)
            {
                continue;
            }
            if($element['name'] === $name)
            {
                unset($this->_elements[$key]);
                return true;
            }
        }
        //not found
        return False;
    }
    /**
     * replace a string element $gap in $this->elements with $elements
     *
     *
     *
     */
    public function fill($gap, array $elements)
    {
        if(!is_string($gap))
        {
            throw new GC_DomForm_Exception('$gap must be string.');
        }
        $gaps = array_keys($this->_elements, $gap, True);
        while(False !== ($key = array_search($gap, $this->_elements,True)))
        {
            array_splice($this->_elements, $key, 1,$elements);
        }
        return $this;
    }
    public function clean()
    {
        foreach(array_keys($this->_elements) as $key)
        {
            if(!is_array($this->_elements[$key])){
                unset($this->_elements[$key]);
            }
        }
        $this->_elements = array_values($this->_elements);
        return $this;
    }
    public function build(){
        $this->clean();
        $this->form = new $this->formClass();
        if($this->_nsIsDefault){
            $this->form->namespace = $this->namespace;
        }
        $this->form->addElements($this->_elements);
        return $this;
    }
    public function getOption($key)
    {
        if(array_key_exists($key, $this->_options))
        {
            return $this->_options[$key];
        }
        return Null;
    }
 }
