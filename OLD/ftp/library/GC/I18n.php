<?php


// i want to use only some possible locales to keep the system maintainable
// i.E. if there is no chineese translation i don't want to allow the useage of a chineese locale...
// having a lot of locales means needing a lot of translation
class GC_I18n
{
    public static function bootstrap()
    {
        //there is no check if the allowed Locales are valid Locales here
        self::_setAllowedLocales();
        self::_setLang2Locale();
        if(!self::isLocale(Zend_Registry::getInstance()->config->locales->default)){
            require_once('GC/Exception.php');
            throw new GC_Exception('the default Locale "'.Zend_Registry::getInstance()->config->locales->default.'" is not in the list of allowed locales ('.Zend_Registry::getInstance()->config->locales->allowed.')');
        }
        Zend_Locale::setDefault(Zend_Registry::getInstance()->config->locales->default);
        //RTFM
        //
        // 31.1.8. Application wide locale
        //
        // Zend Framework allows the usage of an application wide locale. You
        // simply set an instance of Zend_Locale to the registry with the key
        // 'Zend_Locale'. Then this instance will be used within all locale
        // aware classes of Zend Framework. This way you set one locale within
        // your registry and then you can forget about setting it again. It will
        // automatically be used in all other classes. See the below example for
        // the right usage:
        Zend_Registry::getInstance()->Zend_Locale = new Zend_Locale();//will try to guess the right locale
        //self::setLang()
        //Zend_Registry::getInstance()->Zend_Locale->setLocale('de_GB');//for testing lets use something
        self::setLocale(Zend_Registry::getInstance()->Zend_Locale);//will set the locale to some allowed locale
    }
    //  This will yield in an allowed locale
    //  $locale = new Zend_Locale();
    //  GC_I18n::setLocale($locale);
    public static function setLocale(Zend_Locale $locale)
    {
        //if the locale is not allowed
        // the first allowed with the same language wille be used
        // then the default will be used
        if(!self::isLocale($locale))
        {
            //with setLocale on $registry->Zend_Locale the controller || router will be able to change the locale
            //of course they'll have to check if they are setting $allowedLocales
            //GC_I18n::setLocale does that check

            $lang = $locale->getLanguage();
            if(isset(Zend_Registry::getInstance()->lang2Locale[$lang]))
            {
                $locale->setLocale(Zend_Registry::getInstance()->lang2Locale[$lang]);
            }
            else
            {

                $locale->setLocale('default');
            }
        }

    }
    public static function setLang($lang)
    {
        if(!is_string($lang))
        {
            return;
        }
        Zend_Registry::getInstance()->Zend_Locale->setLocale($lang);
        self::setLocale(Zend_Registry::getInstance()->Zend_Locale);//will set the locale to some allowed locale
    }
    public static function getLang()
    {
        //Zend_Registry::getInstance()->Zend_Locale->toString();
        return Zend_Registry::getInstance()->Zend_Locale->toString();
    }

    public static function getDefaultLang()
    {
        //seems a little bit odd, FIXME when there is time or a need
        return key(Zend_Locale::getDefault());
    }


    public static function isLocale($locale)
    {
        $localestring = ($locale instanceof Zend_Locale) ? $locale->toString() : $locale;
        if(!is_string($localestring))
        {
            return False;
            //throw new GC_Exception(sprintf('$locale must be of type Zend_Locale or string but is "%s"', gettype($localestring)));
        }
        return in_array($localestring, Zend_Registry::getInstance()->allowedLocales, True);
    }

    protected static function _setAllowedLocales()
    {
        //there is no check if the allowed Locales are valid Locales here
        $locales = explode(',',trim(Zend_Registry::getInstance()->config->locales->allowed));
        sort($locales);
        Zend_Registry::getInstance()->allowedLocales = $locales;
    }
    /**
     * return a list of available locales
     * array('locale_CODE',tranlated name of the Language in the language of the default locale)
     *
     *
     */

    public static function localeOptions()
    {
        $localeOptions = array();
        foreach(Zend_Registry::getInstance()->allowedLocales as $locale)
        {
            $localeOption = array($locale,Zend_Locale::getLanguageTranslation($locale,Zend_Registry::getInstance()->Zend_Locale));
            if(!$localeOption[1])
            {
                $lang = explode('_',$locale);
                $localeOption[1] = Zend_Locale::getLanguageTranslation($lang[0],Zend_Registry::getInstance()->Zend_Locale);
            }
            if(!$localeOption[1])
            {
                unset($localeOption[0]);
            }
            $localeOptions[] = $localeOption;
        }
        return $localeOptions;
    }
    public static function translateLang($locale)
    {
        $trans = Zend_Locale::getTranslation($locale, 'language', Zend_Registry::getInstance()->Zend_Locale);
        if(!$trans){
            $lang = explode('_',$locale);
            $trans = Zend_Locale::getTranslation($lang, 'language', Zend_Registry::getInstance()->Zend_Locale);
        }
        return $trans;
    }
    public static function notTranslateLang($locale)
    {
        $trans = Zend_Locale::getTranslation($locale, 'language',$locale);
        if(!$trans){
            $lang = explode('_',$locale);

            $trans = Zend_Locale::getTranslation($lang, 'language',$lang);
        }
        return $trans;
    }
    protected static function _setLang2Locale()
    {
        $lang2Locale = array();
        foreach(Zend_Registry::getInstance()->allowedLocales as $allowedLocale)
        {
            $lang = explode('_',$allowedLocale);
            $lang = $lang[0];
            //first come first serve
            if(isset($lang2Locale[$lang])){continue;}
            $lang2Locale[$lang] = $allowedLocale;
        }
        Zend_Registry::getInstance()->lang2Locale = $lang2Locale;
    }
}
