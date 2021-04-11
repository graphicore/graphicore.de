<?php
require_once 'Zend/Filter/Interface.php';

class GC_Filter_NoFilter implements Zend_Filter_Interface
{
    //public function __construct(){}


    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value without anything else
     * it can be set as array('escapeFilter' => 'NoFilter')
     * to prevent Zend_Filter_Input from escaping data with
     * htmlentities
     *
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        //if(!is_string($value)){
        //    require_once('GC/Filter/Exception.php');
        //    throw new GC_Filter_Exception('$value must be string');
        //}
        return (string)$value;
    }
}
