<?php
/**
 * Graphicore Extension for the Zend Framework. Same License of course
 * made by graphicore.de
 * http://framework.zend.com/license/new-bsd     New BSD License
 *
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
/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Class for Database record validation
 *
 */
abstract class GC_Validate_Db_CompoundKeyAbstract extends Zend_Validate_Abstract
{
    /**
     * Error constants
     */
    const ERROR_NO_RECORD_FOUND = 'noRecordFound';
    const ERROR_RECORD_FOUND    = 'recordFound';

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(self::ERROR_NO_RECORD_FOUND => 'No record where "%field1%" and "%field2%" matching %value% was found',
                                         self::ERROR_RECORD_FOUND    => 'A record where "%field1%" and "%field2%" matching %value% was found');
    /**
     * @var array
     */
    protected $_messageVariables = array(
        'field1' => '_field1',
        'field2' => '_field2',
    );

    /**
     * @var string
     */
    protected $_table = '';

    /**
     * @var string
     */
    protected $_field1 = '';
    protected $_field2 = '';
    /**
     * @var mixed
     */
    protected $_exclude = null;

    /**
     * Database adapter to use. If null isValid() will use Zend_Db::getInstance instead
     *
     * @var unknown_type
     */
    protected $_adapter = null;

    /**
     * Provides basic configuration for use with Zend_Validate_Db Validators
     * Setting $exclude allows a single record to be excluded from matching.
     * Exclude can either be a String containing a where clause, or an array with `field` and `value` keys
     * to define the where clause added to the sql.
     * A database adapter may optionally be supplied to avoid using the registered default adapter.
     *
     * @param string $table The database table to validate against
     * @param string $field The field to check for a match
     * @param string||array $exclude An optional where clause or field/value pair to exclude from the query
     * @param Zend_Db_Adapter_Abstract $adapter An optional database adapter to use.
     */
    public function __construct($table, $field1, $field2, $exclude = null, Zend_Db_Adapter_Abstract $adapter = null)
    {
        if ($adapter !== null) {
            $this->_adapter = $adapter;
        }
        $this->_exclude = $exclude;
        $this->_table   = (string) $table;
        $this->_field1   = (string) $field1;
        $this->_field2   = (string) $field2;
    }
        /**
     * Sets the value to be validated and clears the messages and errors arrays
     *
     * @param  mixed $value
     * @return void
     */
    protected function _setValue(array $value)
    {
        parent::_setValue('"'.$value[$this->_field1].'" and "'.$value[$this->_field2].'"');
    }
    /**
     * Run query and returns matches, or null if no matches are found.
     *
     * @param  String $value
     * @return Array when matches are found.
     */
    protected function _query(array $value)
    {
        /**
         * Check for an adapter being defined. if not, fetch the default adapter.
         */
        if($this->_adapter === null) {
            $this->_adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        }

        /**
         * Build select object
         */


        $select = new Zend_Db_Select($this->_adapter);
        $select->from($this->_table)
               ->columns(array($this->_field1,$this->_field2))
               ->where($this->_adapter->quoteIdentifier($this->_field1).' = ?', $value[$this->_field1])
               ->where($this->_adapter->quoteIdentifier($this->_field2).' = ?', $value[$this->_field2]);
        if ($this->_exclude !== null) {
            if (is_array($this->_exclude)) {
                $select->where($this->_adapter->quoteIdentifier($this->_exclude['field']).' != ?', $this->_exclude['value']);
            } else {
                $select->where($this->_exclude);
            }
        }
        $select->limit(1);

        /**
         * Run query
         */

        $result = $this->_adapter->fetchRow($select, array(), Zend_Db::FETCH_ASSOC);
        return $result;
    }
}
