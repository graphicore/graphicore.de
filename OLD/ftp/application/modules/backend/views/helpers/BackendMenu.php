<?php
class Backend_View_Helper_BackendMenu extends Zend_View_Helper_Abstract
{
    public function BackendMenu(array $data)
    {
        $request = $data['request'];
        $urlArr = $data['urlArr'];
        $menu = array_key_exists('menu', $data) ? $data['menu'] : array();
        foreach($data['controllers'] as $link)
        {
            $controllerName = $link[0];
            $controller = $link[1];
            $route = (count($link) === 3) ? $link[2] : 'modules_i18n';

            $urlArr['action'] = 'index'; //they point to index by default
            //check if the resource is "allowed"
            if(is_array($controller))
            {
                $urlArr['action']= $controller[1];
                $controller = $controller[0];
            }

            //quick and dirty, an admin does not need these links
            //but there is no reason to block them via the acl, i think
/*
            if(Zend_Registry::getInstance()->userRole === 'admin'
            && $controllerUrlArr['module'] === 'backend'
            && $controller === 'user'
            && in_array($controllerUrlArr['action'], array('setup'))
            )
            {
                continue;
            }
*/
            if(!Zend_Registry::getInstance()->acl->check(
                    Zend_Registry::getInstance()->userRole,
                    $urlArr['module'].'_'.$controller,
                    $urlArr['action']
                ))
            {
                continue;
            }
            $menuAnchorFormat = ($request['module'] === $urlArr['module']
                && $request['controller'] === $controller
                && ('index' === $urlArr['action'] || $request['action'] === $urlArr['action']))
                ? $data['anchorFormatActive']
                : $data['anchorFormat'];

            $urlArr['controller'] = $controller;
            $menu[] = sprintf($menuAnchorFormat, $this->view->url($urlArr,$route ,True), $controllerName);
        }
        return $menu;
    }
}
