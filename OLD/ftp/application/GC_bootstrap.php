<?php
//apd_set_pprof_trace('C:\temp');
define('ENCODING','UTF-8');
define('TIDY_ENCODING','utf8');
#Set internal character encoding to UTF-8 */
mb_internal_encoding(ENCODING);
mb_http_output(ENCODING);

header("Content-type: text/html; charset=UTF-8");
//header("Content-type: application/xhtml+xml; charset=UTF-8");


defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__)));
//define('APPLICATION_ENV', 'production');
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));


/// Well magic_quotes_gpc is by deprecated by php 5.3 and will not be available in php 6
/// to turn them of write in the .htaccess file for zour directory:
///     php_value magic_quotes_gpc off
/// or uncomment the exception here for that hack to work
/// recommended is using setting it in the php.ini itself, then in the .htaccess then in here by removing the exception

/// SUPERHINT: for compatibillity with code written for environments where magic_quotes_gpc is on
/// it would be easy to write addslashes_deep using stripslashes_deep as blueprint
function stripslashes_deep(&$value)
{
    return is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);
}

if( (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc())
    || (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase'))!="off"))
    )
{
    if(!(defined('REMOVE_MAGIC_QUOTES') && REMOVE_MAGIC_QUOTES))
    {
        throw new Exception('Magic quotes is on, best turn it off. Or define(REMOVE_MAGIC_QUOTES, True) (Or uncomment this Exception at: '.__FILE__.' Line: '.__LINE__);
    }
    stripslashes_deep($_GET);
    stripslashes_deep($_POST);
    stripslashes_deep($_COOKIE);
}

ini_set('xdebug.var_display_max_depth', '10');
// CONFIGURATION - Setup the configuration object
// The Zend_Config_Ini component will parse the ini file, and resolve all of
// the values for the given section.  Here we will be using the section name
// that corresponds to the APP's Environment
require_once 'Zend/Config/Ini.php';
if(file_exists(APPLICATION_PATH . '/configs/graphicore.de'))
{
    $configuration = new Zend_Config_Ini(APPLICATION_PATH . '/configs/graphicore.de.ini', APPLICATION_ENV);
    chdir(APPLICATION_PATH);
    $_SERVER['TEMP'] = APPLICATION_PATH.'/tmp';
}
else
{
    $configuration = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
}


// DATABASE ADAPTER - Setup the database adapter
// Zend_Db implements a factory interface that allows developers to pass in an
// adapter name and some parameters that will create an appropriate database
// adapter object.  In this instance, we will be using the values found in the
// "database" section of the configuration obj.
//$dbAdapter = Zend_Db::factory($configuration->database);

//print_r($configuration->database);
// DATABASE TABLE SETUP - Setup the Database Table Adapter
// Since our application will be utilizing the Zend_Db_Table component, we need
// to give it a default adapter that all table objects will be able to utilize
// when sending queries to the db.
//Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);
//My_Db_Table_Abstract::setDefaultAdapter($dbAdapter);

// REGISTRY - setup the application registry
// An application registry allows the application to store application
// necessary objects into a safe and consistent (non global) place for future
// retrieval.  This allows the application to ensure that regardless of what
// happends in the global scope, the registry will contain the objects it
// needs.
require_once 'Zend/Registry.php';
$registry = Zend_Registry::getInstance();
$registry->config        = $configuration;
///stuff with locales
require_once 'Zend/Locale.php';
Zend_Locale::$compatibilityMode = false;//don't know if this is good or bad for other Zend Classes using Zend_Locale
require_once 'GC/I18n.php';
GC_I18n::bootstrap();

// CLEANUP - remove items from global scope
// This will clear all our local boostrap variables from the global scope of
// this script (and any scripts that called bootstrap).  This will enforce
// object retrieval through the Applications's Registry
unset($configuration, $registry);
