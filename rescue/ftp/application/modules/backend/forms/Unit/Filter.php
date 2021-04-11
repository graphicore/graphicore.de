<?php
class Backend_Form_Unit_Filter extends GC_DomForm_Subset
{
    protected $_elements = array();
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_elements = array(
            array(
                'name' => 'urlId',
                'type' => 'Text',
                'value' => '',
                'label' => $translate->_('URL-Id').': ',
            ),
            array(
                'name' => 'weight',
                'type' => 'Text',
                'value' => '50',
                'label' => $translate->_('weight').': ',
            ),
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
        );
    }
}