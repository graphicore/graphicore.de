<?php
/**
 *
 * Changed by Lasse Fister, there is something left from the Original i think, License remains the New BSD License
 *
 * Imind Library
 *
 * @category   Imind
 * @package    Imind_Auth
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

/**
 * @see Zend_Auth_Result
 */
require_once 'Zend/Auth/Result.php';

/**
 * @category   Imind
 * @package    Imind_Auth
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class GC_Auth_Adapter_Doctrine implements Zend_Auth_Adapter_Interface
{
    /**
     * $_modelName - the class name of the doctrine Model
     * @var string
     */
    protected $_modelName = null;

    /**
     * $_identityColumn - the column to use as the identity
     *
     * @var string
     */
    protected $_identityColumn = null;

    /**
     * $_credentialColumns - columns to be used as the credentials
     *
     * @var string
     */
    protected $_credentialColumn = null;

    /**
     * $_identity - Identity value
     *
     * @var string
     */
    protected $_identity = null;

    /**
     * $_credential - Credential values
     *
     * @var string
     */
    protected $_credential = null;

    /**
     * $_credentialTreatment - Treatment applied to the credential, such as MD5() or PASSWORD()
     *
     * @var string
     */
    protected $_credentialTreatment = null;

    /**
     * $_status
     *
     * @var array
     */
    protected $_status = array(
        'code'     => Zend_Auth_Result::FAILURE,
        'identity' => Null,
        'messages' => array('Authentication failed.')
    );

    /**
     * $_authData - Result of database authentication query
     *
     * @var array
     */
    protected $_authData = null;

    /**
     * __construct() - Sets configuration options
     *
     * @param  string                   $modelName
     * @param  string                   $identityColumn
     * @param  string                   $credentialColumn
     * @param  string                   $credentialTreatment
     * @return void
     */
    public function __construct($modelName = null, $identityColumn = null,
                                $credentialColumn = null, $credentialTreatment = null)
    {
        if (null !== $modelName) {
            $this->setModelName($modelName);
        }

        if (null !== $identityColumn) {
            $this->setIdentityColumn($identityColumn);
        }

        if (null !== $credentialColumn) {
            $this->setCredentialColumn($credentialColumn);
        }

        if (null !== $credentialTreatment) {
            $this->setCredentialTreatment($credentialTreatment);
        }
    }

    /**
     * setModelName() - set the model of the Doctrine Model
     *
     * @param  string $modelName
     * @return GC_Auth_Adapter_Doctrine Provides a fluent interface
     */
    public function setModelName($modelName)
    {
        $this->_modelName = ucfirst($modelName);
        return $this;
    }

    /**
     * setIdentityColumn() - set the column name to be used as the identity column
     *
     * @param  string $identityColumn
     * @return GC_Auth_Adapter_Doctrine Provides a fluent interface
     */
    public function setIdentityColumn($identityColumn)
    {
        $this->_identityColumn = $identityColumn;
        return $this;
    }

    /**
     * setCredentialColumn() - set the column name to be used as the credential column
     *
     * @param  string $credentialColumn
     * @return GC_Auth_Adapter_Doctrine Provides a fluent interface
     */
    public function setCredentialColumn($credentialColumn)
    {
        $this->_credentialColumn = $credentialColumn;
        return $this;
    }

    /**
     * setCredentialTreatment() - allows the developer to pass a parameterized string that is
     * used to transform or treat the input credential data
     *
     * In many cases, passwords and other sensitive data are encrypted, hashed, encoded,
     * obscured, or otherwise treated through some function or algorithm. By specifying a
     * parameterized treatment string with this method, a developer may apply arbitrary SQL
     * upon input credential data.
     *
     * Examples:
     *
     *  'PASSWORD(?)'
     *  'MD5(?)'
     *
     * @param  string $treatment
     * @return GC_Auth_Adapter_Doctrine Provides a fluent interface
     */
    public function setCredentialTreatment($treatment)
    {
        $this->_credentialTreatment = $treatment;
        return $this;
    }

    /**
     * setIdentity() - set the value to be used as the identity
     *
     * @param  string $value
     * @return GC_Auth_Adapter_Doctrine Provides a fluent interface
     */
    public function setIdentity($value)
    {
        $this->_identity = $value;
        return $this;
    }

    /**
     * setCredential() - set the credential value to be used, optionally can specify a treatment
     * to be used, should be supplied in parameterized form, such as 'MD5(?)' or 'PASSWORD(?)'
     *
     * @param  string $credential
     * @return GC_Auth_Adapter_Doctrine Provides a fluent interface
     */
    public function setCredential($credential)
    {
        $this->_credential = $credential;
        return $this;
    }

    /**
     * getResultRowObject() - Returns the result row as a stdClass object
     *
     * @param  string|array $returnColumns
     * @param  string|array $omitColumns
     * @return stdClass|boolean
     */
    public function getAuthData()
    {
        if (!$this->_authData) {
            return false;
        }
        return $this->_authData;
    }

    /**
     * authenticate() - defined by Zend_Auth_Adapter_Interface.  This method is called to
     * attempt an authenication.  Previous to this call, this adapter would have already
     * been configured with all nessissary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @throws Zend_Auth_Adapter_Exception if answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $this->_authData = Null; //reset this every auth attempt
        try
        {
            $this->_setup();
        }
        catch(Zend_Auth_Adapter_Exception $e)
        {
            return $this->_makeResult();
        }

        $identities = $this->_getIdentities(); //a Doctrine_Collection
        $count = $identities->count();
        if(1 === $count)
        {
            $this->_status['code'] = Zend_Auth_Result::SUCCESS;
            $this->_status['messages'][] = 'Authentication successful.';
            $this->_authData = $identities->getFirst();
        }
        elseif ($count < 1)
        {
            $this->_status['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->_status['messages'][] = 'A record with the supplied identity could not be found.';
        }
        elseif ($count > 1)
        {
            $this->_status['code'] = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
            $this->_status['messages'][] = 'More than one record matches the supplied identity.';
        }
        return $this->_makeResult();
    }
    /**
     * _setup() - This method abstracts the steps involved with making sure
     * that this adapter was indeed setup properly with all required peices of information.
     *
     * Checks could be *more Intense* just checking if they are not empty strings does not make sure its setup properly
     *
     * @throws Zend_Auth_Adapter_Exception - in the event that setup was not done properly
     * @return true
     */
    protected function _setup()
    {
        $exception = null;

        if ((string) $this->_modelName == '')
        {
            $exception = 'A model class name must be supplied for the GC_Auth_Adapter_Doctrine authentication adapter.';
        }
        elseif ((string) $this->_identityColumn == '')
        {
            $exception = 'An identity column must be supplied for the GC_Auth_Adapter_Doctrine authentication adapter.';
        }
        elseif ((string) $this->_credentialColumn == '')
        {
            $exception = 'A credential column must be supplied for the GC_Auth_Adapter_Doctrine authentication adapter.';
        }
        elseif ($this->_identity == null)
        {
            $exception = 'A value for the identity was not provided prior to authentication with GC_Auth_Adapter_Doctrine.';
        }
        elseif ($this->_credential === null)
        {
            $exception = 'A credential value was not provided prior to authentication with GC_Auth_Adapter_Doctrine.';
        }

        if ($exception)
        {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception($exception);
        }
        $this->_status = array(
            'code'     => Zend_Auth_Result::FAILURE,
            'identity' => $this->_identity,
            'messages' => array()
            );
        return true;
    }
    /**
     * _getQuery() - This method creates a Doctrine_Query object that
     * is completely configured to be queried against the database.
     *
     * @return Doctrine_Query
     */
    protected function _getQuery()
    {
        // build credential expression
        if (empty($this->_credentialTreatment) || (strpos($this->_credentialTreatment, "?") === false))
        {
            $this->_credentialTreatment = '?';
        }
        $query = Doctrine_Query::create();
        $query->select('*')
            ->from($this->_modelName)
            ->where(
                sprintf('%1$s = ? AND %2$s = %3$s',
                    $this->_identityColumn,
                    $this->_credentialColumn,
                    $this->_credentialTreatment
                ),
                array($this->_identity, $this->_credential)
            );
        return $query;
    }

    /**
     * _getIdentities() - This method queries the database with the Doctrine_Query Objecr of _getQuery.
     *
     * @param Void
     * @throws Zend_Auth_Adapter_Exception - when a invalid select object is encoutered
     * @returns Doctrine_Collection
     */
    protected function _getIdentities()
    {
        try
        {
            $identities = $this->_getQuery()->execute();
        }
        catch (Exception $e)
        {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('The supplied parameters to GC_Auth_Adapter_Doctrine failed to '
                                                . 'produce a valid sql statement, please check table and column names '
                                                . 'for validity.');
        }
        return $identities;
    }
    /**
     * _makeResult() - This method creates a Zend_Auth_Result object
     * from the information that has been collected during the authenticate() attempt.
     *
     * @return Zend_Auth_Result
     */
    protected function _makeResult()
    {
        return new Zend_Auth_Result(
            $this->_status['code'],
            $this->_status['identity'],
            $this->_status['messages']
            );
    }
}