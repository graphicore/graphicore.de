<?php
abstract class GC_Db_Model_Mapper_Abstract
{
    protected $_dbTableClass;
    protected $_modelClass;
    protected $_dbTable;

    //deprecated
    private function _prepareModelSave($model)
    {
        throw new Exception('function _prepareModelSave is deprecated use _prepareModelInsert instead');
    }
    abstract protected function _prepareModelInsert($model);
    abstract protected function _prepareModelUpdate($model);

    protected function _checkModel($model)
    {
        if(!($model instanceof $this->_modelClass))
        {
            throw new Exception ('An instance of Class '.$this->_modelClass.' was expected. ('.gettype($model).') "'.get_class($model).'" was delivered.');
        }
    }

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable))
        {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract)
        {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable)
        {
            $this->setDbTable($this->_dbTableClass);
        }
        return $this->_dbTable;
    }
/*
    public function isValid(Default_Model_Content $content)
    {
        $data = array(
            //'email'   => $guestbook->getEmail(),
            'name' => $content->getName()
        );
        return $this->getDbTable()->isValid($data);
    }

    public function validationMsgs()
    {
        return $this->getDbTable()->validationMsgs;
    }
*/
    //if $action is "save" "_prepareModelSave()" must be defined
    protected function _prepareModel($action, $model)
    {
        $this->_checkModel($model);
        $action = '_prepareModel'.ucfirst($action);
        return $this->$action($model);
    }


    // CAUTION!: $rule is used like a convention here implying that the ruleskey
    // of the filter and the _prepareModel action are the same ('save' OR 'update')
    public function validate($rule, $model)
    {
        $blacklist = $this->_prepareModel($rule, $model);
        $data = $model->getOptions($blacklist);
        if($this->getDbTable()->validate($rule, $data))
        {
            $model->setOptions($this->getDbTable()->getFilterData());
            return true;
        }
        return false;
    }
    public function save($model)
    {
        $blacklist = $this->_prepareModel('insert',$model);
        $data = $model->getOptions($blacklist);
        $return = $this->getDbTable()->insertValidated($data);
        $model->setOptions($this->getDbTable()->getFilterData());
        if($return)
        {
            $model->setPkdata($return);
        }
        return $return;
    }
    public function update($model)
    {
        $blacklist = $this->_prepareModel('update',$model);
        $data = $model->getOptions($blacklist);
        $id = $data['id'];
        $return = $this->getDbTable()->updateValidated($data, array('id = ?' => $id));
        $model->setOptions($this->getDbTable()->getFilterData());
        return $return;
    }
    public function getMessages()
    {
        return $this->getDbTable()->getFilter()->getMessages();
    }
    public function find($id, $model)
    {
        $this->_checkModel($model);
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return false;
        }
        $row = $result->current();
        $model->setRow($row);
        return true;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        return $this->_makeEntries($resultSet);
    }
    public function findByEntry($column, $value, $model, $order = null)
    {
        $this->_checkModel($model);
        //prepare the where
        //where is an array('column condition ?' => 'value')
        $column = $this->getDbTable()
               ->select()
               ->getAdapter()
               ->quoteIdentifier($column);
        $where = array($column.' = ?' => $value);

        $row = $this->getDbTable()->fetchRow($where,$order);
        //throw new Exception(Zend_Debug::Dump($where));

        if (NULL === $row)
        {
            return false;
        }
        $model->setRow($row);
        return true;
    }
    //build an array from a resultSet
    protected function _makeEntries(Zend_Db_Table_Rowset $resultSet)
    {
        $entries   = array();
        foreach ($resultSet as $row)
        {
            $entry = new $this->_modelClass();
            $entry->setRow($row)
                  ->setMapper($this);
            $entries[] = $entry;
        }
        return $entries;
    }
}
