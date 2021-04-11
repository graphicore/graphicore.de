<?php
//stripped down easy version checking for a valid date string


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
 * @version    $Id: Date.php 13371 2008-12-19 11:41:09Z thomas $
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
class GC_Validate_DbDate extends Zend_Validate_Abstract
{
    /**
     * Validation failure message key for when the value does not follow the YYYY-MM-DD format
     */
    const NOT_YYYY_MM_DD = 'dateNotYYYY-MM-DD';

    /**
     * Validation failure message key for when the value does not appear to be a valid date
     */
    const INVALID        = 'dateInvalid';

    /**
     * Validation failure message key for when the value does not fit the given dateformat or locale
     */
    const FALSEFORMAT    = 'dateFalseFormat';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_YYYY_MM_DD => "'%value%' is not of the format YYYY-MM-DD",
        self::INVALID        => "'%value%' does not appear to be a valid date",
        self::FALSEFORMAT    => "'%value%' does not fit given date format"
    );

    /**
     * Optional format
     *
     * @var string|null
     */
    protected $_format = 'Y-m-d H:i:s';


    /**
     * Sets validator options
     *
     * @param  string             $format OPTIONAL
     * @param  string|Zend_Locale $locale OPTIONAL
     * @return void
     */
    public function __construct($format = 'Y-m-d H:i:s')
    {
        $this->setFormat($format);
    }

    /**
     * Returns the locale option
     *
     * @return string|null
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Sets the format option
     *
     * @param  string $format
     * @return Zend_Validate_Date provides a fluent interface
     */
    public function setFormat($format = 'Y-m-d H:i:s')
    {
        $this->_format = $format;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if $value is a valid date of the format YYYY-MM-DD
     * If optional $format or $locale is set the date format is checked
     * according to Zend_Date, see Zend_Date::isDate()
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        $valueString = (string) $value;

        $this->_setValue($valueString);
        $timestamp = strtotime($valueString);
        if(!$timestamp){
            $this->_error(self::INVALID);
            return false;
        }
        $newDate = date($this->getFormat(),$timestamp);
        if($newDate !== $valueString){
            $this->_error(self::FALSEFORMAT);
            return FALSE;
        }
        return true;
    }
}
