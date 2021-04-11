<?php
/**
 * 
 * Imind Library
 *
 * @category   Imind
 * @package    Imind_Build
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * @see Imind_Build_Exception
 */
require_once 'Imind/Build/Exception.php';

/**
 * @category   Imind
 * @package    Imind_Build
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class Imind_Build_Library
{
    
    /** @var string library's name */
    protected $_name;
    
    /** @var array the js files in the library */
    protected $_jsFiles;
    
    /** @var array the css files in the library */
    protected $_cssFiles;
    
    /** @var array the excluded js files (in case only a directory is given) */
    protected $_jsExcludeFiles;
    
    /** @var array the excluded css files (in case only a directory is given) */
    protected $_cssExcludeFiles;

    /** @var string filesystem path for base directory */
    protected $_baseDir;
    
    /** @var string the filesystem path for js directory (if base given they are concatenated) */
    protected $_jsDir;
    
    /** @var string the filesystem path for css directory (if base given they are concatenated) */
    protected $_cssDir;
    
    /** @var string the filesystem path for js build directory (if base given they are concatenated) */
    protected $_jsBuildDir;
    
    /** @var string the filesystem path for css build directory (if base given they are concatenated) */
    protected $_cssBuildDir;
    
    /** @var string the url for js files */
    protected $_jsUrl;
    
    /** @var string the url for css files */
    protected $_cssUrl;
    
    /** @var string the url for builded js files */
    protected $_jsBuildUrl;
    
    /** @var string the url for builded css files */
    protected $_cssBuildUrl;
    
    /** @var array the js locales of the files (eg: en,hu,...),
     *      if set, only builds locale files */
    protected $_jsLocales;
    
    /** @var array the css locales of the files (eg: en,hu,...),
     *      if set, only builds locale files */
    protected $_cssLocales;
    
    /** @var array the js files with urls */
    protected $_jsFileUrls;
    
    /** @var array the css files with urls */
    protected $_cssFileUrls;
    
    /** @var bool merge builded js files into one file */
    protected $_jsMerge = true;
    
    /** @var bool merge builded css files into one file */
    protected $_cssMerge = true;
    
    /** @var array filesystem path for php libraries for stripping require_once */
    protected $_phpLibraries;
    
    /** @var array the types library recognizes */
    public static $types = array("js", "css");
    
    /** @var array the structure the library recognizes */
    public static $attributes = array("files", "excludeFiles", "dir", "buildDir", "url", "buildUrl", "locales", "merge");
    
    /**
     * Constructor
     * 
     * @param string $name
     * @param array $library
     * @return void
     */
    public function __construct($name, array $library=null) {
        $this->_name = $name;
        
        if (isset($library)) {
            $this->setBaseDir($library);

            foreach (self::$types as $type) {
                if (isset($library[$type])) {
                    foreach (self::$attributes as $attribute) {
                        if (isset($library[$type][$attribute])) {
                            $this->setVar($type, $attribute, $library[$type][$attribute]);
                        }
                    }
                }
            }
            if (isset($library["phpLibraries"])) {
               $this->setPhpLibraries($library["phpLibraries"]);
            }
        }
    }

    /**
     * Get library's variable by type
     * 
     * @param $type
     * @param $name
     * @return mixed
     */
    public function getVar($type, $name) {
        if (isset($this->{'_'.$type.ucfirst($name)})) {
            return $this->{'_'.$type.ucfirst($name)};
        } else {
            return null;
        }
    }
    
    /**
     * Set library's variable by type
     * 
     * @param $type
     * @param $name
     * @param $value
     * @return void
     */
    public function setVar($type, $name, $value) {
        if ($name === "files" || $name === "excludeFiles" || $name === "locales" || $name === "fileUrls") {
            if (is_string($value)) {
                $value = split(",", $value);
                $value = array_map("trim", $value);
            } elseif (!is_array($value)) {
                throw new Imind_Build_Exception(
                    sprintf('Wrong type (not string or array) for %s in library: %s for type: %s', $name, $this->_name, $type));
            }
        } elseif ($name !== "merge") {
            $value = trim((string) $value);
        }
        $this->{'_'.$type.ucfirst($name)} = $value;
    }

    /**
     * Set the base directory
     * 
     * @param $library
     * @return void
     */
    public function setBaseDir($library) {
        if (!isset($library["base"])) {
            $base = "";
        } elseif (is_dir(trim($library["base"]))) {
            $base = trim($library["base"])."/";
        } else {
            throw new Imind_Build_Exception(
                sprintf('Couldn\'t find base directory: %s for library: %s', $library["base"], $this->_name));
        }
        $this->_baseDir = (string) $base;
    }
    
    /**
     * Get the php libraries
     * 
     * @return array
     */
    public function getPhpLibraries() {
        return $this->_phpLibraries;
    }
    
    /**
     * Set the php libraries
     * @param $phpLibraries
     * @return void
     */
    public function setPhpLibraries($phpLibraries) {
        if (is_string($phpLibraries)) {
            $phpLibraries = split(",", $phpLibraries);
            $phpLibraries = array_map("trim", $phpLibraries);
        } elseif (!is_array($phpLibraries)) {
            throw new Imind_Build_Exception(sprintf('Wrong type for phpLibraries in library: %s', $this->_name));
        }
        $this->_phpLibraries = $phpLibraries;
    }
    
    /**
     * Generates all the paths and filenames
     * 
     * @param string $name
     * @param array $library
     * @return void
     */
    public function compile($isBuild = true) {
        $this->_setDirs($isBuild);
        $this->_setUrls();
        $this->_setFiles();
        $this->_setLocales();        
    }

    /**
     * Get the library's name
     * @return string
     */
    public function getName() {
        return $this->_name;
    }
    
    /**
     * Get the js buildpath (good for checking valid urls)
     * @return array
     */
    public function getBuildPaths() {
        $buildPath = array();
        foreach (self::$types as $type) {
            $buildDir = $this->getVar($type, "buildDir");
            $buildUrl = $this->getVar($type, "buildUrl");
            if (isset($buildDir) && isset($buildUrl)) {
                $buildPath[$type] = array("buildUrl"=>$buildUrl, "buildDir"=>$buildDir);
            }
        }
        return $buildPath;
    }
    
    /**
     * Gets the extension part off a filename
     * 
     * @param string $fileName
     * @return string
     */
    public static function getType($fileName, $error=false) {
        //$type = pathinfo($fileName, PATHINFO_EXTENSION);
        //$type = substr($fileName,strrpos($fileName,'.')+1);
        $pos = strrpos($fileName, '.');
        $type = "";
        if($pos === false && $error) {
            throw new Imind_Build_Exception(
                sprintf('Couldn\'t calculate type (js|css) from file: %s', $fileName));
        } else {
            $type = substr($fileName, $pos+1);
            if ($error && !in_array($type, self::$types)) {
                throw new Imind_Build_Exception(
                    sprintf('Couldn\'t calculate type (%s) from file: %s', join("|", self::$types),$fileName));
            }
        }
        return $type;
    }

    /**
     * Generates the directory paths
     * 
     * @return void
     */
    protected function _setDirs($isBuild) {
        $dirTypes = array("dir", "buildDir");
        foreach (self::$types as $type) {
            $dirTypesFound = array();
            foreach ($dirTypes as $dirType) {
                $dir = $this->getVar($type, $dirType);
                if (isset($dir)) {
                    $dir = $this->_baseDir.$dir;
                    $this->setVar($type,$dirType,$dir);
                    if (!is_dir($dir)) {
                        if ($isBuild && $dirType === "buildDir") {
                            mkdir($dir, 0777, true);
                        } else {
                            throw new Imind_Build_Exception(
                                sprintf('Couldn\'t find %s directory: %s for library: %s', $type, $dir, $this->_name));
                        }
                    }
                    $dirTypesFound[] = $dirType;
                }
            }
            $diff = array_diff($dirTypes, $dirTypesFound);
            foreach ($diff as $dirType) {
                if ($dirType == "buildDir") {
                    $this->setVar($type, $dirType, $this->getVar($type, "dir"));
                } else {
                    $this->setVar($type, $dirType, $this->_baseDir);
                }
            }
        }
    }
    
    /**
     * Generates the url paths
     * @return void
     */
    protected function _setUrls() {
        foreach (self::$types as $type) {
            $url = $this->getVar($type, "url");
            if (!isset($url)) {
                $this->setVar($type, "url", "");
            }
            $buildUrl = $this->getVar($type, "buildUrl"); 
            if (!isset($buildUrl)) {
                $this->setVar($type, "buildUrl", $this->getVar($type, "url"));
            }
        }
    }
    
    /**
     * Generates the file paths
     * 
     * @return void
     */
    protected function _setFiles() {
        foreach (self::$types as $type) {
            $excludeFiles = $this->getVar($type,"excludeFiles"); 
            if (!isset($excludeFiles)) {
                $this->setVar($type, "excludeFiles", array());
            }
            $files =& $this->{'_'.$type."Files"};
            $dir = $this->getVar($type, "dir");
            if (isset($files)) {
                if (strlen($dir) > 0) {
                    $dir = $dir."/";
                }
                foreach ($files as &$file) {
                    $fileUrls = $this->getVar($type, "fileUrls"); 
                    if (!isset($fileUrls)) {
                        $this->setVar($type, "fileUrls", array());
                    }
                    $this->{'_'.$type."FileUrls"}[] = $this->getVar($type, "url")."/".$file;
                    $file = $dir.$file;
                    if (!is_file($file)) {
                        throw new Imind_Build_Exception(
                            sprintf('Couldn\'t find file: %s for library: %s', $file, $name));
                    }
                }
            } elseif (strlen($dir) > 0) {
                foreach (new DirectoryIterator($dir) as $fileInfo) {
                    $fileName = $fileInfo->getFileName();
                    if ($fileInfo->isFile()
                        && !in_array($fileName, $this->getVar($type, "excludeFiles"))
                        && in_array(self::getType($fileName), self::$types)
                        && !($this->getVar($type, "dir") === $this->getVar($type, "buildDir")
                            && preg_match("/^".$this->_name."-(?P<version>\d+\.\d+\.\d+\w*?)\.".$type."$/", $fileName, $match))) {

                        $files = $this->getVar($type, "files");
                        if (!isset($files)) {
                            $this->setVar($type, "files", array());
                        }
                        $fileUrls = $this->getVar($type, "fileUrls"); 
                        if (!isset($fileUrls)) {
                            $this->setVar($type, "fileUrls", array());
                        }
                        $this->{'_'.$type."Files"}[] = $this->getVar($type, "dir")."/".$fileName;
                        $this->{'_'.$type."FileUrls"}[] = $this->getVar($type, "url")."/".$fileName;
                    }
                }
            }
        }
    }
    
    /**
     * If the library is locale library, filters only the locale files
     * 
     * @return void
     */
    protected function _setLocales() {
        foreach (self::$types as $type) {
            $locales = $this->getVar($type, "locales");
            $files =  $this->getVar($type, "files");
            $fileUrls = $this->getVar($type, "fileUrls");
            if (isset($locales) && isset($files)) {
                $localeFiles = array();
                $localeFileUrls = array();
                foreach ($locales as $locale) {
                    for ($i = 0; $i < count($files); ++$i) {
                        if (preg_match("/(^|\/|-)".$locale."\.".$type."$/", $files[$i])) {
                            $localeFiles[] = $files[$i];
                            $localeFileUrls[] = $fileUrls[$i];
                        }
                    }
                }
                $this->setVar($type, "files", $localeFiles);
                $this->setVar($type, "fileUrls", $localeFileUrls);
            }
        }
    }
        
}
