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
class Imind_Build
{
    const BUILD = "build";
    const PROXY = "proxy";
    
    /** @var hash the libraries (Imind_Build_Library) that need to be builded */
    protected $_libraries;
    
    /** @var string the name of the default library (buildVersion is calculated from it) */
    protected $_defaultLibrary;
        
    /** @var bool do obfuscation (or just minify) - yuicompressor argument */
    protected $_obfuscate;
    
    /** @var bool preserve semicolons - yuicompressor argument */
    protected $_preserveSemicolons;
    
    /** @var bool do micro optimizations - yuicompressor argument */
    protected $_optimize;
    
    /** @var string the files charset - yuicompressor argument */
    protected $_charset;
    
    /** @var string the full path for running the YUICompressor 
     *  eg: /bin/java -jar /usr/local/bin/yuicompressor-2.4.2.jar 
     */
    protected $_yuiCompressorPath;
    
    /** @var bool return builded filename or original for proxy()*/
    protected $_isProxy;
    
    /** @var Imind_Version the current build version */
    protected $_buildVersion;
    
    /** @var array the types we build */
    protected $_types = array("js", "css");
    
    /** @var bool has the libraries been compiled */
    protected $_compiled = false;
    
    /** @var array the js files build path/url (good for checking valid urls) */
    protected $_buildPaths = array();
    
    /**
     * Constructor
     * 
     * @param array $libraries the libraries that need to be builded
     *  "libraryName"=>array(
     *      "js|css" => array (
     *          "files" =>  files to build (commas separated string or array)
     *          "excludeFiles" => files to exclude (if only dirs given) 
     *              (commas separated string or array)
     *          "url" => url path of the files
     *          "dir" => filesystem path of the files
     *          "buildUrl" => url path of the builded file(s)
     *          "buildDir" => filesystem path of the builded file(s)
     *          "locales" => the locales of the files (eg: en,hu,...), 
     *              if set, only builds locale files (commas separated string or array)
     *          "merge" => boolean for merge builded files into one file
     *      "base" => string filesystem path for main directory, can calculate other js/css paths, 
     *          if filenames are given with path
     *      "phpLibraries" => string|array filesystem path for php libraries for stripping require_once
     *          it's only experimental, not working yet (commented out in code)
     * @param string $defaultLibrary
     * @param string $yuiCompressorPath
     * @param bool $isProxy
     * @param bool $obfuscate
     * @param bool $preserveSemicolons
     * @param bool $optimize
     * @param string $charset
     * @return void
     */
    public function __construct(array $libraries, $defaultLibrary, $yuiCompressorPath, $isProxy=false, 
        $obfuscate=true, $preserveSemicolons=false, $optimize=true, $charset="UTF-8") {
        if (isset($libraries) && is_array($libraries)) {
            foreach ($libraries as $name=>$library) {
                $this->_libraries[$name] = new Imind_Build_Library($name, $library);
            }
            $this->_defaultLibrary = (string) $defaultLibrary;
            if (!isset($this->_libraries[$this->_defaultLibrary])) {
                throw new Imind_Build_Exception(
                    sprintf('The default library "%s" can\'t be found', $this->_defaultLibrary));
            }
            if (!$this->_libraries[$this->_defaultLibrary]->getVar("js", "merge")) {
                throw new Imind_Build_Exception(
                    sprintf('For the default library (%s) "merge js files" has to be set true', $this->_defaultLibrary));
            }
        }
        if (isset($yuiCompressorPath)) {
            $this->setYuiCompressorPath($yuiCompressorPath);
        }
        $this->setObfuscate($obfuscate);
        $this->setPreserveSemicolons($preserveSemicolons);
        $this->setOptimize($optimize);
        $this->setCharset($charset);
        $this->setIsProxy($isProxy);
    }
    
    /**
     * Runs the build. Parameter set the next version optionally
     * 
     * @param string $version
     * @return void
     */
    public function run($version=null) {
        if (!isset($version)) {
            $version = Imind_Version::BUILD;
        }
        $this->_compileLibraries(true);
        if (isset($this->_buildVersion)) {
            $this->_buildVersion = $this->_buildVersion->next($version);
        } else {
            $this->_buildVersion = new Imind_Version('1.0.0');
        }
        // cycle through libraries and build them
        foreach ($this->_libraries as $name=>$library) {
            foreach (Imind_Build_Library::$types as $type) {
                $this->_compress($type, $library);
            }
        }
        //$this->_doStripRequireOnce();
    }
    
    /**
     * Proxy a filename. If a built file found return that
     * 
     * @param string $fileName
     * @param string $libraryName
     * @return string
     */
    public function proxy($fileName, $libraryName = "default") {
        $this->_compileLibraries(false);
        $type = Imind_Build_Library::getType($fileName);
        if (isset($this->_buildVersion) && $this->_isProxy && strlen($type) > 0) {
            if ($libraryName == "default") {
                $libraryName = $this->_defaultLibrary;
            }
            if (!isset($this->_libraries[$libraryName])) {
                throw new Imind_Build_Exception(sprintf('No such library: %s', $libraryName));
            }
            $library = $this->_libraries[$libraryName];
            $fileUrls = $library->getVar($type, "fileUrls");
            if (isset($fileUrls) && in_array($fileName, $fileUrls)) {
                $locales = $library->getVar($type, "locales");
                if (isset($locales)
                    && preg_match("/(^|\/|-)(?P<locale>(".join("|", $locales)."))\.".$type."$/", $fileName, $match)) {
                    return $this->_getBuildFileName($type, $library, $fileName, $this->_buildVersion, $match["locale"], true);
                } else {
                    return $this->_getBuildFileName($type, $library, $fileName, $this->_buildVersion, null, true);
                }
            } else {
                return $fileName;
            }
        } else {
            return $fileName;
        }
    }
    
    /**
     * Can add a library dynamicaly
     * 
     * @param string $name
     * @param array $library
     * @return void
     */
    public function addLibrary($name, $library, $isBuild=true) {
        if (is_array($library)) {
            $library = new Imind_Build_Library($name, $library);
        } elseif (!$library instanceof Imind_Build_Library) {
            throw new Imind_Build_Exception(
                    sprintf('Wrong type for library: %s', $name));
        }
        $library->compile($isBuild);
        $this->_libraries[$name] = $library;
    }
    
    /**
     * Gets all the libraries' build paths by type (js|css)
     * 
     * @param string $type
     * @return array
     */
    public function getBuildPaths($type) {
        $this->_compileLibraries(false);
        if (isset($this->_buildPaths[$type])) {
            return $this->_buildPaths[$type];
        } else {
            return array();
        }
    }
    
    protected function _compress($type, Imind_Build_Library $library) {
        $files = $library->getVar($type, "files");
        $buildDir = $library->getVar($type, "buildDir");
        $name = $library->getName();
        if (isset($files) && isset($buildDir)) {
            $locales = $library->getVar($type, "locales");
            if (isset($locales)) {
                foreach ($locales as $locale) {
                    $localeFiles = $this->_getLocaleFiles($type, $files, $locale);
                    $compressCommand = $this->_getCompressCommand($type, $library, $localeFiles, $locale);
                    //echo $compressCommand."\n";
                    shell_exec($compressCommand);
                }
            } else {
                $compressCommand = $this->_getCompressCommand($type, $library, $files);
                //echo $compressCommand."\n";
                shell_exec($compressCommand);
            }
        }
    }
    
    /**
     * Get the command for running the files through yuicompressor
     * 
     * @param string $type
     * @param Imind_Build_Library $library
     * @return string
     */
    protected function _getCompressCommand($type, Imind_Build_Library $library, $files, $locale=null) {
        if ($library->getVar($type, "merge")) {
            $files = array(join(" ", $files));
        }
        $compressCommand = "";
        $fileCount = count($files);
        for ($i = 0; $i < $fileCount; ++$i) {
            $file = $files[$i];
            $outputFile = $this->_getBuildFileName($type, $library, $file, $this->_buildVersion, $locale, false);
            $compressCommand .= "cat ".$file." | ".
            $this->_yuiCompressorPath." --charset ".$this->_charset." --type ".$type.
            " -o ".$outputFile;
            if (!$this->_obfuscate) {
                $compressCommand .= " --nomunge";
            }
            if ($this->_preserveSemicolons) {
                $compressCommand .= " --preserve-semi";
            }
            if (!$this->_optimize) {
                $compressCommand .= "--disable-optimiziations";
            }
            if ($i < $fileCount-1) {
                $compressCommand .= "; ";
            }
        }
        return $compressCommand;
    }
    
    protected function _getLocaleFiles($type, $files, $locale) {
        $localeFiles = array();
        foreach ($files as $file) {
            if (strlen($locale) > 0 && preg_match("/(^|\/|-)".$locale."\.".$type."$/", $file)) {
                $localeFiles[] = $file;
            }
        }
        return $localeFiles;
    }
    
    /**
     * Generates all the paths and filenames from the config for all the libraries
     * 
     * @return void
     */
    protected function _compileLibraries($isBuild=true) {
        if (!$this->_compiled) {
            foreach ($this->_libraries as $name => &$library) {
                $library->compile($isBuild);
                $buildPaths = $library->getBuildPaths();
                foreach ($buildPaths as $type=>$buildPath) {
                    if (!isset($this->_buildPaths[$type])) {
                        $this->_buildPaths[$type] = array();
                    }
                    $this->_buildPaths[$type][] = $buildPath;
                }
            }
            $this->_compiled = true;

            if (!isset($this->_buildVersion)) {
                $defaultLibrary = $this->_libraries[$this->_defaultLibrary];
                $buildDir = $defaultLibrary->getVar("js", "buildDir");
                if (isset($buildDir)) {
                    foreach (new DirectoryIterator($buildDir) as $fileInfo) {
                        if (preg_match("/^".$this->_defaultLibrary."-(?P<version>\d+\.\d+\.\d+\w*?)\.js$/", $fileInfo->getFileName(), $match)) {
                            $this->_setBuildVersion($match["version"]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Runs the require_once stripping for php libraries
     * 
     * @return void
     */
    protected function _doStripRequireOnce() {
        //TODO: s/^(\s*?)[^\/](require_once)/\1\/\/\2/g
        // sed not working from php as from command line
        $cmd = "cd %s; ".
            "find . -name '*.php' -print0 | xargs -0 ".
            "sed --regexp-extended --in-place 's/(require_once)/\/\/ \1/g'; exit";
        foreach ($this->_libraries as $library) {
            $phpLibraries = $library->getPhpLibraries();
            if (isset($phpLibraries)) {
                foreach ($phpLibraries as $phpLibrary) {
                    shell_exec(sprintf($cmd, $phpLibrary));
                }
            }
        }
    }
    
    /**
     * Sets a buildVersion if it bigger than the one we had
     * 
     * @param string $version
     * @return void
     */
    protected function _setBuildVersion($version) {
        $newVersion = new Imind_Version($version);
        if (!isset($this->_buildVersion) || $this->_buildVersion->compare($newVersion) === 1) {
            $this->_buildVersion = $newVersion;
        }
    }
        
    /**
     * Returns the filename we need to build
     * 
     * @param string $name
     * @param string $libraryName
     * @param Imind_Version $version
     * @param string $type
     * @param bool $url
     * @return string
     */
    protected function _getBuildFileName($type, Imind_Build_Library $library, $file, $version, $locale=null, $url=false) {
        if ($library->getVar($type, "merge")) {
            $fileName = $library->getName();
            if (isset($locale)) {
                $fileName = $fileName."-".$locale;
            }
        } else {
            $fileName = $this->_getRawFileName($file);
        }
        $path = "";
        if ($url) {
            $path = $library->getVar($type, "buildUrl");
        } else {
            $path = $library->getVar($type, "buildDir");
        }
        return $path."/".$fileName."-".$version->toString().".".$type;
    }
    
    /**
     * Strip the url/dir paths and the extension from a filename
     * 
     * @param string $fileName
     * @return string
     */
    protected function _getRawFileName($fileName) {
        $slashPos = strrpos($fileName, '/')+1;
        $dotPos = strrpos($fileName, '.');
        $length = strlen($fileName);
        return substr($fileName, $slashPos, $dotPos-$length);

    }

    /**
     * 
     * @param bool $obfuscate
     * @return void
     */
    public function setObfuscate($obfuscate) {
        $this->_obfuscate = (bool) $obfuscate;
    }
    
    /**
     * 
     * @return bool
     */
    public function getObfuscate() {
        return $this->_obfuscate;
    }
    
    /**
     * 
     * @param bool $preserveSemicolons
     * @return void
     */
    public function setPreserveSemicolons($preserveSemicolons) {
        $this->_preserveSemicolons = (bool) $preserveSemicolons;
    }
    
    /**
     * 
     * @return bool
     */
    public function getPreserveSemicolons() {
        return $this->_preserveSemicolons;
    }
    
    /**
     * 
     * @param bool $optimize
     * @return void
     */
    public function setOptimize($optimize) {
        $this->_optimize = (bool) $optimize;
    }
    
    /**
     * 
     * @return bool
     */
    public function getOptimize() {
        return $this->_optimize;
    }
    
    /**
     * 
     * @param string $charset
     * @return void
     */
    public function setCharset($charset) {
        $this->_charset = $charset;
    }
    
    /**
     * 
     * @return string
     */
    public function getCharset() {
        return $this->_charset;
    }
    
    /**
     * 
     * @param string $yuiCompressorPath
     * @return void
     */
    public function setYuiCompressorPath($yuiCompressorPath) {
        $this->_yuiCompressorPath = (string) $yuiCompressorPath;
    }
    
    /**
     * 
     * @return string
     */
    public function getYuiCompressorPath() {
        return $this->_yuiCompressorPath;
    }
    
    /**
     * 
     * @param bool $isProxy
     * @return void
     */
    public function setIsProxy($isProxy) {
        $this->_isProxy = (bool) $isProxy;
    }
    
    /**
     * 
     * @return bool
     */
    public function getIsProxy() {
        return $this->_isProxy;
    }
    
    /**
     * 
     * @return Imind_Version
     */
    public function getBuildVersion() {
        return $this->_buildVersion;
    }
    
}
