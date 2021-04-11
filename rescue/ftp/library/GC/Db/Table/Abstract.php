<?php
abstract class GC_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
    const RULE_INSERT = 'insert';
    const RULE_UPDATE = 'update';
    //its recommendet to use a function called init() here, instead of __construct
    //init will be called from parent::construct, if i got that right
    /*
    public function __construct($config = Null)
    {
        parent::__construct($config);
        //$this->_setValidators();//is now integrated in isValid
    }
    //runs layzy in is valid...
    //this was used for testing in __construct
    protected function _setValidators(){
        foreach($this->_validation as $fieldname => $field){
            $this->_setValidator($fieldname);
        }
    }
    */
    //public function init()
    //{
    //}

    protected $_defaultFilterOptions = array(
        'filterNamespace' => 'GC_Filter',
        'validatorNamespace' => 'GC_Validate'
    );
    protected $_filterOptions = array();

    protected $_validators = array();
    protected $_filters = array();
    //a whitelist to save strings unescaped e.g. HTML fragments
    //used by getFilterData()
    protected $_unescaped = array();
    protected $_filter;
    protected $_rulesKey;
    //add a prefix to all tablefields if specified
    protected function _setupTableName()
    {

        parent::_setupTableName();
        //$config = Zend_Registry::getInstance()->config;
        //$config = Zend_Registry('configuration');
        //resources->db->table_prefix
        //$prefix = $config->database->table_prefix;
        //$application->getOption('db');
        //require_once 'Zend/Registry.php';

        self::makePrefix($this->_name);
    }
    static public function makePrefix(&$name)
    {
        $prefix = Zend_Registry::getInstance()->config->resources->db->table_prefix;
        if($prefix)
        {
            $name = $prefix . '_' . $name;
        }
    }
    //$input = new Zend_Filter_Input($this->_filters, $this->_validators,$newData,$this->_filterOptions);
    //OR
    //$input = new Zend_Filter_Input($this->_filters, $this->_validators);
    //$input
    //$input->setData($newData);
    public function setFilter($rulesKey)
    {
        if(!isset($this->_filters[$rulesKey]) || !isset($this->_validators[$rulesKey]))
        {
            throw new GC_Db_Table_Exception('Rules for "'.$rulesKey.'" are missing');
        }
        $this->_rulesKey = $rulesKey;
        $this->_filter = new Zend_Filter_Input(
            $this->_filters[$rulesKey],
            $this->_validators[$rulesKey]
        );

        $options = array_merge($this->_defaultFilterOptions, (array) $this->_filterOptions);
        $this->_filter->setOptions($options);
    }

    public function getFilter()
    {
        if(NULL === $this->_filter)
        {
            throw new GC_Db_Table_Exception('There is no Filter, setFilter first');
        }
        return $this->_filter;
    }

    public function getFilterData()
    {
        $data = array();
        $filter = $this->getFilter();
        $data = $filter->getEscaped();
        foreach($this->_unescaped as $key)
        {
            if(array_key_exists($key,$data))
            {
                $data[$key] = $filter->getUnescaped($key);
            }
        }
        if(method_exists($this , '_afterGetFilterData'))
        {
            $this->_afterGetFilterData($data);
        }
        return $data;
    }
    public function isValid($input)
    {
        $this->_filter->setData($input);
        return $this->_filter->isValid();
    }
    public function validate($rulesKey, array $input){
        $this->setFilter($rulesKey);
        $this->_filter->setData($input);
        return $this->_filter->isValid();
    }
    public function insertValidated(array $input,$rulesKey = self::RULE_INSERT)
    {
        $this->setFilter($rulesKey);
        if($this->isValid($input))
        {
            return $this->insert($this->getFilterData());
        }
        return false;
    }
    public function updateValidated(array $input, $where,$rulesKey = self::RULE_UPDATE)
    {
        $this->setFilter($rulesKey);
        if($this->isValid($input))
        {
            return $this->update($this->getFilterData(), $where);
        }
        return false;
    }

    public function getReferenceByRule($ruleKey){
        $thisClass = get_class($this);
        $refMap = $this->_getReferenceMapNormalized();
        if (!array_key_exists($ruleKey, $refMap))
        {
            require_once "GC/Db/Table/Exception.php";
            throw new GC_Db_Table_Exception("No reference rule \"$ruleKey\" from table $thisClass to table $tableClassname");
        }
        return $refMap[$ruleKey];
    }
/*
    $input->isValid()
    $input->isValid('month')
    if ($input->hasInvalid() || $input->hasMissing()) {
        $messages = $input->getMessages();
    }

    // getMessages() simply returns the merge of getInvalid() and
    // getMissing()

    if ($input->hasInvalid()) {
        $invalidFields = $input->getInvalid();
    }

    if ($input->hasMissing()) {
        $missingFields = $input->getMissing();
    }

    if ($input->hasUnknown()) {
        $unknownFields = $input->getUnknown();
    }

    $m = $input->month;                 // escaped output from magic accessor
    $m = $input->getEscaped('month');   // escaped output
    $m = $input->getUnescaped('month'); // not escaped
*/







        //add validation to the model
    /*
     * protected $_validation
     * validation rules for each field that shall be validated
     * array('fieldname' => array(
     *                      'required'      => [bool] //defaults to false
     *                      'validators' => rules array,
     *      )
     * rules array(
     * string 'ruleName' where namespace 'Zend_Validate_' will be prepended and arguments will be empty
     * OR array('validator => string 'className', ['namespace' => string defaults to 'Zend_Validate_'], ['options' => array defaults to array(), arguments for the validators constructor])
     *    //)
     * Working Example, not quite usable
     * protected $_validation = array(
     *    'name' => array(
     *        'required'      => true, //defaults to false
     *        'validators'    => array(
     *            'NotEmpty',
     *            'EmailAddress',
     *            array('validator' => 'StringLength', 'options' => array(2, 7)),
     *        ),
     *    ),
     * );
    */

}
?>