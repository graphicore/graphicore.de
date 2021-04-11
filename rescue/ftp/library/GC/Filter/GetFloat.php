<?php

/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';


class GC_Filter_GetFloat implements Zend_Filter_Interface
{
    protected $_locale = Null;
    protected $_returnDefault = False;
    protected $_default = 0.0;
    protected $_precision = 2;
    public function __construct($locale = False, $returnDefault = True, $default = 0)
    {
        if($locale instanceof Zend_Locale)
        {
            $this->_locale = $locale;
        }
        else if(is_string($locale))
        {
            $this->_locale = new Zend_Locale($locale);
        }
        else
        {
            $this->_locale = new Zend_Locale();
        }

        $this->_returnDefault = (bool) $returnDefault;
        if($this->_returnDefault)
        {
            $this->_default = floatval($default);
        }


    }
    protected function _getFloat(Zend_Locale $locale, $value)
    {
        try
        {
            $number = Zend_Locale_Format::getFloat( $value,
                array('precision' => $this->_precision,
                'locale' => $locale)
            );
        }
        catch(Zend_Locale_Exception $e)
        {
            return $value;
        }
        return floatval($number);
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns (int) $value
     *
     * @param  string $value
     * @return integer
     */
    public function filter($value)
    {
        if(is_float($value)){return $value;}
        $locales = ($this->_locale) ? array($this->_locale) : array();

        $locales[] = new Zend_Locale('en');//might run twice, that sucks

        foreach($locales as $locale)
        {
            $number = $this->_getFloat($locale, $value);
            if(is_float($number))
            {
                return $number;
            }
        }

        if(!$this->_returnDefault)
        {
            return $value;
        }
        return $this->_default;
    }
}
