<?php

class Backend_IndexController extends GC_Controller_Action
{
    public function indexAction()
    {
        $dump = array();
        $this->view->dump = $dump;
    }
}

