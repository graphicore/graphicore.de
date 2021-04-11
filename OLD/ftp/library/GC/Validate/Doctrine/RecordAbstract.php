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
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 17160 2009-07-26 19:46:24Z bittarman $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Class for Database record validation
 *
 * @category   Zend
 * @package    Zend_Validate
 * @uses       Zend_Validate_Abstract
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class GC_Validate_Doctrine_RecordAbstract extends Zend_Validate_Abstract
{
    /**
     * Error constants
     */
    const ERROR_NO_RECORD_FOUND = 'noRecordFound';
    const ERROR_RECORD_FOUND    = 'recordFound';

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(self::ERROR_NO_RECORD_FOUND => 'No record matching %value% was found',
                                         self::ERROR_RECORD_FOUND    => 'A record matching %value% was found');


    /**
     * @var string (new var should load an doctrine model)
     */
    protected $_doctrineModelString = '';
    protected $_useTranslation = false;
    /**
     * @var mixed
     */
    protected $_exclude = null;

    /**
     * Provides basic configuration for use with Zend_Validate_Db Validators
     * Setting $exclude allows a single record to be excluded from matching.
     * Exclude can either be a String containing a where clause, or an array with `field` and `value` keys
     * to define the where clause added to the sql.
     * A database adapter may optionally be supplied to avoid using the registered default adapter.
     *
     * @param string||array $table The database table to validate against, or array with table and schema keys
     * @param string $field The field to check for a match
     * @param string||array $exclude An optional where clause or field/value pair to exclude from the query
     * @param Zend_Db_Adapter_Abstract $adapter An optional database adapter to use.
     */
    public function __construct($doctrineModelString, $field, $useTranslation = false)
    {
        $this->_field   = (string) $field;
        $this->_doctrineModelString   = (string) $doctrineModelString;
        $this->_useTranslation = (bool) $useTranslation;
    }

    /**
     * Run query and returns matches, or null if no matches are found.
     *
     * @param  String $value
     * @return Array when matches are found.
     */
    protected function _query($value)
    {
        $tabel = Doctrine::getTable($this->_doctrineModelString);
        if(!$this->_useTranslation)
        {
            $finder = 'findOneBy'.ucFirst($this->_field);
            return $tabel->$finder($value);
        }

        return Doctrine_Query::create()
            ->from($this->_doctrineModelString.' x')
            ->leftJoin('x.Translation t')
            ->where('t.'.$this->_field.' = ?', $value)
            ->fetchOne();
    }
}
