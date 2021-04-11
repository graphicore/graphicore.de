<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: LessThan.php 8064 2008-02-16 10:58:39Z thomas $
 */


/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Custom_Validate_MediaServer_isSourceOfResource extends Zend_Validate_Abstract
{

    const NOT_SOURCE = 'notSourceOfResource';
    const MEDEIA_SERVER_MESSAGEGES = 'mediaServerMessages';
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_SOURCE => "'%value%' is no Source of the given Resources",
        self::MEDEIA_SERVER_MESSAGEGES => "%value%",
    );

    /**
     * Resources
     *
     * @var array
     */
    protected $_resources;
     /**
     * _mediaServer
     *
     * @var Formation_MediaServer_Gateway_Abstract
     */
    protected $_mediaServer;
    /**
     * Sets validator options
     *
     * @param  array $resources
     * @param  Formation_MediaServer_Gateway_Abstract $server
     * @return void
     */
    public function __construct(array $resources, $server)
    {
        $this->setResources($resources);
        $this->setMediaServer($server);
    }

    /**
     * Returns the allowed option
     *
     * @return Array
     */
    public function getResources()
    {
        return $this->_resources;
    }


    public function setMediaServer(Formation_MediaServer_Gateway_Abstract $server)
    {
        $this->_mediaServer = $server;
    }

    public function getMediaServer()
    {
        if(!$this->_mediaServer)
        {
            throw new Zend_Validate_Exception('there is no Mediaserver');
        }
        return $this->_mediaServer;
    }

    /**
     * Sets the available resources
     *
     * @param  array $resources
     * @return this Provides a fluent interface
     */
    public function setResources(array $resources)
    {
        if(!is_array($resources) || empty($resources))
        {
            throw new Exception('no resources is no good, ya');
        }

        $this->_resources = $resources;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is less than max option
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue((string) $value);
        
        $mediaServer = $this->getMediaServer();
        $resources = $this->getResources();
        if(!$mediaServer->isSourceOfResource($value, $resources))
        {
            if($mediaServer->hasMessages())
            {
                $this->_error(self::MEDEIA_SERVER_MESSAGEGES, $mediaServer->getMessages(True));
            }
            else
            {
                $this->_error(self::NOT_SOURCE);
            }
            return False;
        }
        return True;
    }

}
