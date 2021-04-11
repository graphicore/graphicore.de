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
abstract class GC_Validate_Doctrine_RecordsExistAbstract extends Zend_Validate_Abstract
{
    /**
     * Error constants
     */
    const ERROR_NOT_ALL_RECORDS_FOUND = 'notAllRecordsFound';

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(
        self::ERROR_NOT_ALL_RECORDS_FOUND => 'Not all values (%value%) where found in "%column%" of %table%',
    );
    /**
     * @var array
     */
    protected $_messageVariables = array(
        'column' => '_column',
        'table' => '_table',
    );

    /**
     * @var string
     */
    protected $_table = '';

    /**
     * @var string
     */
    protected $_column = '';

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
    public function __construct($table, $column)
    {
        $table = (string) $table;
        $column = (string) $column;

        if(!Doctrine::isValidModelClass($table))
        {
            throw new GC_Validate_Exception('$table "'.htmlspecialchars($table).'" is no valid Doctrine Model Class');
        }

        $dcTable = Doctrine::getTable($table);
        if(!$dcTable->hasColumn($column))
        {
            throw new GC_Validate_Exception('Table "'.htmlspecialchars($table).'" has no Column "'.htmlspecialchars($column).'"');
        }

        $this->_table   = $table;
        $this->_column   = $column;
    }
    /**
     * Sets the value to be validated and clears the messages and errors arrays
     *
     * @param  mixed $value
     * @return void
     */
    protected function _setValue($value)
    {
        if(!is_array($value))
            throw new GC_Validate_Exception('value must be array.');
        array_map('strval', $value);
        parent::_setValue(join(', ',$value));
    }
    /**
     * Run query and returns matches, or null if no matches are found.
     *
     * @param  String $value
     * @return Array when matches are found.
     */
    protected function _query(array $value)
    {
        //are all
        //$this->_column $value in $this->_table

        /*
         * An IN conditional expression returns true if the operand is
         * found from result of the subquery or if its in the specificied
         * comma separated value list, hence the IN expression is always
         * false if the result of the subquery is empty.
         *
         */
        //throw new GC_Debug_Exception($value);
        $value = array_unique($value);
        $q = Doctrine_Query::create()
            ->select('x.'.$this->_column)
            ->from($this->_table.' x')
            ->whereIn('x.'.$this->_column, $value)
            ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
            ->execute()
            ;
        if(empty($q) || count($value) !== count($q))
        {
            return False;
        }
        return True;
    }
}
