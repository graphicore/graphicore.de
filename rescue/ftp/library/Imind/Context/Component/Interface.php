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
 * @category   Imind
 * @package    Imind_Context
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
interface Imind_Context_Component_Interface {

    /**
     * Gets the component's id
     *
     */
    public function getId();

    /**
     * Returns the component's classname
     *
     */
    public function getClass();

    /**
     * Returns if it's a default for a class
     *
     */
    public function getDefault();

    /**
     * Return the component converted to array
     *
     */
    public function toArray();

    /**
     * Sets the component's classname
     *
     * @param string
     */ 
    public function setClass($class);

    /**
     * Sets the component's factory methodname
     *
     * @param string
     */ 
    public function setFactory($factory);

    /**
     * Sets the component to be default for a class
     *
     * @param boolean
     */
    public function setDefault($default);

    /**
     * The object should be created on load or not
     * 
     * @param booelean
     */
    public function setCreateOnLoad($createOnLoad);

    /**
     * The object should be created on load or not
     * 
     */
    public function getCreateOnLoad();

    /**
     * Sets the component's init method (it gets called first after creating an instance)
     *
     * @param string
     */
    public function setInit($init);

    /**
     * Creates an argument for the component
     *
     * @param string the value
     * @param string the argument's name
     */
    public function createArgument($value,$name="");

    /**
     * Creates an array argument for the component
     *
     * @param array the value
     * @param string the argument's name
     */
    public function createArrayArgument($value,$name="");

    /**
     * Creates a reference argument for the component
     *
     * @param string the reference's name
     */
    public function createReferenceArgument($ref);

    /**
     * Adds a constructor argument to the array
     *
     * @param array the argument
     */
    public function addConstructorArgument($arg);

    /**
     * Adds a method argument to the array
     *
     * @param array the argument
     */
    public function addMethodArgument($name,$arg);

    /**
     * Adds a setter argument to the array
     *
     * @param array the argument
     */
    public function addSetterArgument($variableName,$arg);

    /**
     * Creates an object from the component
     *
     */
    public function createInstance();
}
