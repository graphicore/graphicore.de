<?php
class Backend_PageController extends Formation_Controller_Action_I18n
{
    //this is very important now, and not solely for display, it conntects model, router, and forms etc
    protected $_typeName = 'Page';
    protected $_moduleName = 'Backend';

    protected $_metaFormats = array(
        array(
            'key' => '_modelClass',
            'val' => '%1$s_Model_%2$s',//Backend_Model_Page
        ),
        array(
            'key' => '_urlArray',
            'val' => array(
                array(
                    'key' => 'controller',
                    'val' => '%2$s',
                    'callbacks' => array(2 => 'mb_strtolower'),
                ),
                array(
                    'key' => 'module',
                    'val' => '%1$s',
                    'callbacks' => array(1 => 'mb_strtolower'),
                )
            )
        ),
        array(
            'key' => '_form',
            'val' => array(
                array(
                    'key' => 'create',
                    'val' => '%1$s_Form_%2$sCreate',
                ),
                array(
                    'key' => 'update',
                    'val' => '%1$s_Form_%2$sUpdate',
                ),
                array(
                    'key' => 'delete',
                    'val' => '%1$s_Form_%2$sDelete',
                )
            )
        )
    );

    protected $_modelClass = '';


    protected $_urlArray = array(
        //'module' => 'backend',
        //'controller' => 'static',
        'action' => Null
    );

    protected $_form = array(
        //'create' => 'Backend_Form_StaticCreate',
        //'update' => 'Backend_Form_StaticUpdate',
        //'delete' => 'Backend_Form_StaticDelete',
    );

    protected $_indexListItem = array(
        'fields' => array('urlId'),
        'format' => '%1$s'
    );
    public function init()
    {
        /*
         * sets some member-variables automated,
         * that would be useful with naming conventions and code generation
         * the naminging conventions i have...
         */
        $stack =  (is_array($this->_metaFormats)) ? $this->_metaFormats : array();
        while(count($stack))
        {
            $action = array_pop($stack);
            if(!array_key_exists('subject', $action))
            {
                $action['subject'] =& $this->$action['key'];
            }
            if(is_array($action['val']))
            {
                foreach($action['val'] as $child)
                {
                    $child['subject'] =& $action['subject'][$child['key']];
                    $stack[] = $child;
                }
                continue;
            }
            else if(is_string($action['val']))
            {
                $callbacks = (array_key_exists('callbacks', $action)) ? $action['callbacks'] : array();
                $action['subject'] = sprintf(
                    $action['val'],
                    array_key_exists(1, $callbacks)
                        ? call_user_func($callbacks[1], $this->_moduleName)
                        : $this->_moduleName,
                    array_key_exists(2, $callbacks)
                        ? call_user_func($callbacks[2], $this->_typeName)
                        : $this->_typeName
                );
            }
            else
            {
                throw new GC_Exception(sprintf(
                    'there is a wrong datatype (it\'s %1$s) for a "val" '
                    .'key in the _metaFormats array',
                    gettype($action['val'])
                ));
            }
        }
        return parent::init();
    }
    protected function _setFrontUrlArr($action, $dcModel)
    {
        switch($action)
        {
            case 'update':
            default:
                $frontArr = array(
                    'module' => 'default',
                    'controller' => 'index',
                    'action' => ''
                );
                $type = mb_strtolower($this->_typeName);
                $frontArr['action'] = $type;
                $frontArr['key'] = $dcModel['urlId'];
                $this->view->frontUrlArr = $frontArr;
                break;
        }
        return True;
    }
    protected function _setUpForm(GC_DomForm_Subset $form)
    {
        //$translate = GC_Translate::get();
    }
    protected function _setUpFormCreate(/* Backend_Form_PageCreate */$form)
    {
        if(!$form instanceof Backend_Form_PageCreate)
            throw new GC_Exception('Form must be a Backend_Form_PageCreate');
        $this->_setupForm($form);
    }
    protected function _setUpFormUpdate(/* Backend_Form_PageUpdate */$form)
    {
        if(!$form instanceof Backend_Form_PageUpdate)
            throw new GC_Exception('Form must be a Backend_Form_PageUpdate');
        $this->_setupForm($form);
    }
}

