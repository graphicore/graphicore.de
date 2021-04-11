<?php

class Custom_Image extends GC_Image
{
    public function init()
    {
        parent::init();
        $this->imageFolder = realpath(PUBLIC_PATH.'/images/managed/');
        $this->publicImageFolder = '/images/managed/';
    }
}
