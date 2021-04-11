<?php
class Formation_MediaServer_Exception extends Formation_Exception
{
    public function __construct()
    {
        $args = func_get_args();
        if( !array_key_exists(1, $args) || empty($args[1]) )
        {
            //internal server error
            $args[1] = 500;
        }
        call_user_func_array(array($this,'parent::__construct'),$args);
    }
}
