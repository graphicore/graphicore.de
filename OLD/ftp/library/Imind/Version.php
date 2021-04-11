<?php
/**
 * 
 * Imind Library
 *
 * @category   Imind
 * @package    Imind_Version
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * @see Imind_Version_Exception
 */
require_once 'Imind/Version/Exception.php';

/**
 * @category   Imind
 * @package    Imind_Version
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class Imind_Version
{

    /** @const int the index of the major number */
    const MAJOR = 0;

    /** @const int the index of the minor number */
    const MINOR = 1;

    /** @const int the index of the build number */
    const BUILD = 2;

    /** @var int the major number part of the version */
    protected $_major;
    
    /** @var int the minor number part of the version */
    protected $_minor;
    
    /** @var int the build number part of the version */
    protected $_build;
    
    /** @var int the first number part of the build number */
    protected $_rawBuildNumber;
     
    /**
     * Constructor
     * 
     * @param string $version
     * @return void
     */
    public function __construct($version) {
        $this->_calculateVersion($version);
    }
     
    /**
     * Creates a next version from a string or an int
     *  if it's an int (Imind_Version::BUILD, Imind_Version::MINOR, Imind_Version::MAJOR),
     *  then calculates next version by incrementing the part
     * @param string|int $version
     * @return Imind_Version
     */
    public function next($version=2) {
        if (is_int($version)) {
            $nextBuild = $this->_rawBuildNumber;
            $nextMinor = $this->_minor;
            $nextMajor = $this->_major;
            switch($version) {
                case self::BUILD:
                    $nextBuild = $this->_rawBuildNumber + 1;
                    break;
                case self::MINOR:
                    $nextMinor = $this->_minor + 1;
                    $nextBuild = 0;
                    break;
                case self::MAJOR:
                    $nextMajor = $this->_major + 1;
                    $nextMinor = 0;
                    $nextBuild = 0;
                    break;
            }
            $version = $nextMajor.".".$nextMinor.".".$nextBuild;
        }
        $newVersion = new Imind_Version($version);
        return $newVersion;
    }
    
    /**
     * Compares the parameter to this version (php: compare_version)
     * Returns -1 if less, 0 if equals, 1 if more
     * 
     * @param Imind_Version|string $version
     * @return int
     */
    public function compare($version) {
        if ($version instanceof Imind_Version) {
            $version = $version->toString();
        }
        return version_compare($version, $this->toString());
    }

    /**
     * Returns the version as a string
     * 
     * @return string
     */
    public function toString() {
        return $this->_major.'.'.$this->_minor.'.'.$this->_build;
    }

    /**
     * Calculates the major, minor, build parts of the version
     * 
     * @param string $version
     * @return void
     */
    protected function _calculateVersion($version) {
        $versionArray = split("\.", $version);
        if (count($versionArray) > 3) {
            for ($i = 3; $i < count($versionArray); ++$i) {
                $versionArray[self::BUILD] .= $versionArray[$i];
            }
            $versionArray = array_slice($versionArray, 0, 3);
        }
        if ($this->_isNumber($versionArray[self::MAJOR])) {
            $this->_major = (int) $versionArray[self::MAJOR];
        } else {
            throw new Imind_Version_Exception(
                sprintf('Wrong major number: "%s"', $versionArray[self::MAJOR]));
        }
        if ($this->_isNumber($versionArray[self::MINOR])) {
            $this->_minor = (int) $versionArray[self::MINOR];
        } else {
            throw new Imind_Version_Exception(
                sprintf('Wrong minor number: "%s"', $versionArray[self::MINOR]));
        }
        $rawBuildNumber = preg_replace("/^(\d+?)[^\d]*?$/","\\1", $versionArray[self::BUILD]);
        if ($this->_isNumber($rawBuildNumber)) {
            $this->_build = $versionArray[self::BUILD];
            $this->_rawBuildNumber = (int) preg_replace("/^(\d+?)[^\d]*?$/","\\1", $versionArray[self::BUILD]);
        } else {
            throw new Imind_Version_Exception(
                sprintf('Can\'t find first number part in build number: "%s"', $versionArray[self::BUILD]));
        }
    }
    
    /**
     * Return true if the parameter is a number
     * 
     * @param int $number
     * @return bool
     */
    protected function _isNumber($number) {
        return strval(intval($number)) === strval($number);
    }
}
