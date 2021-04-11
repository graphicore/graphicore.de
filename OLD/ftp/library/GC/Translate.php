<?php
class GC_Translate
{
    public static function get()
    {
        try
        {
            return Zend_Registry::get('Zend_Translate');
        }
        catch(Zend_Exception $e)
        {
            return self::_set();
        }

    }
    protected static function _set()
    {
        $translate = new Zend_Translate(
            'gettext',
            APPLICATION_PATH.'/languages',
            null,
            array('scan' => Zend_Translate::LOCALE_DIRECTORY)
        );
        Zend_Registry::set('Zend_Translate', $translate);
        return $translate;
    }
}
