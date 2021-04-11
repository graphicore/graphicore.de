<?php
class Backend_Form_Unit_Page extends GC_DomForm_Subset
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
        );
    }
}