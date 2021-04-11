<?php
class Common_View_Helper_GetMainMenu extends Zend_View_Helper_Abstract
{
    public function getMainMenu()
    {
        $submenuFormat = '<span>%2$s</span>%1$s';
        $menus = array();
        foreach(array('blog', 'pages') as $menuId)
        {
            $menus[] = $this->view->getMenu($menuId, $submenuFormat);
        }
        return join('', $menus);

    }
}