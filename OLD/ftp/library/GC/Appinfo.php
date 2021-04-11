<?php
class GC_Appinfo extends GC_Appinfo_Basics
{
    protected $_whitelist = array('dispatcher', 'modules');

    protected $_modules = array();

    public function __construct(Zend_Controller_Dispatcher_Interface $dispatcher)
    {
        $this->setDispatcher($dispatcher);
    }

    public function loadAll()
    {
        $dispatcher = $this->getDispatcher();
        $ctrlDirs = $dispatcher->getControllerDirectory();
        foreach(array_keys($ctrlDirs) as $module)
        {
            $this->addModule(new GC_Appinfo_Module($module, $dispatcher));
        }
        return $this;
    }
    public function getModules()
    {
        if(!$this->_modules)
        {
            $this->loadAll();
        }
        return $this->_modules;
    }
    public function addModule(GC_Appinfo_Module $module)
    {
        $this->_modules[$module->name] = $module;
    }

    public function getModule($name)
    {
        if($this->hasModule($name))
        {
            return $this->_modules[$name];
        }
        return NULL;
    }

    public function hasModule($name)
    {
        return (array_key_exists($name, $this->_modules));
    }

    public function removeModule($name)
    {
        if ($this->hasModule($name))
        {
            unset ($this->_modules[$name]);
        }
    }
    public function getResources()
    {
        $resources = array();
        foreach($this->modules as $module)
        {
            foreach($module->controllers as $controller){
                $resources[] = $controller;
            }
        }
        return $resources;
    }
}