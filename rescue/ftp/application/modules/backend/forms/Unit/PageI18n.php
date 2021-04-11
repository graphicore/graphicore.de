<?php
class Backend_Form_Unit_PageI18n extends GC_DomForm_Subset
{
    protected $_elements = array();
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_elements = array(
            array(
                'name' => 'published',
                'type' => 'Select',
                'value' => GC_DomForm::FALSEVAL,
                'hasBooleanOptions' => True,
                'options' => array(
                    array(GC_DomForm::FALSEVAL, $translate->_('not published')),
                    array(GC_DomForm::TRUEVAL,  $translate->_('published')),
                ),
                'size' => '1',
            ),
            array(
                'name' => 'title',
                'type' => 'Text',
                'value' => '',
                'label' => $translate->_('title like for the <title> tag').': ',
            ),
            array(
                'name' => 'htmlContent',
                'type' => 'Textarea',
                'class' => $this->getOption('wysiwyg') ? 'wysiwyg' : Null,
                'value' => '',
                'label' => $translate->_('html page content').': ',
            ),
        );
    }
}