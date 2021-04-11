<?php
class GC_Filter_Zero2Null implements Zend_Filter_Interface
{
    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns Null if Value is integer 0
     *
     * @param   $value
     * @return Null
     */
    public function filter($value)
    {
        $value = (0 === $value) ? NULL : $value;
        return $value;
    }
}
