<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: LessThan.php 8064 2008-02-16 10:58:39Z thomas $
 */


/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class GC_Validate_CountryCode extends Zend_Validate_Abstract
{

    const NOT_ALLOWED = 'noCountry';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_ALLOWED => "'%value%' is no allowed Countrycode"
    );
    protected static $_allowed = array();
    /**
     * Sets validator options
     *
     * @param  mixed $allowed
     * @return void
     */
    public function __construct()
    {
        if(empty(self::$_allowed))
        {
            self::$_allowed = array_keys(Zend_Locale::getTranslationList('Territory', Zend_Registry::getInstance()->Zend_Locale, 2));
        }
    }
    /**
     * Returns the allowed option
     *
     * @return Array
     */
    public function getAllowed()
    {
        return self::$_allowed;
    }
    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is less than max option
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue($value);
        $return = (in_array($value, self::$_allowed, True));
        if (!$return)
        {
            $this->_error();
        }
        return $return;
    }
}
