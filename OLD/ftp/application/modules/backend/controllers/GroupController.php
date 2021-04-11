<?php
class Backend_GroupController extends Formation_Controller_Action_Simple
{
    protected $_modelClass = 'Backend_Model_Group';
    protected $_urlArray = array(
        'module' => 'backend',
        'controller' => 'group',
        'action' => Null
    );
    protected $_form = array(
        'create' => 'Backend_Form_GroupCreate',
        'update' => 'Backend_Form_GroupUpdate',
        'delete' => 'Backend_Form_GroupDelete',
    );
    protected $_typeName = 'Group';
    protected $_indexListItem = array(
        'fields' => array('name'),
        'format' => '%1$s'
    );
}