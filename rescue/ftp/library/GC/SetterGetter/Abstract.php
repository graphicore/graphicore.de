<?php
//GC/SetterGetter/Abstract.php
abstract class GC_SetterGetter_Abstract
{
    protected $_whitelist = array();
    protected $_silentGetterFail = array();
    protected $_silentSetterFail = array();
    protected $_silentIssetFail = array();
    protected $_disableIsset = False;//the isset method was added later, so it's possible to disable it
    protected $_useWhitelist = true;
    protected $_get = '_get';
    protected $_set = '_set';
    protected $_isset = '_isset';
    public function __set($name, $value)
    {
        $method = $this->_getMethodName($name,$this->_set);
        $this->$method($value);
    }
    public function __get($name)
    {
        $method = $this->_getMethodName($name,$this->_get);
        if(!$method){
            return;
        }
        return $this->$method();
    }
    public function __isset($name)
    {
        if($this->_disableIsset)
        {
            //this is called if there is no $class->member
            //if this function would not exist isset($class->member) would return false
            return false;
        }
        $method = $this->_getMethodName($name,$this->_isset);
        if(!$method){
            return;
        }
        return $this->$method();
    }

    protected function _getMethodName($name,$methodPrefix){
        if ($this->_useWhitelist
            && !in_array($name, $this->_whitelist, true))
        {
            if( ($methodPrefix === $this->_get && in_array($name,$this->_silentGetterFail))
                ||($methodPrefix === $this->_set && in_array($name,$this->_silentSetterFail))
                ||($methodPrefix === $this->_set && in_array($name,$this->_silentIssetFail))
                )
            {
                return;
            }
            $msg = '"("'.$methodPrefix.'")'.$name.'" is not whitelisted, whitelisted are '.join(', ',$this->_whitelist);
            require_once 'GC/SetterGetter/Exception.php';
            throw new GC_SetterGetter_Exception(get_class($this).' Invalid content property. '.$msg);
        }

        if($methodPrefix !== $this->_get
            && $methodPrefix !== $this->_set
            && $methodPrefix !== $this->_isset)
        {
            $msg = '"'.$methodPrefix.'" not allowed';
            require_once 'GC/SetterGetter/Exception.php';
            throw new GC_SetterGetter_Exception(get_class($this).' Invalid content property. '.$msg);
        }

        $method = $methodPrefix . ucfirst($name);
        //this is case sensitive!
        if( FALSE === stripos(PHP_OS, 'win')
        &&  !in_array($method, get_class_methods($this), true) )
        {
            //return $method;
            //this works with linux but not with windows at the momement
            //windows does not find protected methods

            $msg = 'Method "'.$method.'" doesn\'t exists.' ;
            require_once 'GC/SetterGetter/Exception.php';
            throw new GC_SetterGetter_Exception(get_class($this).'::'.__FUNCTION__.': Invalid content property. '.$msg);
        }
        return $method;
    }
}
