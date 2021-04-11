<?php
class Custom_Controller_Plugin_Navigation extends Zend_Controller_Plugin_Abstract
{
    protected $value = '';
    public function __construct()
    {
    }
    protected function _getNavigation()
    {
        $translate = GC_Translate::get();
        $menu = array(

            array(
                'internalId' => 'pages',
                'label' => $translate->_('About'),
                'controller' => 'index',
/*
            array(
                'internalId' => 'pages',
                'type'       => 'mvc',
                'label'      => $translate->_('Pages'),
                'liClass'    => 'Pages',
                'module'     => 'default',
                'controller' => 'index',
                'action'     => 'page',
                'route'    => 'i18n',
                'params'     => array
                (
                    'key'     => 'nowhere',
                ),
*/
                'pages' => array(
/*
                    array(
                        'type'       => 'mvc',
                        'label'      => $translate->_('About'),
                        //'liClass'    => 'tour',
                        'relation'        => 'me',
                        'module'     => 'default',
                        'controller' => 'index',
                        'action'     => 'page',
                        'params'     => array
                        (
                            'key'     => 'about',
                        ),
                        'route'    => 'i18n',
                    ),
*/
                    /*
                     * possibly a mission statement will become necessary
                    /*array(
                        'type'       => 'mvc',
                        'label'      => $translate->_('Mission'),
                        //'liClass'    => 'tour',
                        'module'     => 'default',
                        'controller' => 'index',
                        'action'     => 'page',
                        'params'     => array
                        (
                            'key'     => 'mission',
                        ),
                        'route'    => 'i18n',
                    ),
                    */
                    array(
                        'type'       => 'mvc',
                        'label'      => $translate->_('Lasse'),
                        //'liClass'    => 'tour',
                        'relation'        => 'me',
                        'module'     => 'default',
                        'controller' => 'index',
                        'action'     => 'page',
                        'params'     => array
                        (
                            'key'     => 'lasse',
                        ),
                        'route'    => 'i18n',
                    ),
                    /*
                    array(
                        'type'       => 'mvc',
                        'label'      => $translate->_('Anne'),
                        //'liClass'    => 'tour',
                        'relation'        => 'me',
                        'module'     => 'default',
                        'controller' => 'index',
                        'action'     => 'page',
                        'params'     => array
                        (
                            'key'     => 'anne',
                        ),
                        'route'    => 'i18n',
                    ),
                    array(
                        'type'       => 'mvc',
                        'label'      => $translate->_('Competences'),
                        //'liClass'    => 'tour',
                        'module'     => 'default',
                        'controller' => 'index',
                        'action'     => 'page',
                        'params'     => array
                        (
                            'key'     => 'competences',
                        ),
                        'route'    => 'i18n',
                    ),
                    */
                ),
            ),
            array(
                'internalId' => 'blog',
                'label'      => $translate->_('Filters'),
                'controller' => 'index',
                'pages' => array(
                    array(
                        'internalId' => 'blogAllLink',
                        'type'       => 'mvc',
                        'label'      => $translate->_('All'),
                        'title'      => $translate->_('list all articles'),
                        //'liClass'    => 'tour',
                        'module'     => 'default',
                        'controller' => 'index',
                        'action'     => 'diary',
                        'route'    => 'i18n',
                        /* this makes the link only active if no other params are set
                         * if params are -> filter => projects this is not active
                         */
                        'params'     => array('' => ''),
                    ),
                ),
            ),
        );
        return new Zend_Navigation($menu);
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        GC_I18n::setLang($request->getParam('lang'));
        $navi = $this->_getNavigation();
        Zend_Registry::getInstance()->Zend_Navigation = $navi;
        return True;
    }
}
