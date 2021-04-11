<?php
class GC_Appinfo_Controller extends GC_Appinfo_Basics
{
    protected $_whitelist = array('dispatcher', 'name', 'module', 'file', 'class', 'actions', 'resource');
    protected $_silentSetterFail = array('module', 'actions');
    //if this is Acl_IndexController
    protected $_reflection;
    protected $_name;//Index
    protected $_file;//IndexController.php
    protected $_class;//Acl_IndexController
    protected $_module;
    protected $_resource;
    protected $_actions = array();

    public function __construct($name, $file, $class, GC_Appinfo_Module $module)
    {
        $this->_module = $module;
        $this->setName($name);
        $this->setFile($file);
        $this->setClass($class);
        $this->_resource = mb_strtolower($this->module->name.'_'.$this->name);
    }

    public function toArray()
    {
        $array = array();
        foreach(array('name', 'file', 'class', 'resource', 'actions') as $key)
        {
            $array[$key] = $this->$key;
        }
        return $array;
    }

    public function setName($name)
    {
        if($this->_name)
        {
            throw new GC_Appinfo_Exception('name is already set');
        }
        if(!is_string($name) || empty($name))
        {
            throw new GC_Appinfo_Exception('$name must be a not empty string');
        }
        $this->_name = $name;
        return $this;
    }

    public function setFile($file)
    {
        if($this->_file)
        {
            throw new GC_Appinfo_Exception('file is already set');
        }
        if(!is_file($this->module->path.'/'.$file))
        {
            throw new GC_Appinfo_Exception(sprintf('%1$s is no file in %2$s module', $file, $this->module->name));
        }
        $this->_file = $file;
    }

    public function setClass($class)
    {
        if($this->_class)
        {
            throw new GC_Appinfo_Exception('class is already set');
        }
        //dunno how to check here if $class is the right name
        if(!is_string($class) || empty($class))
        {
            throw new GC_Appinfo_Exception('$class must be a not empty string');
        }
        $this->_class = $class;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getModule()
    {
        return $this->_module;
    }

    public function getResource()
    {
        return $this->_resource;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function getClass()
    {
        return $this->_class;
    }

    public function getActions()
    {
        if(!empty($this->_actions))
        {
            return $this->_actions;
        }
        return $this->_getActions();
    }

    protected function _getActions()
    {
        if($this->_reflection === False)
        {
            return array();
        }
        else if($this->_reflection === Null)
        {
            $filename = realpath($this->module->path.'/'.$this->file);
            // damn why must i load those files
            // can't they just get parsed without execution?
            require_once($filename);
            $reflection = new Zend_Reflection_File($filename);
            try
            {
                $this->_reflection = $reflection->getClass($this->class);
            }
            catch(Zend_Reflection_Exception $e)
            {
                //the class was not found, so this is no controller
                throw new GC_Appinfo_Exception(
                    sprintf(
                        'class %1$s in file %2$s of module %3$s is missing. path:(%4$s)',
                        $this->class,
                        $this->file,
                        $this->module->name,
                        $filename
                    ));
                return;
            }
        }
        $methods = $this->_reflection->getMethods();
        $actionLength = strlen('Action');
        $dispatcher = $this->module->dispatcher;
        foreach($methods as $method)
        {
            //name must be %sAction
            $methodname = $method->getName();
            if('Action' !== substr($methodname, -$actionLength))
            {
                continue;
            }
            $actionName = substr($methodname, 0, -$actionLength);
            if($methodname !== $dispatcher->formatActionName($actionName))
            {
                continue;
            }
            $this->_actions[] = $actionName;
        }
        return $this->_actions;
    }

    public function getDispatcher()
    {
        return $this->module->dispatcher;
    }

    public function setDispatcher($dispatcher)
    {
        return $this->module->dispatcher = $dispatcher;
    }
}