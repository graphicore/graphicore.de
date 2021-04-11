<?php
class Backend_View_Helper_EditingLanguageSwitch extends Zend_View_Helper_Abstract
{
    public function editingLanguageSwitch($editingLanguage, array $urlArray, $routeName, $editLangKey = 'editlang')
    {
        $langLinks = array();
        $allowedLocales = Zend_Registry::getInstance()->allowedLocales;
        foreach($allowedLocales as $lang)
        {
            if($editingLanguage === $lang)
            {
                continue;
            }
            $urlArray[$editLangKey] = $lang;
            $url = $this->view->url($urlArray, $routeName, True);
            $langLinks[] = '<li><a href="'.$url.'">'.GC_I18n::translateLang($lang).' ('.$lang.')</a></li>';
        }
        $translate = GC_Translate::get();
        if(count($langLinks) > 0)
        {
            return sprintf(
                $translate->_('<div class="editingLanguageSwitch">You are editing in <strong class="current-lang">%2$s (%1$s)</strong> change it to: <ul>%3$s</ul></div>'),
                $editingLanguage,
                GC_I18n::translateLang($editingLanguage),
                join(' ',$langLinks));
        }
        return sprintf(
            $translate->_('<div>editing Language is %1$s</div>'),
            $editingLanguage);
    }
}
