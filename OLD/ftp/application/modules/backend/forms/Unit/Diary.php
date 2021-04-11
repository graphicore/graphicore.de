<?php
class Backend_Form_Unit_Diary extends GC_DomForm_Subset
{
    protected $_elements = array();
    public function init()
    {
        $date = new Zend_Date();
        $date->setTimeZone(Zend_Registry::get('config')->timezone);
        $translate = GC_Translate::get();
        $this->_elements = array(
            array(
                'name' => 'urlId',
                'type' => 'Text',
                'value' => $date->get('Y-MM-dd').'_ insert an id here',/**/
                'label' => $translate->_('URL-Id').': ',
            ),
            array(
                'name' => 'Filters',
                'type' => 'Select',
                'multiple' => True,
                'label' => $translate->_('Filters'),
                'options' => array(
                    array($translate->_('there are no Filters')),
                ),
            ),
            array
            (
                'name' => 'timestamp',
                'type' => 'Text',
                'value' => $date->get('Y-MM-dd H:mm:ss'),//date('Y-m-d H:i:s'),//now
                'label' => $translate->_('this Articles Date (YYYY-MM-DD HH:MI:SS)')
                   .' '.$translate->_('the Timezone is').': '
                   .sprintf(
                   '%1$s, %2$s, UTC%3$s',
                    $date->get(Zend_Date::TIMEZONE),
                    $date->get(Zend_Date::TIMEZONE_NAME),
                    $date->get(Zend_Date::GMT_DIFF_SEP)),
            ),
        );
    }
}
