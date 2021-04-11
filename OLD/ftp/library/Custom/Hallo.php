<?php

class Custom_Hallo
{
    public function __construct($say = '')
    {
        echo 'Hallo';
        if(!empty($say))
        {
            echo ' ';
            echo (string) $say;
        }
    }
}
