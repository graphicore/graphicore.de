<?php
abstract class Formation_MediaServer_Gateway_Abstract
implements Formation_MediaServer_Interface
{
    /*
     * pathes like these:
     * portfolio/images/
     * portfolio/videos/
     * portfolio/plaintext/
     *
     * protected $_resources = array(
     *     'portfolio' => array
     *     (
     *        'images' => 'Custom_MediaServer_Images_Portfolio',
     *        'videos' => 'Custom_MediaServer_Videos_Portfolio',
     *     ),
     * );
     */
    protected $_resources = array();
    protected $_resourcesCache = array();
    /*
     * the realpath to the folder managed by this class
     * realpath(APPLICATION_PATH.'/public/media/)'
     */
    protected $_realPath = '';
    /*
     * so that appending /portfolio/images/ leads to the image Server class
     * without the url, but with the relative rest
     */
    protected $_webPath = '';
    protected $_messages = array();
    public function __construct($realPath, $webPath)
    {
        Formation_MediaServer::checkRealPath($realPath);
        $this->_realPath = $realPath;
        $this->_webPath = $webPath;
        $this->setUpPathes();
    }
    public function getMessages($asString = False)
    {
        if($asString)
        {
            return join("\n", $this->_messages);
        }
        return $this->_messages;
    }
    public function hasMessages()
    {
        return ( count($this->_messages) > 0 );
    }
    public function setUpPathes()
    {
        foreach(array_keys($this->_resources) as $dirName1)
        {
            foreach(array_keys($this->_resources[$dirName1]) as $dirName2)
            {
                $dir = $this->_realPath.'/'.$dirName1.'/'.$dirName2;
                /*
                * http://de2.php.net/manual/de/function.mkdir.php#33513
                *
                * The created folder actually has permissions of 0755, instead of the specified
                * 0777. Why is this you ask? Because of umask(): http://www.php.net/umask
                *
                * The default value of umask, at least on my setup, is 18. Which is 22 octal, or
                * 0022. This means that when you use mkdir() to CHMOD the created folder to 0777,
                * PHP takes 0777 and substracts the current value of umask, in our case 0022, so
                * the result is 0755
                *
                *
                * $old_umask = umask(0);
                * umask($old_umask);
                */
                if( !is_dir($dir) && !mkdir($dir, 0777, true))
                {
                    throw new Formation_MediaServer_Exception(
                        sprintf(
                            '"%1$s" is no dir and can\'t be created',
                            htmlspecialchars($dir)
                        ), 500
                    );
                }
            }
        }
    }
    public function serve($pathInfo = '')
    {
        try
        {
            $resourceArray = is_array($pathInfo)
                ? array_values($pathInfo)
                : Formation_MediaServer::getResourceArray($pathInfo);
            $this->_dispatch($resourceArray);
        }
        catch(Exception $e)
        {
            //do here something useful

            //$e could hold some useful information
            //use that

            //like loggin the error
            //  and/or sending emails to the server admin/software developer
            if(APPLICATION_ENV === 'development')
            {
                echo $e->getCode();
                throw $e;
            }
            $this->ausgang($e->getCode(), $e);
        }
    }
    /*
     *
     * @$keys mixed string|array whitelist of keys from $_resources or all resources will be used
     */
    public function getSources()
    {
        /*
         * to not violate the interface
         */
        $keys = (func_num_args() > 0) ? func_get_arg(0) : Null;
        $keys = empty($keys) ? array_keys($this->_resources) : $keys;
        $keys = (!is_array($keys)) ? array($keys) : $keys;

        $return = array(array());//an empty array in $return that array_merge is happy
        foreach($keys as $resourceKey)
        {
            $return[] = $this->getSourcesOfResource($resourceKey);
        }
        return call_user_func_array('array_merge', $return);
    }
    public function getSourcesOfResource( $resourceKey )
    {
        if(!array_key_exists($resourceKey, $this->_resources))
        {
            throw new Formation_MediaServer_Exception(
                sprintf(
                    'resource Key %1$s not found.',
                    $resourceKey
                ), 500
            );
        }
        $return = array(array());//an empty array in $return that array_merge is happy
        foreach(array_keys($this->_resources[$resourceKey]) as $type)
        {
            $resource = $this->_getResource( array($resourceKey, $type) );
            /*
             * the empty string at the end is intended so $offset ends with a slash
             */
            $offset = join('/', array($resourceKey, $type, ''));
            $return[] = $resource->getSources( $offset );
        }
        return call_user_func_array('array_merge', $return);
    }
    public function sourceExists( $sourceString )
    {
        $resourceArray = is_array($sourceString)
            ? array_values($sourceString)
            : Formation_MediaServer::getResourceArray($sourceString);
        $resource = $this->_getResource($resourceArray);
        $resourceArray = array_slice($resourceArray, 2);
        $return = $resource->sourceExists($resourceArray[0]);
        
        if($resource->hasMessages())
        {
            $this->_messages = array_merge($this->_messages, $resource->getMessages());
        }
        return $return;
    }
    
    public function isSourceOfResource($source, $resources)
    {
        $this->_messages = array();
        if(!is_array($resources))
        {
            $resources = array($resources);
        }
        $resources = array_map('Formation_MediaServer::getResourceArray', $resources);
        $sourceArray = is_array($source)
            ? array_values($source)
            : Formation_MediaServer::getResourceArray($source);
        
        if(!$this->resourceExists($sourceArray))
        {
            return False;
        }
        if(!in_array(array_slice($sourceArray, 0, 1), $resources, True)
        && !in_array(array_slice($sourceArray, 0, 2), $resources, True)
        )
        {
            return False;
        }
        return $this->sourceExists( $sourceArray );
    }
    
    /*
     * checks if there is a key at
     * $this->_resources[$resourceArray[0]] called $resourceArray[1]
     */
    public function resourceExists( array $resourceArray )
    {
        if(count($resourceArray) < 2)
        {
            return False;
        }
        /*
         * an array with numerical indexes starting with 0 is expected
         */
        if(!array_key_exists($resourceArray[0],$this->_resources))
        {
            return False;
        }
        if(!array_key_exists($resourceArray[1],$this->_resources[$resourceArray[0]]))
        {
            return False;
        }
        return True;
    }
    public function getWebPath($source, $usage = Null)
    {
        $resourceArray = is_array($source)
            ? array_values($source)
            : Formation_MediaServer::getResourceArray($source);
        $resource = $this->_getResource($resourceArray);
        $resourceArray = array_slice($resourceArray, 2);
        return $resource->getWebPath($resourceArray, $usage);
    }
    protected function _getResource( array $resourceArray )
    {
        if(!$this->resourceExists($resourceArray))
        {
            throw new Formation_MediaServer_Exception(
                sprintf(
                    'resource not found (%1$s)',
                    htmlspecialchars(join(', ', $resourceArray))
                ), 404
            );
        }
        
        /*
         * an array with numerical indexes starting with 0 is expected
         */
        $className = $this->_resources[ $resourceArray[0] ][ $resourceArray[1] ];
        $offset = join('/', array(
            $resourceArray[0],$resourceArray[1]
        ));
        
        if( array_key_exists($offset, $this->_resourcesCache))
        {
            return $this->_resourcesCache[$offset];
        }
        
        /*
         * the call to realpath here is because i am sitting on a window$ machine here
         * realpath will translate the slashes to backslashes
         */
        $realPath = realpath(sprintf('%1$s/%2$s',$this->_realPath, $offset));
        $webPath = sprintf('%1$s/%2$s',$this->_webPath, $offset);
        $resource = new $className($realPath, $webPath);
        if(!($resource instanceof Formation_MediaServer_Interface))
        {
            throw new Formation_MediaServer_Exception(
                sprintf(
                    'resource %1$s does not implement %2$s.',
                    $className,
                    'Formation_MediaServer_Interface'
                ), 500
            );
        }
        $this->_resourcesCache[$offset] = $resource;
        return $resource;
    }

    protected function _dispatch( array $resourceArray )
    {
        $resource = $this->_getResource($resourceArray);
        /*
         * an array with numerical indexes starting with 0 is expected
         */
        $resourceArray = array_slice($resourceArray, 2);
        $resource->serve($resourceArray);
    }
    public function ausgang($code = 500, Exception $e = Null)
    {
        switch ( (int) $code)
        {
            case(200):
                //we don't set anything here it should be 200 anyway
                //header("HTTP/1.0 200 Ok");
                return True;
                break;
            case(404):
                header("HTTP/1.0 404 Not Found");
                break;
            case (500):
            default:
            //a code we don't no is a Server Error. That means our fault.
                header("HTTP/1.0 500 Internal Server Error");
                break;
        }
        if(APPLICATION_ENV === 'development' && $e !== Null)
        {
            print $message;
        }
        /*
         * exit with having set an appropriate http response code is considered succesful
         * but exit prevents the ob to be flushed
         * so let the programm end normal
         */
        return False;
        //exit(0);
    }
}
