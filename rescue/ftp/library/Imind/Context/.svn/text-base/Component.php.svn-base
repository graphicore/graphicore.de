<?php
 /**
 * 
 * Imind Library
 *
 * @category   Imind
 * @package    Imind_Context
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * @see Imind_Context_Component_Interface
 */
require_once 'Imind/Context/Component/Interface.php';

/**
 * @see Imind_Context
 */
require_once 'Imind/Context.php';

/**
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';

/**
 * @category   Imind
 * @package    Imind_Context
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class Imind_Context_Component implements Imind_Context_Component_Interface {

    /** @var string the component's id */
    protected $_id;
    
    /** @var string the component's classname */
    protected $_class;
    
    /** @var string the component's factory method */
    protected $_factory = "";
    
    /** @var boolean is the component default for a class */
    protected $_default = false;
    
    /** @var string the component's init method, that gets called first after creating an instance */
    protected $_init = "";
    
    /** @var boolean the object should be created on load or not */
    protected $_createOnLoad = false;
    
    /** @var array the component's constructor arguments */
    protected $_constructor=array();
    
    /** @var array the component's methods */
    protected $_methods=array();
    
    /** @var array the component's setters */ 
    protected $_setters=array();

    /**
     * Constructor
     *
     * @param string the component's id
     * @param string the component's classname
     * @param string the component's factory methodname
     * @param boolean is the component default for a class
     * @param string the component's init method, that gets called first after creating an instance
     * @return void
     */
    public function __construct($id,$class,$factory="",$default=false,$init="",$createOnLoad="no") {
        $this->_id=$id;
        if (is_array($class)) {
            if (isset($class["constructor"])) {
                $this->_constructor=$class["constructor"];
            }
            if (isset($class["methods"])) {
                $this->_methods=$class["methods"];
            }
            if (isset($class["setters"])) {
                $this->_setters=$class["setters"];
            }
            if (isset($class["class"])) {
                $this->setClass($class["class"]);
            }
            if (isset($class["factory"])) {
                $this->setFactory($class["factory"]);
            }
            if (isset($class["default"])) {
                $this->setDefault($class["default"]);
            }
            if (isset($class["init"])) {
                $this->setInit($class["init"]);
            }
            if (isset($class["createOnLoad"])) {
                $this->setCreateOnLoad($class["createOnLoad"]);
            }
        } elseif (is_string($class)) {
            $this->setClass($class);
            $this->setFactory($factory);
            $this->setDefault($default);
            $this->setInit($init);
            $this->setCreateOnLoad($createOnLoad);
        }
    }

    /**
     * Gets the component's id
     *
     * @return string
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * Returns the component's classname
     *
     * @return string
     */
    public function getClass() {
        return $this->_class;
    }

    /**
     * Returns if it's a default for a class
     *
     * @return boolean
     */
    public function getDefault() {
        return $this->_default;
    }

    /**
     * Sets the component's classname
     *
     * @param string
     * @return void
     */
    public function setClass($class) {
        $this->_class=$class;
        return $this;
    }

    /**
     * Sets the component's factory methodname
     *
     * @param string
     * @return void
     */
    public function setFactory($factory) {
        $this->_factory=$factory;
        return $this;
    }

    /**
     * Sets the component to be default for a class
     *
     * @param boolean
     * @return void
     */
    public function setDefault($default) {
        $this->_default=$this->_convertBoolean($default);
        if ($this->_default) {
            try {
                Imind_Context::setDefault($this->_id,$this->_class);
            } catch (Imind_Context_Exception $e) {
                $this->_default = false;
            }
        }
        return $this;
    }
    
    /**
     * The object should be created on load or not
     * 
     * @param booelean
     * @return void
     */
    public function setCreateOnLoad($createOnLoad) {
        $this->_createOnLoad = $this->_convertBoolean($createOnLoad);
        return $this;
    }
    
    /**
     * The object should be created on load or not
     * 
     * @return boolean
     */
    public function getCreateOnLoad() {
        return $this->_createOnLoad;
    }

    /**
     * Sets the component's init method (it gets called first after creating an instance)
     *
     * @param string
     * @return void
     */
    public function setInit($init) {
        $this->_init=$init;
        return $this;
    }

    /**
     * Creates an argument for the component
     *
     * @param string the value
     * @param string the argument's name
     * @return array the argument
     */
    public function createArgument($value,$name="") {
        return array("name"=>$name,"value"=>$value);
    }

    /**
     * Creates an array argument for the component
     *
     * @param array the value
     * @param string the argument's name
     * @return array the argument
     */
    public function createArrayArgument($array,$name="") {
        $arg=array("name"=>$name,"value"=>array());
        if (is_array($array)) {
            foreach ($array as $key=>$value) {
                $arg["value"][]=array("key"=>$key,"value"=>$value);
            }
        }
        return $arg;
    }

    /**
     * Creates a reference argument for the component
     *
     * @param string the reference's name
     * @return array the argument
     */
    public function createReferenceArgument($ref) {
        return array("ref"=>$ref);
    }

    /**
     * Adds a constructor argument to the array
     *
     * @param array the argument
     * @return Imind_Context_Component
     */
    public function addConstructorArgument($arg) {
        $this->_constructor[]=$arg;
        return $this;
    }

    /**
     * Adds a setter argument to the array
     *
     * @param array the argument
     * @return Imind_Context_Component
     */
    public function addSetterArgument($variableName,$arg) {
        $arg["name"]=$variableName;
        $this->_setters[]=$arg;
        return $this;
    }
    
    /**
     * Adds a method argument to the array
     *
     * @param array the argument
     * @return Imind_Context_Component
     */ 
    public function addMethodArgument($name,$arg) {
        $this->_methods[$name][]=$arg;
        return $this;
    }

    /**
     * Creates an object from the component
     *
     * @return mixed
     */
    public function createInstance() {
        Zend_Loader::loadClass($this->_class);
        $reflectionObj = new ReflectionClass($this->_class);
        
        $arguments=$this->_getArguments($this->_constructor);
        if ($this->_factory != "" && $reflectionObj->hasMethod($this->_factory)) {
            $instance = call_user_func_array(array($this->_class, $this->_factory), $arguments);
        } else {
            $constructor = $reflectionObj->getConstructor();
            if (!is_object($constructor) && count($arguments) > 0) {
                throw new Imind_Context_Component_Exception('No constructor defined for class: '.$this->_class.', but constructor arguments were given');
            } elseif (is_object($constructor)) {
                $instance = $reflectionObj->newInstanceArgs($arguments);
            } else {
                $instance = new $this->_class();
            }
        }
        foreach ($this->_setters as $setter) {
            if (isset($setter["name"])) {
                $setterName="set".strtoupper($setter["name"][0]).substr($setter["name"],1,strlen($setter["name"]));
                if ($reflectionObj->hasMethod($setterName)) {
                    $parameter=null;
                    if (isset($setter["value"])) {
                        if (is_array($setter["value"])) {
                            $parameter=$this->_getArrayArgument($setter["value"]);
                        } else {
                            $parameter=$setter["value"];
                        }
                    } elseif (isset($setter["ref"])) {
                        $parameter=Imind_Context::getObject($setter["ref"]);
                    }
                    call_user_func(array(&$instance,$setterName),$parameter);
                } else {
                    throw new Imind_Context_Component_Exception('No such setter as '.$setterName.' in class: '.$this->_class);
                }
            }
        }
        foreach ($this->_methods as $methodName=>$method) {
            if ($reflectionObj->hasMethod($methodName)) {
                $arguments=$this->_getArguments($method);
                call_user_func_array(array(&$instance,$methodName),$arguments);
            } else {
                throw new Imind_Context_Component_Exception('No such method as '.$methodName.' in class: '.$this->_class);
            }
        }
        if ($this->_init != "" && $reflectionObj->hasMethod($this->_init)) {
            call_user_func(array(&$instance,$this->_init));
        }
        return $instance;
    }

    /**
     * Return the component converted to array
     *
     * @return array
     */
    public function toArray() {
        $array=array("class"=>$this->_class);
        if ($this->_factory != "") {
            $array["factory"]=$this->_factory;
        }
        if ($this->_default != "no") {
            $array["default"]=$this->_default;
        }
        if ($this->_init != "") {
            $array["init"]=$this->_init;
        }
        if (count($this->_constructor) > 0) {
            $array["constructor"]=$this->_constructor;
        }
        if (count($this->_methods) > 0) {
            $array["methods"]=$this->_methods;
        }
        if (count($this->_setters) > 0) {
            $array["setters"]=$this->_setters;
        }
        return $array;
    }

    /**
     * Converts the arguments to real arguments to create an instance
     *
     * @param array the component's stored argument definition
     * @return array
     */
    protected function _getArguments($argumentsDef) {
        $arguments=array();
        if (is_array($argumentsDef)) {
            foreach ($argumentsDef as $argument) {
                if (isset($argument["value"])) {
                    if (is_array($argument["value"])) {
                        $arguments[]=$this->_getArrayArgument($argument["value"]);
                    } else {
                        $arguments[]=$this->_convertArgument($argument);
                    }
                } elseif (isset($argument["ref"])) {
                    $arguments[]=Imind_Context::getObject($argument["ref"]);
                }
            }
        }
        return $arguments;
    }

    /**
     * Converts the array arguments to real arguments to create an instance
     *
     * @param array the component's stored array argument definition
     * @return array
     */
    protected function _getArrayArgument($argument) {
        $array=array();
        foreach ($argument as $elem) {
            $array[$elem["key"]]=$this->_convertArgument($elem);
        }
        return $array;
    }
    
    /**
     * Convert the arguments value to a given type
     *
     * @param array the argument
     * @return mixed the converted value
     */
    protected function _convertArgument($argument) {
        if (is_array($argument["value"])) {
            $value = $this->_getArrayArgument($argument["value"]);
        } else {
            $value = $this->_convertProperties($argument["value"]);
            if (isset($argument["type"])) {
                switch ($argument["type"]) {
                    case "int":
                        $value = (int)$value;
                        break;
                    case "float":
                        $value = (float)$value;
                        break;
                    case "bool":
                        $value = $this->_convertBoolean($value);
                        break;
                    case "object":
                        $value = (object)$value;
                        break;
                    case "array":
                        $value = (array)$value;
                        break;
                    case "const":
                        $value = constant($value);
                        break;
                    case "null":
                        $value = null;
                        break;
                    default:
                        $value = (string)$value;
                    break;
                }
            }
        }
        return $value;
    }

    /**
     * Converts values with the context's properties to real values
     *
     * @param string a value
     * @return string
     */
    protected function _convertProperties($value) {
        $converted=$value;
        $properties = Imind_Context::getProperties();
        if (count($properties) > 0
            && preg_match("/\\$\\{([a-zA-Z0-9\.]+)\\}/",$value,$match)) {
            foreach ($properties as $property) {
                $result=$property->get($match[1]);
                if ($result !== false) {
                    $converted=str_replace($match[0],$result,$converted);
                }
            }
        }
        return $converted;
    }
    
    /**
     * Convert a string value to a boolean ("true","false","0","1")
     * @param string a value
     * @return boolean
     */
    protected function _convertBoolean($value) {
        if ($value === "true" || $value === "1") {
            $value = true;
        } elseif ($value === "false" || $value === "0") {
            $value = false;
        } else {
            $value = (bool)$value;
        }
        return $value;
    }
}
