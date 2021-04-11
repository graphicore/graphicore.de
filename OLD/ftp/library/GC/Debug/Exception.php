<?php
class GC_Debug_Exception extends GC_Exception
{
    public function __construct()
    {
        $args = func_get_args();
        if(!empty($args))
        {
            $args[0] = GC_Debug::Dump($args[0]);
        }
        call_user_func_array(array($this, 'parent::__construct'), $args);
    }
}