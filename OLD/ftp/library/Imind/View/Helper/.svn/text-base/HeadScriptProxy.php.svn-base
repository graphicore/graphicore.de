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
class Imind_View_Helper_HeadScriptProxy extends Zend_View_Helper_HeadScript
{
    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'Imind_View_Helper_HeadScriptProxy';
    
    /**
     * Proxied items
     * @var array
     */
    protected $_proxyItems = array();
    
    /**
     * Return headScriptProxy object
     *
     * Returns headScriptProxy helper object.
     *
     * @return Imind_View_Helper_HeadScriptProxy
     */
    public function headScriptProxy() {
        return $this;
    }
    
    /**
     * Merge the proxied files' urls into one script tag
     *     <script type="text/javascript" src="$url?$paramName=/scripts/files1.js,/scripts/files2.js"></script>
     * 
     * @param string $url the url where this merged script src can be parsed
     * @param string $paramName the request paramname
     * @param string $placement (prepend, append)
     * @return Imind_View_Helper_HeadScriptProxy
     */
    public function merge($url, $paramName='f', $placement='prepend') {
        $mergedSrc = $url."?".$paramName."=";
        $files = array();
        $indexes = array();
        $count = count($this);
        for ($i = 0; $i < $count; ++$i) {
            $item = $this[$i];
            if (in_array($item->attributes['src'], $this->_proxyItems)) {
                $files[] = $item->attributes['src'];
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
            $mergedItem = $this->createData('text/javascript',array('src'=>$mergedSrc));
            $this->$action($mergedItem);
        }
        return $this;
    }
    
    /**
     * Overload method access
     *
     * Allows the following method calls:
     * - appendFile($src, $library = 'default', $type = 'text/javascript', $attrs = array())
     * - offsetSetFile($index, $src, $library = 'default', $type = 'text/javascript', $attrs = array())
     * - prependFile($src, $library = 'default', $type = 'text/javascript', $attrs = array())
     * - setFile($src, $library = 'default', $type = 'text/javascript', $attrs = array())
     * - appendScript($script, $type = 'text/javascript', $attrs = array())
     * - offsetSetScript($index, $src, $type = 'text/javascript', $attrs = array())
     * - prependScript($script, $type = 'text/javascript', $attrs = array())
     * - setScript($script, $type = 'text/javascript', $attrs = array())
     *
     * @param  string $method
     * @param  array $args
     * @return Imind_View_Helper_HeadScriptProxy
     */
    public function __call($method, $args) {
        if (preg_match('/^(?P<action>set|(ap|pre)pend|offsetSet)File$/', $method, $matches)) {
            $action  = $matches['action'];
            $type    = 'text/javascript';
            $attrs   = array();
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
                $oldSrc = $args[1+$offsetIndex];
                $args[1+$offsetIndex] = $build->proxy($args[1+$offsetIndex], $library);
                if ($oldSrc !== $args[1+$offsetIndex] && !in_array($args[1+$offsetIndex], $this->_proxyItems)) {
                    $this->_proxyItems[] = $args[1+$offsetIndex];
                }
            }
            if (!isset($args[2+$offsetIndex])) {
                $args[2+$offsetIndex] = $type;
            }
            if (!isset($args[3+$offsetIndex])) {
                $args[3+$offsetIndex] = $attrs;
            }
            parent::__call($method, $args);
            return $this;
        }
        return parent::__call($method, $args);
    }
    
}
