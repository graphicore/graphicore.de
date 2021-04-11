<?php

class GC_Session_SaveHandler_DbTable extends Zend_Session_SaveHandler_DbTable
{
    //adding a prefix to tablename
    protected function _setupTableName()
    {
        parent::_setupTableName();
        GC_Db_Table_Abstract::makePrefix($this->_name);
    }
}