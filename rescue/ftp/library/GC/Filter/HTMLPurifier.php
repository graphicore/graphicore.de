<?php
class GC_Filter_HTMLPurifier implements Zend_Filter_Interface
{
//filter with html purifier, use xhtml transitional and utf-8
//these are the defaults
    protected $_htmlPurifier = null;
    protected $_options = array();
    protected function _setConfig(HTMLPurifier_Config $config, array $options)
    {
        foreach ($options as $option)
        {
            for($i = 0; $i < 3; $i++)
            {
                if(!array_key_exists($i, $option))
                {
                    $option[$i] = Null;
                }
            }
            $config->set($option[0], $option[1], $option[2]);
        }
    }
    protected function _init(){}
    public function __construct(array $options = null)
    {
        //require_once 'HTMLPurifier.auto.php';
        //require_once 'HTMLPurifier/Bootstrap.php';
        require_once 'HTMLPurifier.autoload.php';//works, let's see if it causes trouble
        $config = Null;
        $this->_init();
        if(!empty($this->_options))
        {
            $config = HTMLPurifier_Config::createDefault();
            $this->_setConfig($config, $this->_options);
        }
        //passed options will overwrite build in options
        if (Null !== $options)
        {
            $config = (Null === $config) ? HTMLPurifier_Config::createDefault() : $config;
            $this->_setConfig($config, $options);
        }
        $this->_htmlPurifier = new HTMLPurifier($config);
    }
    public function filter($value)
    {
        return $this->_htmlPurifier->purify($value);
    }
}
