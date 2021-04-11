<?php
class GC_Appinfo_Module extends GC_Appinfo_Basics
{
    protected $_whitelist = array('dispatcher', 'name', 'path', 'controllers');
    protected $_silentSetterFail = array('path', 'controllers');
    protected $_name;
    protected $_path;
    protected $_controllers = array();

    public function __construct($name, Zend_Controller_Dispatcher_Interface $dispatcher)
    {
        $this->setDispatcher($dispatcher)
             ->setName($name);
    }

    public function toArray()
    {
        $array = array();
        $keys = array('name', 'path');
        foreach($keys as $key)
        {
            $array[$key] = $this->$key;
        }
        foreach($this->controllers as $key => $controller)
        {
            $array['controllers'][$key] = $controller->toArray();
        }
        return $array;
    }

    public function getPath()
    {
        if(!$this->_path)
        {
            $this->_path = $this->getDispatcher()->getControllerDirectory($this->getName());
            if(!$this->_path)
            {
                throw new GC_Appinfo_Exception(sprintf('module %1$s has no path.',$this->getName()));
            }
            if(!is_dir($this->_path))
            {
                throw new GC_Appinfo_Exception(sprintf('path %1$s is no directory.', $this->_path));
            }
        }
        return $this->_path;
    }

    public function setName($name)
    {
        if($this->_name)
        {
            throw new GC_Appinfo_Exception('name is already set');
        }
        if(!$this->getDispatcher()->isValidModule($name))
        {
            throw new GC_Appinfo_Exception(sprintf('%1$s is not a valid module.',$name));
        }
        $this->_name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function filename2Classname($filename)
    {
        $class = pathinfo($filename, PATHINFO_FILENAME);
        if($this->name !== $this->dispatcher->getDefaultModule()
                || $this->dispatcher->getParam('prefixDefaultModule'))
        {
            $class = $this->dispatcher->formatClassName($this->name, $class);
        }
        return $class;
    }

    public function loadControllers()
    {
        $ctrlSuffix = 'Controller';
        $searchString = sprintf('%1$s/*%2$s.php',$this->path, $ctrlSuffix);
        foreach(glob($searchString) as $match)
        {
            if(is_dir($match) || !is_file($match))
            {
                continue;
            }
            $ctrl = new GC_Appinfo_Controller(
                substr(pathinfo($match, PATHINFO_FILENAME) , 0 ,-(strlen($ctrlSuffix))),
                pathinfo($match, PATHINFO_BASENAME),
                $this->filename2Classname($match),
                $this
            );
            $this->addController($ctrl);
        }
        return $this;
    }

    public function getControllers()
    {
        if(empty($this->_controllers))
        {
            $this->loadControllers();
        }
        return $this->_controllers;
    }

    public function addController(GC_Appinfo_Controller $controller)
    {
        $this->_controllers[$controller->name] = $controller;
    }

    public function getController($name)
    {
        if($this->hasController($name))
        {
            return $this->_controllers[$name];
        }
        return NULL;
    }

    public function hasController($name)
    {
        return (array_key_exists($name, $this->_controllers));
    }

    public function removeController($name)
    {
        if ($this->hasController($name))
        {
            unset ($this->_controllers[$name]);
        }
    }
}