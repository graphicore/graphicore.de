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
 * @category   Imind
 * @package    Imind_Context
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
abstract class Imind_Context_Loader {

    /**
     * Creates and returns a loader from the parameters
     *
     * @param string the loader's kind (array, xml)
     * @param mixed the data (it depends on the kind)
     * @param mixed the schema for checking the data (it depends on the kind)
     * @return mixed the loader (Imind_Context_Loader_Array or Imind_Context_Loader_Xml)
     */
    public static function factory($kind,$data,$schema=null) {
        switch ($kind) {
            case 'array':
                $className = 'Imind_Context_Loader_Array';
                break;
            case 'xml':
                $className = 'Imind_Context_Loader_Xml';
                break;
            default:
                require_once 'Imind/Context/Loader/Exception.php';
                throw new Imind_Context_Loader_Exception("Kind \"$kind\" is not supported");
        }
        Zend_Loader::loadClass($className);
        return new $className($data,$schema);
    }

    /**
     * Constructor
     *
     * @param mixed the data, that needs to be loaded
     * @return mixed the object
     */
    abstract protected function __construct($data);

    /**
     * Returns a loaded data
     *
     * @return mixed the data that can be read by the context
     */
    abstract public function get();

    /**
     * Validate the data
     *
     * @param mixed the data
     * @return bool is it valid
     */
    abstract public function validate($data);
    
}