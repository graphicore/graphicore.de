<?php
/**
 * 
 * Imind Library
 *
 * @category   Imind
 * @package    Imind_Context
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license    http://library.imind.hu/licence/bsd
 */

/**
 * @see Imind_Context_Loader_Exception
 */
require_once 'Imind/Context/Loader/Exception.php';


/**
 * @category   Imind
 * @package    Imind_Context
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license    http://library.imind.hu/licence/bsd
 */
class Imind_Context_Loader_Array extends Imind_Context_Loader {

    /** @var array the loaded data */
    protected $_data = array();

    /**
     * Constructor
     *
     * @param mixed the data, that needs to be loaded
     * @param mixed the schema to validate to
     * @return mixed the object
     */
    protected function __construct($data,$schema=null) {
        if (is_array($data)) {
            $this->_data = $data;
        } elseif (is_string($data) && is_file($data)) {
            $this->_data = require_once($data);
        } else {
            throw new Imind_Context_Loader_Exception('Unsupported data type for array loading');
        }
    }
    
    /**
     * Returns a loaded data
     *
     * @return mixed the data that can be read by the context
     */
    public function get() {
        return $this->_data;
    }
    
    /**
     * Validate the data
     *
     * @param mixed the data
     * @return bool is it valid
     */
    public function validate($data) {
        return true;
    }
}