<?php
/**
 *
 * Imind Library
 *
 * @category   Imind
 * @package    Imind_Context
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license    http://library.imind.hu/licence/bsd
 */

/**
 * @see Imind_Context_Loader_Exception
 */
require_once 'Imind/Context/Loader/Exception.php';

/**
 * @category   Imind
 * @package    Imind_Context
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license    http://library.imind.hu/licence/bsd
 */
class Imind_Context_Loader_Xml extends Imind_Context_Loader {

    /** @var DomDocument the loaded xml */
    protected $_xml = null;
    
    /** @var array the loaded data */
    protected $_data = array();

    /**
     * Constructor
     *
     * @param mixed the data, that needs to be loaded
     * @param mixed the schema to validate to
     * @return mixed the object
     */
    protected function __construct($data,$schema=null) {
        $this->_xml = new DOMDocument();
        $this->_xml->preserveWhiteSpace=false;
        $this->_xml->validateOnParse=true;
        $this->validate($data,$schema);
        $this->_data = $this->_convertXmlToArray();
        unset($this->_xml);
    }
    
    /**
     * Returns a loaded data
     *
     * @return mixed the data that can be read by the context
     */
    public function get() {
        return $this->_data;
    }
    
    /**
     * Validate the data
     *
     * @param mixed the data
     * @param mixed the schema to validate to
     * @return bool is it valid
     */ 
    public function validate($data,$schema=null) {
        libxml_use_internal_errors(true);
        $this->_convertDataToXml($data);
        if ($schema !== null) {
            if (!is_file($schema)) {
                throw new Imind_Context_Loader_Exception("Schema not found ($schema)");
            }
            if (!$this->_xml->schemaValidate($schema)) {
                $errorString=$this->_getValidationError();
                throw new Imind_Context_Exception('Errors during schema validation: '.$errorString);
            }
        }
    }

    /**
     * Converts the incoming data to DomDocument
     *
     * @param mixed the data (DomDocument, SimpleXMLElement, string)
     * @return void
     */
    protected function _convertDataToXml($data) {
        if ($data instanceof DOMDocument) {
            // we need preserveWhiteSpace
            $this->_xml->loadXML($data->saveXML());
        } elseif ($data instanceof SimpleXMLElement) {
            $xmlString = $data->asXml();
            $this->_xml->loadXml($xmlString);
        } elseif (is_string($data)) {
            $data=$this->_parseData($data);
            $this->_xml->loadXml($data);
        } else {
            throw new Imind_Context_Loader_Exception('Unsupported data type for xml loading');
        }
        if (!isset($this->_xml->documentElement->firstChild)) {
            throw new Imind_Context_Loader_Exception('Error during conversion');
        }
    }
    
    /**
     * Parse a file or string
     *
     * @param string the incoming data or a filename that contains the data
     * @return string the parsed data
     */
    protected function _parseData($data) {
        if (is_file($data)) {
            $data=file_get_contents($data);
        }
        $xmlParser = xml_parser_create();
        xml_parser_set_option($xmlParser, XML_OPTION_CASE_FOLDING, 1);
        xml_parser_set_option($xmlParser, XML_OPTION_SKIP_WHITE, 1);
        xml_set_element_handler($xmlParser,"","");
        if (!xml_parse($xmlParser, $data)) {
            throw new Imind_Context_Loader_Exception("XML parse error: "
                .xml_error_string(xml_get_error_code($xmlParser))." at line "
                .xml_get_current_line_number($xmlParser));
        }
        xml_parser_free($xmlParser);
        return $data;
    }

    /**
     * Get any errors from validation
     *
     * @return string error's string
     */
    protected function _getValidationError() {
        $errors = libxml_get_errors();
        $errorString='';
        foreach ($errors as $error) {
            $errorString .= '<br/>\n';
            switch ($error->level) {
                case LIBXML_ERR_WARNING:
                    $errorString .= "<b>Warning $error->code</b>: ";
                    break;
                case LIBXML_ERR_ERROR:
                    $errorString .= "<b>Error $error->code</b>: ";
                    break;
                case LIBXML_ERR_FATAL:
                    $errorString .= "<b>Fatal Error $error->code</b>: ";
                    break;
            }
            $errorString .= trim($error->message);
            if ($error->file) {
                $errorString .= " in <b>$error->file</b>";
            }
            $errorString .= " on line <b>$error->line</b>\n";
        }
        libxml_clear_errors();
        return $errorString;
    }
    
    /**
     * Convert the xml to array
     *
     * @return array the array that can be read by context
     */
    protected function _convertXmlToArray() {
        $node = $this->_xml->documentElement;
        $array = $this->_convertAttributes($node);
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeName == "import") {
                $kind = $childNode->getAttribute("kind");
                $file = $childNode->getAttribute("file");
                if (isset($kind) && isset($file)) {
                    $loader = Imind_Context_Loader::factory($childNode->getAttribute("kind"),$childNode->getAttribute("file"));
                    $importArray = $loader->get();
                    $array = array_merge($importArray, $array);
                    if (isset($importArray["components"]) && isset($array["components"])) {
                        $array["components"] = array_merge($importArray["components"], $array["components"]);
                    }
                    if (isset($importArray["aliases"]) && isset($array["aliases"])) {
                        $array["aliases"] = array_merge($importArray["aliases"], $array["aliases"]);
                    }
                }
            } elseif ($childNode->nodeName == "components") {
                if ($childNode->hasChildNodes()) {
                    foreach ($childNode->childNodes as $componentNode) {
                        $component=$this->_convertComponent($componentNode);
                        if (isset($array["components"])) {
                            $array["components"] = array_merge($array["components"],$component);
                        } else {
                            $array["components"] = $component;
                        }
                    }
                }
            } elseif ($childNode->nodeName == "aliases") {
                $array["aliases"] = $this->_convertAliases($childNode);
            } elseif ($childNode->nodeType == XML_ELEMENT_NODE) {
                $array[$childNode->nodeName] = $this->_getNodeValue($childNode);
            }
        }
        //error_log(var_export($array, true));
        return $array;
    }
    
    /**
     * Convert an xml node to component array
     *
     * @param DomNode the node
     * @return array the component array
     */
    protected function _convertComponent($node) {
        $component = $this->_convertAttributes($node);
        $componentId=null;
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeName == "id") {
                    $componentId=$this->_getNodeValue($childNode);
                } elseif ($childNode->nodeName == "constructor") {
                    $component["constructor"] = $this->_convertArguments($childNode);
                } elseif ($childNode->nodeName == "setters") {
                    $component["setters"] = $this->_convertArguments($childNode);
                } elseif ($childNode->nodeName == "methods") {
                    if ($childNode->hasChildNodes()) {
                        foreach ($childNode->childNodes as $methodNode) {
                            if ($methodNode->nodeType == XML_ELEMENT_NODE) {
                                $component["methods"][$methodNode->getAttribute("name")] = $this->_convertArguments($methodNode);
                            }
                        }
                    }
                } elseif ($childNode->nodeType == XML_ELEMENT_NODE) {
                    $component[$childNode->nodeName] = $this->_getNodeValue($childNode);
                }
            }
        }
        if (isset($component["id"])) {
            $componentId = $component["id"];
            unset($component["id"]);
        }
        return array($componentId=>$component);
    }
    
    /**
     * Convert an xml node to an alias array
     *
     * @param DomNode the node
     * @return array the alias array
     */
    protected function _convertAliases($node) {
        $aliases=array();
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $childNode) {
                $aliases[$childNode->getAttribute("key")] = $this->_getNodeValue($childNode);
            }
        }
        return $aliases;
    }
    
    /**
     * Convert an xml node's attributes to component attributes
     *
     * @param DomNode the node
     * @return array the attributes array
     */
    protected function _convertAttributes($node) {
        $array=array();
        if ($node->nodeType == XML_ELEMENT_NODE && $node->hasAttributes()){ 
            $attributes = $node->attributes;
            foreach ($attributes as $index => $attribute)
            {
                $array[$attribute->name] = $attribute->value;
            }
        }
        return $array;
    }
    
    /**
     * Convert an xml node's childnodes to component arguments
     *
     * @param DomNode the node
     * @return array the argument array
     */
    protected function _convertArguments($node) {
        $arguments=array();
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType == XML_ELEMENT_NODE || $childNode->nodeType == XML_TEXT_NODE) {
                    $arguments[] = $this->_convertNodeToArray($childNode);
                }
            }
        }
        return $arguments;
    }
    
    /**
     * Convert an xml node to an array
     *
     * @param DomNode the node
     * @return array the array
     */
    protected function _convertNodeToArray($node) {
        $array = $this->_convertAttributes($node);
        if ($node->hasChildNodes() && $node->firstChild->nodeType != XML_TEXT_NODE) {
            $array["value"] = $this->_convertArguments($node->firstChild);
        } elseif (!isset($array["value"]) && $node->nodeValue != '') {
            $array["value"] = $node->nodeValue;
        }
        return $array;
    }
    
    /**
     * Gets the value of the node (nodeValue or a node named value)
     *
     * @param DomNode the node
     * @return string the value
     */
    protected function _getNodeValue($node) {
        if ($node->nodeType == XML_ELEMENT_NODE && $node->hasAttribute("value")) {
            return $node->getAttribute("value");
        } else {
            return $node->nodeValue;
        }
    }
}