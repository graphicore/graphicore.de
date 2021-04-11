<?php
class Backend_Form_Confirm extends GC_DomForm_Subset
{
    protected $_nsIsDefault = True;
    protected $_elements = array();
    protected $_legend = '';
    public $subject = '';
    protected function _preInit(){}
    public function init()
    {
        $this->_preInit();
        $this->_namespace = get_class($this);
        $this->subject = ($this->subject) ? $this->subject.' ' : '';
        $translate = GC_Translate::get();
        $this->_elements = array(
            array(
                'type' => '!dropZone',
                'tag' => 'fieldset',
            ),
            array(
                'type' => '!child',
                'tag' => 'legend',
                'text' => $this->subject.$this->_legend,
            ),
            array(
                'name' => 'confirm',
                'type' => 'Radios',
            ),
            array(
                'name' => 'confirm',
                'type' => 'Radio',
                'value' => 'False',
                'label' => $translate->_('No'),
                'checked' => true,
                'labelAfter' => true,
            ),
            array(
                'name' => 'confirm',
                'type' => 'Radio',
                'value' => 'True',
                'label' => $translate->_('Yes'),
                'labelAfter' => true,
            ),
            array
            (
                'name' => 'submit',
                'type' => 'Submit',
                'value' => $translate->_('Confirm'),
            ),
        );
    }
}