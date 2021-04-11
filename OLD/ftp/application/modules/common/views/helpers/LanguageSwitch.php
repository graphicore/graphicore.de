<?php
class Common_View_Helper_LanguageSwitch extends Zend_View_Helper_Abstract
{
    protected $_helperUrl;
    public function url()
    {
        $passedArgs = func_get_args();
        if(!$this->_helperUrl)
        {
            $this->_helperUrl = new Zend_View_Helper_Url();
        }
        return call_user_func_array(array($this->_helperUrl, 'url'), $passedArgs);
    }


    public function languageSwitch($urlArray, $options = array())
    {
        if(!is_array($urlArray)){ $urlArray = array();}
        if(!array($options))
        {
            //backward compwhen $options used to be route
            $options = array('route' => $options);
        }
        else
        {
            //backward comp $route used to be 'i18n'
            if(!array_key_exists('route', $options))
            {
                $options['route'] = 'i18n';
            }
        }
        $route = $options['route'];

        $languageEntries = array();
        //the first has a class first
        $classes = array('first');

        $translate = GC_Translate::get();
        $oldTranslateLocale = $translate->getLocale();
        foreach(Zend_Registry::getInstance()->allowedLocales as $lang)
        {
            $translate->setLocale($lang);

            $urlArray['lang'] = $lang;
            $classes[] = $lang;
            if( $lang === GC_I18n::getLang() )
            {
                $classes[] = 'active';
            }
            //$displayLang = ('es' !== $lang)
            //    ? GC_I18n::notTranslateLang($lang)
            //    : 'Castellano';



            $languageEntries[] = sprintf(
                '<li title="%4$s"%1$s ><a hreflang="%5$s" rel="alternate" href="%2$s">%3$s</a></li>',
                count($classes) ? ' class="'.join(' ', $classes).'" ' : '',
                $this->url($urlArray, $route, False),
                GC_I18n::notTranslateLang($lang),//$displayLang,
                sprintf(
                    $translate->_('Display this page in %1$s. The interface is translated, but not all articles necessarily are.'),
                    GC_I18n::notTranslateLang($lang)
                ),
                $lang
            );

            //reset
            $classes = array();
        }
        $translate->setLocale($oldTranslateLocale);

        return sprintf('<ul class="language-switch"%2$s>%1$s</ul>', join($languageEntries) , array_key_exists('title', $options) ? ' title="'.$options['title'].'"' : '');
    }
}
