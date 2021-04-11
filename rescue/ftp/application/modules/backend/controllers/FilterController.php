<?php
class Backend_FilterController extends Formation_Controller_Action_I18n
{
    protected $_modelClass = 'Backend_Model_Filter';
    protected $_urlArray = array(
        'module' => 'backend',
        'controller' => 'filter',
        'action' => Null
    );
    protected $_form = array(
        'create' => 'Backend_Form_FilterCreate',
        'update' => 'Backend_Form_FilterUpdate',
        'delete' => 'Backend_Form_FilterDelete',
    );
    protected $_typeName = 'Filter';
    protected $_indexListItem = array(
        'fields' => array('name'),
        'format' => '%1$s'
    );
}