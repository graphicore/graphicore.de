<?php

class GC_Validate_Doctrine_AllRecordsExist extends GC_Validate_Doctrine_RecordsExistAbstract
{
    public function isValid($value)
    {
        $valid = true;
        $this->_setValue($value);
        $result = $this->_query($value);
        if (!$result)
        {
            $valid = false;
            $this->_error(self::ERROR_NOT_ALL_RECORDS_FOUND);
        }
        return $valid;
    }
}
