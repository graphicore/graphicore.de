<?php
// application/models/Content.php

abstract class GC_Db_Model_Abstract
{
    protected $_mapperClass;
    protected $_mapper;
    protected $_keys = array();


    public function __construct(array $options = null)
    {
        if (NULL !== $options)
        {
            $this->setOptions($options);
        }
    }
    //in a normal environment where the table has one primary key
    //the save method of a modle returns just that
    //but if the table has more keys, an array might be returned
    abstract public function setPkdata($returnOfSave);
    public function setOptions(array $options = array(), $dontSet = array())
    {
        if(!is_array($dontSet)){
            $dontSet = array($dontSet);
        }
        foreach ($options as $key => $value)
        {
            if (!in_array($key,$this->_keys,True)
                || in_array($key, $dontSet,True))
            {
                continue;
            }
            $this->$key = $value;
        }
        return $this;
    }
    public function getOptions($dontGet = array())
    {
        if(!is_array($dontGet)){
            $dontGet = array($dontGet);
        }
        $options = array();
        foreach ($this->_keys as $key)
        {
            if(in_array($key, $dontGet,True))
            {
                continue;
            }
            $options[$key] = $this->$key;
        }
        return $options;
    }
    public function setRow($row)
    {
        foreach($this->_keys as $key)
        {
            if(isset($row[$key]))
            {
                $this->$key = $row->$key;
            }
        }
        return $this;
    }

    public function setMapper($mapper)
    {
        $this->_checkMapper($mapper);
        $this->_mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        if (null === $this->_mapper)
        {
            $this->setMapper(new $this->_mapperClass());
        }
        return $this->_mapper;
    }

    protected function _checkMapper($mapper)
    {
        if(!($mapper instanceof $this->_mapperClass))
        {
            throw new Exception ('An instance of Class '.$this->_mapperClass.' was expected. ('.gettype($mapper).') "'.get_class($mapper).'" was delivered.');
        }
    }

    public function validate($rule){
        return $this->getMapper()->validate($rule, $this);
    }
    //public function isValid()
    //{
    //    return $this->getMapper()->isValid($this);
    //}

    public function validationMsgs()
    {
        return $this->getMapper()->validationMsgs();
    }

    public function save()
    {
        return $this->getMapper()->save($this);
    }
    public function update()
    {
        return $this->getMapper()->update($this);
    }

    public function find($id)
    {
        if(!$this->getMapper()->find($id, $this))
        {
            return false;
        }
        return $this;
    }
    public function findByEntry($column, $value, $order = null){
        if (!in_array($column,$this->_keys,True)){
            throw new Exception ('$column is no key');
        }

        if (!is_string($value) || empty($value)){
            throw new Exception ('$value is expected to be a non empty string');
        }
        if(! $this->getMapper()->findByEntry($column, $value, $this, $order))
        {
            return false;
        }
        return $this;
    }
    public function fetchAll()
    {
        return $this->getMapper()->fetchAll();
    }
    public function getMessages()
    {
        return $this->getMapper()->getMessages();
    }
}
