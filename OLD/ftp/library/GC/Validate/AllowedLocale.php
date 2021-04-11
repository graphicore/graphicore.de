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
class GC_Validate_AllowedLocale extends Zend_Validate_Abstract
{

    const NOT_ALLOWED = 'notAllowedLocale';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_ALLOWED => "'%value%' is no allowed Locale, allowed Locales are '%allowed%'"
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'allowed' => '_allowedString'
    );

    /**
     * Allowed values
     *
     * @var array
     */
    protected $_allowed;

    /**
     * Allowed values message string
     *
     * @var string
     */
    protected $_allowedString;

    /**
     * Sets validator options
     *
     * @param  mixed $allowed
     * @return void
     */
    public function __construct($allowed = NULL)
    {
        $this->setAllowed($allowed);
    }

    /**
     * Returns the allowed option
     *
     * @return Array
     */
    public function getAllowed()
    {
        return $this->_allowed;
    }

    /**
     * Sets the allowed option
     *
     * @param  array $allowed
     * @return GC_Validate_AllowedLocale Provides a fluent interface
     */
    public function setAllowed($allowed = NULL)
    {
        if(!isset($allowed)){
            $allowed = Zend_Registry::getInstance()->allowedLocales;
        }
        if(is_string($allowed)){
            $allowed = explode(',',$allowed);
        }
        if(!is_array($allowed) || empty($allowed)){
            throw new Exception('no locales are allowed');
        }

        $this->_allowed = $allowed;
        $this->_allowedString = implode(', ',$allowed);
        return $this;
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
        if (!in_array($value,$this->_allowed,true)) {
            $this->_error();
            return false;
        }
        return true;
    }

}
