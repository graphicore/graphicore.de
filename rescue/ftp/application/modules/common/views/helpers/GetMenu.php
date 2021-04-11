<?php
class Common_View_Helper_GetMenu extends Zend_View_Helper_Abstract
{
    public function getMenu($menuId, $submenuFormat = '%1$s %2$s')
    {
        $navi = Zend_Registry::getInstance()->Zend_Navigation;
        $container = $navi->findOneBy('internalId', $menuId);
        $submenu = $this->view->navigation()
        ->mainmenu()
        ->renderMenu($container,
            array(
                'indent'           => Null,
                'ulClass'          => 'menu '.$menuId,
                'minDepth'         => 0,
                'maxDepth'         => null,
                'onlyActiveBranch' => false,
                'renderParents'    => false
        ));
        return ($submenuFormat) ? sprintf($submenuFormat, $submenu ,$container->label) : $submenu;
    }
}