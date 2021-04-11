<?php
/*
 * some things all Modelcontrollers share
 * changelog
 * 2009/12/17
 *      added protected function _addFilterOptions() as a quick way to
 *          add a library of filters and validators used by an extending class
 */
abstract class GC_Modelctrl_Abstract
{
    protected $_defaultFilterOptions = array(
        Zend_Filter_Input::FILTER_NAMESPACE => 'GC_Filter',
        Zend_Filter_Input::VALIDATOR_NAMESPACE => 'GC_Validate'
    );
    protected $_filterOptions = array();//see setFilter()
    protected $_validators = array();
    protected $_filters = array();
    //a whitelist to save strings unescaped e.g. HTML fragments
    //used by getFilterData()
    protected $_unescaped = array();//see getFilterData()
    protected $_filter;
    protected $_rulesKey;
    public function __construct()
    {
        $this->init();
    }
    public function init()
    {}

    protected function _addFilterOptions(array $filterOptions)
    {
        foreach(array(Zend_Filter_Input::FILTER_NAMESPACE, Zend_Filter_Input::VALIDATOR_NAMESPACE) as $ns)
        {
            if(array_key_exists($ns, $filterOptions))
            {
                $filterOption = (is_string($filterOptions[$ns]))
                    ? array($filterOptions[$ns])
                    : $filterOptions[$ns];
                if(!is_array($filterOption))
                {
                    throw new GC_Modelctrl_Exception(sprintf(
                        '$filterOptions[%1$s] must be string or array, but its %2$s.',
                        $ns,
                        gettype($filterOption)
                    ));
                }
                if(!array_key_exists($ns, $this->_defaultFilterOptions))
                {
                    $this->_defaultFilterOptions[$ns] = array();
                }
                else if(!is_array($this->_defaultFilterOptions[$ns]))
                {
                    $this->_defaultFilterOptions[$ns] = array($this->_defaultFilterOptions[$ns]);
                }
                $this->_defaultFilterOptions[$ns] = array_merge(
                    $this->_defaultFilterOptions[$ns],
                    $filterOption);
            }
        }
    }

    //set filter by ruleskey
    public function setFilter($rulesKey)
    {
        if(!isset($this->_filters[$rulesKey]) || !isset($this->_validators[$rulesKey]))
        {
            throw new GC_Modelctrl_Exception('Rules for "'.$rulesKey.'" are missing');
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
            throw new GC_Modelctrl_Exception('There is no Filter, setFilter first');
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
            if(array_key_exists($key, $data))
            {
                $data[$key] = $filter->getUnescaped($key);
            }
        }
        return $data;
    }
    //might get overwritten
    public function getMessages()
    {
         return $this->getFilter()->getMessages();
    }
    //maybe a mapper is needed to map input to the format of $this->_filter->setData($input)
    public function isValid($input)
    {
        $this->getFilter()->setData($input);
        return $this->getFilter()->isValid();
    }
    public function validate($rulesKey, array $input)
    {
        $this->setFilter($rulesKey);
        $this->_filter->setData($input);
        return $this->_filter->isValid();
    }
}
