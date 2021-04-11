<?php

class GC_Validate_Doctrine_RecordExists extends GC_Validate_Doctrine_RecordAbstract
{
    public function isValid($value)
    {
        $valid = true;
        $this->_setValue($value);

        $result = $this->_query($value);
        if (!$result)
        {
            $valid = false;
            $this->_error(self::ERROR_NO_RECORD_FOUND);
        }
        return $valid;
    }
}
