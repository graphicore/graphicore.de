<?php
/**
 * 
 * Imind Library
 *
 * @category   Imind
 * @package    Imind_Controller
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * @category   Imind
 * @package    Imind_Controller
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class Imind_Controller_Action_Helper_BuildProxy extends Zend_Controller_Action_Helper_Abstract
{
    
    /**
     * Gets js/css files source and outputs them in one response 
     *  (@see Imind_View_Helper_HeadScriptProxy::merge or Imind_View_Helper_HeadLinkProxy::merge)
     *  Set gzip compression and expires header too
     * 
     * @param string|array $files
     * @param bool $compress
     * @param int $expires timestamp
     * @return void
     */
    public function direct($files, $compress = false, $expires = null) {
        if (is_string($files)) {
            $files = split(",", $files);
        } elseif (!is_array($files)) {
            // throw new
        }   
        if ($compress) {
            $acceptEncoding = $_SERVER["HTTP_ACCEPT_ENCODING"];
            if (strpos($acceptEncoding, 'x-gzip') !== false) {
                $compress = 'x-gzip'; 
            } elseif(strpos($acceptEncoding, 'gzip') !== false) {
                $compress = 'gzip'; 
            } else {
                $compress = false;
            }   
        }   
        $responseString = "";
        $responseLength = 0;
        $response = $this->getResponse();
        $files = array_map("trim", $files);
        if (count($files) > 0) {
            $type = Imind_Build_Library::getType($files[0]);
            $build = Imind_Context::getDefaultObject("Imind_Build");
            $buildPaths = $build->getBuildPaths($type);
            $charset = $build->getCharset();
            foreach ($files as $file) {
                if (strlen($file) > 0) {
                    foreach ($buildPaths as $libraryName=>$buildPath){
                        if (strpos($file,$buildPath["buildUrl"]) === 0) {
                            $filePath = $buildPath["buildDir"].substr($file, strlen($buildPath["buildUrl"]));
                            if (is_file($filePath)) {
                                $responseString .= file_get_contents($filePath);
                            }   
                            break;
                        }   
                    }   
                }   
            }   
            $responseLength = strlen($responseString);
            if ($responseLength > 0) {
                if ($compress) {
                    $response->setHeader("Content-Encoding", $compress);
                    $responseString = gzcompress($responseString, 9);
                    $responseString = substr($responseString, 0, $responseLength);
                    $responseString = "\x1f\x8b\x08\x00\x00\x00\x00\x00".$responseString;
                    $responseLength = strlen($responseString);
                }   
                if (isset($expires) && is_int($expires) && $expires > time()) {
                    $response->setHeader("Expires", gmdate("D, d M Y H:i:s", $expires)." GMT", true);
                }   
            }   
            switch($type) {
                case "css":
                    $response->setHeader("Content-Type", "text/css; charset=".$charset);
                    break;
                case "js":
                default:
                    $response->setHeader("Content-Type", "application/x-javascript; charset=".$charset);
                    break;
            }
        }
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
        $response->setHeader("Content-Length", $responseLength);
        $response->setBody($responseString);
        $response->sendResponse();
        exit;
    }
    
}
