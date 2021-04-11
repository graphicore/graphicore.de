<?php
abstract class Backend_Form_Abstract extends GC_DomForm_Subset
{
    protected $_nsIsDefault = True;
    protected $_elements = array();

    protected $_hasI18n = False;
    protected $_action = '';
    protected $_name = '';
    protected $_unitPrefix = 'Backend_Form_Unit_';
    protected $_unitName = False;
    protected $_i18nSuffix = 'I18n';

    public function init()
    {
        $this->_namespace = get_class($this);
        $this->_elements = array(
            array(
                'type' => '!dropZone',
                'tag' => 'fieldset',
            ),
            array(
                'type' => '!child',
                'tag' => 'legend',
                'text' => $this->_action,
            ),
            'element',
        );
        if($this->_hasI18n)
        {
            $this->_elements[] =  'elementI18n';
        }
        $this->_elements[] = array(
            'name' => 'submit',
            'type' => 'Submit',
            'value' => $this->_action,
        );
        $class = sprintf('%1$s%2$s', $this->_unitPrefix, ($this->_unitName) ? $this->_unitName : $this->_name);
        $element = new $class($this->_options);
        $element->namespace = $this->namespace;
        $this->fill('element', $element->elements);
        if($this->_hasI18n)
        {

            $i18nClass = $class.$this->_i18nSuffix;
            $elementI18n = new $i18nClass($this->_options);
        /*
         * IMPORTANT! Recognize the namespace assignment hack!
         */
            $elementI18n->namespace = $this->namespaceI18n;
            $this->fill('elementI18n', $elementI18n->elements);
        }
    }
}