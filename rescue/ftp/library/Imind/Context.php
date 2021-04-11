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
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';

/**
 * @see Imind_Context_Exception
 */
require_once 'Imind/Context/Exception.php';

/**
 * @see Imind_Context_Component
 */
require_once 'Imind/Context/Component.php';

/**
 * @category   Imind
 * @package    Imind_Context
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class Imind_Context {

    /** @var bool is context already loaded */
    protected static $_loaded = false;

    /** @var array the components loaded */
    protected static $_components = array();

    /** @var array the components in loading, to prevent a circle */
    protected static $_componentsLoading = array();

    /** @var array the properties components, that can import variables */
    protected static $_properties = array();

    /** @var mixed the registry implementation (array, Zend_Registry, ...) */
    protected static $_registry = array();

    /** @var array the default objects for classes */
    protected static $_defaults = array();

    /**
     * Load the context from a loader, Zend_Config or array
     *
     * @param  mixed the data (Imind_Context_Loader, Zend_Config, array)
     * @return void
     */  
    public static function load($data) {
        if ($data instanceof Imind_Context_Loader) {
            $data = $data->get();
        } elseif (!is_array($data)) {
            throw new Imind_Context_Exception("Unsupported data type");
        }
        if (isset($data["registry"])) {
            self::setRegistry($data["registry"]);
        }
        if (isset($data["property"])) {
            self::addProperties($data["property"]);
        }
        foreach ($data["components"] as $componentId=>$componentDef) {
            self::$_components[$componentId] = new Imind_Context_Component($componentId,$componentDef);
            if (self::$_components[$componentId]->getCreateOnLoad()) {
                self::getObject($componentId);
            }
        }
        if (isset($data["aliases"])) {
            foreach ($data["aliases"] as $newId => $oldId) {
                self::setAlias($oldId,$newId);
            }
        }
        self::$_loaded=true;
    }

    /**
     * Sets the registry's implementation
     *
     * @param  string the implemetation's classname ("Zend_Registry")
     * @return void
     */  
    public static function setRegistry($registryClassName) {
        if (count(self::$_registry) == 0) {
            Zend_Loader::loadClass($registryClassName);
            self::$_registry = new $registryClassName();
        } else {
            throw new Imind_Context_Exception("Can't set registry, already has data");
        }
    }

    /**
     * Returns the registry's implementation
     *
     * @return mixed the registry
     */  
    public static function getRegistry() {
        return self::$_registry;
    }

    /**
     * Sets the properties for the context
     *
     * @param  mixed the property components' names separated with comma
     *               or an array of the components' names
     * @return void
     */
    public static function addProperties($properties) {
        if (is_string($properties)) {
            $properties = split(",",$properties);
        } elseif (!is_array($properties)) {
            throw new Imind_Context_Exception("Wrong type for properties");
        }
        for ($i=0; $i < count($properties); ++$i) {
            self::$_properties[] = self::getObject($properties[$i]);
        }
    }

    /**
     * Returns the properties components
     *
     * @return array array of the components
     */
    public static function getProperties() {
        return self::$_properties;
    }

    /**
     * Sets an alias for a component
     *
     * @param string the old component's name
     * @param string the alias name for the component
     * @return void
     */
    public static function setAlias($oldId,$newId) {
        if (!isset(self::$_components[$oldId])) {
            throw new Imind_Context_Exception("Can't create alias $newId, component with id: $oldId doesn't exists");
        } elseif (!isset(self::$_components[$newId])) {
            self::$_components[$newId] =& self::$_components[$oldId];
        } else {
            throw new Imind_Context_Exception("Can't create alias $newId, component already exists");
        }
    }

    /**
     * Is the context already loaded
     *
     * @return bool is it loaded
     */
    public static function isLoaded() {
        return self::$_loaded;
    }

    /**
     * Clears everything from context
     *
     * @return void
     */
    public static function reset() {
        self::$_loaded = false;
        self::$_components = array();
        self::$_componentsLoading = array();
        self::$_properties = array();
        self::$_registry = array();
        self::$_defaults = array();
    }

    /**
     * Returns a component's object from the context
     *
     * @param  string the component's name
     * @param  boolean reinitialize the object
     * @return mixed the stored object
     */
    public static function getObject($id,$reinit=false) {
        if (!isset(self::$_registry[$id]) || $reinit) {
            if (in_array($id,self::$_componentsLoading)) {
                throw new Imind_Context_Exception("Circle in components definition");
            }
            self::$_componentsLoading[]=$id;
            self::$_registry[$id] = self::$_components[$id]->createInstance();
            $index=array_search($id,self::$_componentsLoading);
            if ($index !== false) {
                unset(self::$_componentsLoading[$index]);
            }
        }
        if (!isset(self::$_registry[$id])) {
            return null;
        }
        return self::$_registry[$id];
    }

    /**
     * Returns the default component's object for a classname
     *
     * @param  string the classname
     * @return mixed the stored object
     */
    public static function getDefaultObject($className) {
        $id=null;
        if (isset(self::$_defaults[$className])) {
            $id=self::$_defaults[$className];
        } else {
            foreach (self::$_components as $componentId=>$component) {
                if ($component->getClass() == $className && $component->getDefault()) {
                    $id=$componentId;
                }
            }
        }
        if ($id == null) {
            return null;
        }
        return self::getObject($id);
    }

    /**
     * Returns a component from the context
     *
     * @param  string the component's name
     * @return Imind_Context_Component the component
     */
    public static function getComponent($id) {
        if (isset(self::$_components[$id])) {
            return self::$_components[$id];
        } else {
            return null;
        }
    }

    /**
     * Returns a default component for a classname
     *
     * @param  string the classname
     * @return Imind_Context_Component the component
     */
    public static function getDefaultComponent($className) {
        $id=null;
        if (isset(self::$_defaults[$className])) {
            $id=self::$_defaults[$className];
        } else {
            foreach (self::$_components as $componentId=>$component) {
                if ($component->getClass() == $className && $component->getDefault()) {
                    $id=$componentId;
                }
            }
        }
        if ($id == null) {
            return null;
        }
        return self::$_components[$id];
    }

    /**
     * Sets a default component for a classname
     *
     * @param string the component's name
     * @param string the classname
     * @return void
     */
    public static function setDefault($id,$className) {
        if ($id instanceof Imind_Context_Component) {
            $id=$id->getId();
        }
        self::$_defaults[$className]=$id;
    }

    /**
     * Adds a component to the context
     *
     * @param Imind_Context_Component the component
     * @return void
     */ 
    public static function addComponent(Imind_Context_Component $component) {
        $id=$component->getId();
        if (!isset(self::$_components[$id])) {
            self::$_components[$id]=$component;
        } else {
            throw new Imind_Context_Exception("There is already a component with id: $id");
        }
    }

    /**
     * Replace a component with a new one
     *
     * @param mixed the component's name or the component
     * @param Imind_Context_Component the new component
     * @return void
     */
    public static function replaceComponent($old,Imind_Context_Component $new) {
        if ($old instanceof Imind_Context_Component) {
            $old=$old->getId();
        }
        self::$_components[$old]=$new;
    }

    /**
     * Deletes a component from the context
     *
     * @param mixed the component's name or the component
     * @return void
     */
    public static function removeComponent($id) {
        if ($id instanceof Imind_Context_Component) {
            $id=$id->getId();
        }
        if (isset(self::$_components[$id])) {
            unset(self::$_components[$id]);
        }
    }

}
