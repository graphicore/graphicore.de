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
class Imind_Controller_Action_Helper_Raw extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Sets the headers
     *
     * @param string  $contentType
     * @param string  $contentEncoding
     * @param string  $contentLength
     * @param string  $contentName
     * @param boolean $contentDisposition
     * @return void
     */
    public function setHeaders($contentType, $contentEncoding = null, $contentLength = null,
        $contentName = null, $contentDisposition = false) {
        $response = $this->getResponse();
        if (!empty($contentEncoding)) {
            $contentType.=";charset=$contentEncoding";
        }
        if ($contentDisposition) {
            $response->setHeader("Content-Disposition", "attachment; filename=\"$contentName\"");
        } elseif (!empty($contentName)) {
            $contentType.=";name=$contentName";
        }
        if (!empty($contentType)) {
            $response->setHeader("Content-Type", "$contentType");
        }
        if (!empty($contentLength)) {
            $response->setHeader("Content-Length", $contentLength);
        }
    }

    /**
     * Strategy pattern: call helper as helper broker method
     *
     * Send the raw data to the output with header informations
     *
     * @param $data
     * @param $contentType
     * @param $contentEncoding
     * @param $contentLength
     * @param $contentName
     * @param $contentDisposition
     * @param $sendNow
     * @return mixed
     */
    public function direct($data, $contentType, $contentEncoding = null, $contentLength = null,
        $contentName = null, $contentDisposition = false, $sendNow = true) {
        $this->setHeaders($contentType, $contentEncoding, $contentLength,
            $contentName, $contentDisposition);
        $response = $this->getResponse();
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
        if ($sendNow) {
            $response->setBody($data);
            /**
             * Some hack for unit testing
             * Zend_Test_PHPUnit_ControllerTestCase sets json's suppressExit to true
             */
            $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
            if (!$json->suppressExit) {
                $response->sendResponse();
                exit;
            }
        }
        return $data;
    }
}