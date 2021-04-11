<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initIniset()
    {
        if(APPLICATION_ENV === 'development')
        {
            ini_set('soap.wsdl_cache_enabled', '0');
        }
    }
    protected function _initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Default_',
            //'basePath'  => dirname(__FILE__),
            'basePath'  => APPLICATION_PATH.'/modules/default',
        ));
        return $autoloader;
    }
    protected function _initModules()
    {
        //these might be set in application.ini somehow

        $this->bootstrap('FrontController');
        $front = $this->getResource('FrontController');

        $controllerDirs = array(
            array(APPLICATION_PATH.'/modules/default/controllers', 'default'),
            array(APPLICATION_PATH.'/modules/backend/controllers', 'backend'),
        );
        foreach($controllerDirs as $controllerDir)
        {
            $front->addControllerDirectory($controllerDir[0],$controllerDir[1]);
        }

        //adding other modules to the autoloader
        $this->bootstrap('Autoload');
        $loader = Zend_Loader_Autoloader::getInstance();

        $extraModules = array(
            array(
                'namespace' => 'Backend_',
                'basePath'  => APPLICATION_PATH.'/modules/backend',
            ),
        );
        foreach($extraModules as $extraModule)
        {
            $autoloader = new Zend_Application_Module_Autoloader($extraModule);
            $loader->pushAutoloader($autoloader);
        }
    }
    protected function _initViewHelpers()
    {
        $this->bootstrap('view');                   // make sure we have a view
        $view = $this->getResource('view');         // get the view resource

        $view->addHelperPath(APPLICATION_PATH.'/modules/backend/views/helpers', 'Backend_View_Helper');

        $view->addHelperPath(APPLICATION_PATH.'/modules/common/views/helpers', 'Common_View_Helper');
        $view->addHelperPath(APPLICATION_PATH.'/modules/common/views/helpers/Navigation', 'Common_View_Helper_Navigation');

        return $view;
    }

    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
    }
    protected function _initRouter()
    {
        //as a starting point
        //maybe a router can be added via the config?
        //  but here we can replace thr standard router or access its methods to change it's behavior
        $this->bootstrap('FrontController');
        $front   = $this->getResource('FrontController');
        $router = $front->getRouter();

        $registry = Zend_Registry::getInstance();

        //might be the systems default lang:
        //$defaultLang = $registry->config->locales->default
        //or the lang the user uses as default:
        //$defaultLang = $registry->Zend_Locale->toString();

        $defaultLang = $registry->Zend_Locale->toString();

        $allowedLocales = '('.implode('|', $registry->allowedLocales).')';

        $router->setGlobalParam('lang', $defaultLang);

        $route = new Zend_Controller_Router_Route(
            //':lang/:controller/:action/*',
            ':lang/:action/:key/*',
            array(
                'module'    => 'default',
                'lang'      => $defaultLang,
                'controller' => 'index',
                'action'    => 'index',//all actions of the indexController are allowed
                'key' => ''

            ),
            array(
                'lang'  => $allowedLocales//these are possible

            )
        );
        $router->addRoute('i18n', $route);

        $route = new Zend_Controller_Router_Route(
            ':module/:controller/:action/*',
            array(
                'module'    => 'default',
                'controller' => 'index',
                'action'    => 'index'
            ),
            array(
                'module' => '(default|backend)',
            )
        );
        $router->addRoute('modules', $route);

        $route = new Zend_Controller_Router_Route(
            ':lang/:module/:controller/:action/*',
            array(
                'module'    => 'default',
                'lang'      => $defaultLang,
                'controller' => 'index',
                'action'    => 'index'
            ),
            array(
                'module' => '(default|backend)',
                'lang'  => $allowedLocales//these are possible
            )
        );
        $router->addRoute('modules_i18n', $route);

        $route = new Zend_Controller_Router_Route(
            ':lang/login/*',
            array(
                'module'    => 'backend',
                'lang'      => $defaultLang,
                'controller' => 'user',
                'action'    => 'login'
            ),
            array(
                'lang'  => $allowedLocales//these are possible
            )
        );
        $router->addRoute('loginI18n', $route);
        $route = new Zend_Controller_Router_Route(
            'login/*',
            array(
                'module'    => 'backend',
                'lang'      => $defaultLang,
                'controller' => 'user',
                'action'    => 'login'
            )
        );
        $router->addRoute('login', $route);
        //$this->setRouter(new Zend_Controller_Router_Rewrite()); //default router
        //throw new Exception(Zend_Debug::Dump($front->getRouter()));
    }
    public function _initDoctrine()
    {
        $this->bootstrap('Autoload');
        require_once 'Doctrine.php';
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->pushAutoloader(array('Doctrine_Core', 'autoload'));

        $doctrineConfig = $this->getOption('doctrine');
        $manager = Doctrine_Manager::getInstance();
        $manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_CONSERVATIVE);

        Doctrine_Core::loadModels($doctrineConfig['models_path']);
        //Doctrine_Core::setModelsDirectory($doctrineConfig['models_path']);
        $loader->pushAutoloader(array('Doctrine', 'modelsAutoload'));
        //GC_Debug::Dump($lm,true);
        //throw new Exception('bam');


        //get the table prefix from the ini
        $rescources = $this->getOption('resources');
        $db = $rescources['db'];
        //CAUTION:: the _ underscore is added since its hardcoded in my gc_zend database classes
        $table_prefix = (array_key_exists('table_prefix',$db)) ? (string) $db['table_prefix'].'_' : '';
        $manager->setAttribute(Doctrine::ATTR_TBLNAME_FORMAT, $table_prefix.'%s');
        //turn on all validation
        $manager->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);
        //enabled the auto_accessor_override attribute
        $manager->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
        /*
        $dbConnect = $db['params'];
        $connection_string = sprintf(
            'mysql://%1$s:%2$s@%3$s/%4$s',
            $dbConnect['username'],
            $dbConnect['password'],
            $dbConnect['host'],
            $dbConnect['dbname']);
        */
        $this->bootstrap('db');
        $pdoAdapter = Zend_Db_Table_Abstract::getDefaultAdapter()->getConnection();
        //reuse the connection from zendframework
        //this way doctrine doesn't know the username and password of the connection,
        //what can be added of course
        $manager->openConnection($pdoAdapter);
        //resources.db.params.profiler.enabled    = true
        if($db['params']['profiler']['enabled'])
        {
        //$conn = Doctrine_Manager::connection('pgsql://dbuser:secret@db.example.com/db');
            $profiler = new Imind_Profiler_Doctrine_Firebug();
            //$conn->setListener($profiler);
            Doctrine_Manager::connection()->setListener($profiler);
        }
        return $manager;
    }
    protected function _initSession()
    {
        $config = Zend_Registry::getInstance()->config->session;
        if($config->ini_set)
        {
            foreach($config->ini_set as $key => $val)
            {
                ini_set('session.'.$key, (string) $val);
            }
        }
        $this->bootstrap('db');
        //this can fail without an useable errormessage!
        //it did that when the table had the wrong name (a typo, the table did not exist)
        $saveHandlerClass = 'GC_Session_SaveHandler_DbTable';
        Zend_Session::setSaveHandler(new $saveHandlerClass($config));
        if(Zend_Session::sessionExists())
        {
/*
            // helped to debug
            $gc_probability = isset($config->gc_probability_custom) ? (int)$config->gc_probability_custom : 1;
            $gc_divisor = isset($config->gc_divisor_custom) ? (int)$config->gc_divisor_custom : 100;
            if( $gc_probability > 0 && $gc_divisor > 0)
            {
                $number = (int) round($gc_divisor / $gc_probability);
                $number = (1 > $number) ? 1 : $number;
                if(rand(1, $number) === $number)
                {
                    $saveHandler = Zend_Session::getSaveHandler();
                    //throw new GC_Debug_Exception($saveHandler);
                    $saveHandler->gc(ini_get('session.gc_maxlifetime'));
                }
            }
            throw new GC_Debug_Exception('stop');
*/
//            Zend_Session::regenerateId();
        }
    }
    protected function _initAcl()
    {
        $this->bootstrap('FrontController');


        $config = Zend_Registry::getInstance()->config->acl;

        if($config->file)
        {
            require_once $config->file;
        }
        Zend_Registry::getInstance()->acl = new $config->class();
        $front = $this->getResource('FrontController');
        $front->registerPlugin(new GC_Controller_Plugin_NotFound());
        $front->registerPlugin(new Custom_Controller_Plugin_Auth());
    }


    protected function _initNavi()
    {

        $this->bootstrap('Language');
        $this->bootstrap('FrontController');
        $front   = $this->getResource('FrontController');
        //$this->bootstrap('Router');
        //$front->getRouter();
        //$request = new Zend_Controller_Request_Http();
        //$request = $front->getRequest();
        //GC_I18n::setLang($request->getParam('lang'));

        $front -> registerPlugin(new Custom_Controller_Plugin_Navigation());
    }
    protected function _initLanguage()
    {
        $this->bootstrap('FrontController');
        $front   = $this->getResource('FrontController');
        $front -> registerPlugin(new Custom_Controller_Plugin_Lang());
    }

}

