<?php
class Backend_Form_Unit_FilterI18n extends GC_DomForm_Subset
{
    protected $_elements = array();
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_elements = array(
            array(
                'name' => 'name',
                'type' => 'Text',
                'value' => '',
                'label' => $translate->_('Name').': ',
            ),
            array(
                'name' => 'description',
                'type' => 'Textarea',
                //'class' => $this->getOption('wysiwyg') ? 'wysiwyg' : Null,
                'value' => '',
                'label' => $translate->_('description').': ',
            ),
        );
    }
}