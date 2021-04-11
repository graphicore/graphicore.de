<?php
/**
 * 
 * Imind Library
 *
 * @category   Imind
 * @package    Imind_View
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * @category   Imind
 * @package    Imind_View
 * @copyright  Copyright (c) iMind Ltd. (http://www.imind.hu)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class Imind_View_Helper_HeadLinkProxy extends Zend_View_Helper_HeadLink
{
    /**
     * @var string registry key
     */
    protected $_regKey = 'Imind_View_Helper_HeadLinkProxy';
    
    /**
     * Proxied items
     * @var array
     */
    protected $_proxyItems = array();
        
    /**
     * headLinkProxy() - View Helper Method
     *
     * Returns current object instance.
     *
     * @return Imind_View_Helper_HeadLinkProxy
     */
    public function headLinkProxy() {        
        return $this;
    }
    
    /**
     * Merge the proxied files' urls into one link tag
     *     <link href="$url?$paramName=/styles/files1.css,/styles/files2.css" media="screen" rel="stylesheet" type="text/css" >
     *     
     * @param string $url
     * @param string $paramName
     * @param string $placement
     * @return Imind_View_Helper_HeadLinkProxy
     */
    public function merge($url, $paramName='f', $placement='prepend') {
        $mergedSrc = $url."?".$paramName."=";
        $files = array();
        $indexes = array();
        $count = count($this);
        for ($i = 0; $i < $count; ++$i) {
            $item = $this[$i];
            if (in_array($item->href, $this->_proxyItems)) {
                $files[] = $item->href;
                $indexes[] = $i;
            }
        }
        if (count($indexes) > 0) {
            foreach ($indexes as $index) {
                unset($this[$index]);
            }
            switch ($placement) {
                case 'set':
                case 'prepend':
                case 'append':
                    $action = $placement;
                    break;
                default:
                    $action = 'prepend';
                    break;
            }
            $mergedSrc .= join(",", $files);
            $mergedItem = $this->createDataStylesheet(array($mergedSrc));
            $this->$action($mergedItem);
        }
        
        return $this;
    }
    
    /**
     * Overload method access
     *
     * Creates the following virtual methods:
     * - appendStylesheet($href, $library, $media, $conditionalStylesheet, $extras)
     * - offsetSetStylesheet($index, $library, $href, $media, $conditionalStylesheet, $extras)
     * - prependStylesheet($href, $library, $media, $conditionalStylesheet, $extras)
     * - setStylesheet($href, $library, $media, $conditionalStylesheet, $extras)
     * 
     * For others look in Zend_View_Helper_Headlink
     *
     * @param mixed $method
     * @param mixed $args
     * @return Imind_View_Helper_HeadLinkProxy
     */
    public function __call($method, $args)
    {
        if (preg_match('/^(?P<action>set|(ap|pre)pend|offsetSet)Stylesheet$/', $method, $matches)) {
            $action = $matches['action'];
            $offsetIndex = -1;
            if ('offsetSet' == $action) {
                $offsetIndex = 0;
            }
            $library = "default";
            if (isset($args[2+$offsetIndex])) {
                $library = (string) $args[2+$offsetIndex];
                unset($args[2+$offsetIndex]);
                $args = array_values($args);
            }
            $build = Imind_Context::getDefaultObject("Imind_Build");
            if (isset($build)) {
                $oldHref = $args[1+$offsetIndex];
                $args[1+$offsetIndex] = $build->proxy($args[1+$offsetIndex], $library);
                if ($oldHref !== $args[1+$offsetIndex] && !in_array($args[1+$offsetIndex], $this->_proxyItems)) {
                    $this->_proxyItems[] = $args[1+$offsetIndex];
                }
            }
            parent::__call($method, $args);
            return $this;
        }

        return parent::__call($method, $args);
    }
}
