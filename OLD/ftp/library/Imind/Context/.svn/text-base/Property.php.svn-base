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
class Imind_Context_Property {
    
    /** @var array the property's locations */
    protected $_locations=array();

    /** @var string the property's kind (ini) */
    protected $_kind=null;
    
    /** @var array the property's data */
    protected $_data=null;
    
    /** @var bool has the property been loaded */
    protected $_loaded=false;

    /**
     * Constructor
     *
     * @param mixed string (comma separated) or an array of locations
     * @param string the kind of the property
     * @return void
     */
    public function __construct($locations=array(),$kind="ini") {
        $this->_kind=$kind;
        $this->_setLocations($locations);
    }
    
    /**
     * Get the locations of the properties
     *
     * @param boolean if true, we get a comma separated string, if false we get an array
     * @return mixed (string, array)
     */
    public function getLocations($string=true) {
        if ($string) {
            return join(",",$this->_locations);
        } else {
            return $this->_locations;
        }
    }
    
    /**
     * Returns a value that was found in a property
     *
     * @param string
     * @return string
     */
    public function get($name) {
        if (!$this->_loaded) {
            $this->_load();
        }
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        return false;
    }
    
    /**
     * Sets the locations of the properties
     *
     * @param mixed string (comma separated) or an array of locations
     * @return void
     */
    protected function _setLocations($locations) {
        if (is_string($locations)) {
            $this->_locations=split(",",$locations);
        } elseif (is_array($locations)) {
            $this->_locations=$locations;
        } else {
            throw new Imind_Context_Exception("Unkown type for property locations");
        }
    }
    
    /**
     * Loads the properties from the locations
     *
     * @return void
     */
    protected function _load() {
        switch ($this->_kind) {
        	case "ini":
        	    foreach ($this->_locations as $location) {
        	        $location=$this->_getRealLocation($location);
        	        if ($this->_data == null) {
        	            $this->_data=parse_ini_file($location);
        	        } else {
        	           $this->_data=array_merge($this->_data,parse_ini_file($location));
        	        }
        	    }
        		break;        
        	default:
        	    throw new Imind_Context_Exception("Unknown type for property");
        		break;
        }
    }
    
    /**
     * Gets the real locations of the properties
     *
     * @param string the location
     * @return string the real location
     */
    protected function _getRealLocation($location) {
        if (substr($location,0,13) == "include_path:") {
            $location=substr($location,13);
            $pathArray = explode(PATH_SEPARATOR,get_include_path());
            foreach ($pathArray as $path) {
                if (is_file($path.DIRECTORY_SEPARATOR.$location)) {
                    return $path.DIRECTORY_SEPARATOR.$location;
                }
            }
        }
        if (is_file($location)) {
            return $location;
        } else {
            throw new Imind_Context_Exception("Location for property not found: $location");
        }
    }
    
}