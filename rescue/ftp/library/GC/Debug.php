<?php
class GC_Debug extends Zend_Debug
{
    /**
     * It just changes the order and default values of Zend_Debug::Dump
     * @param  mixed  $var   The variable to dump.
     * @param  bool   $echo  OPTIONAL Echo output if true. Now defaulting to false
     * @param  string $label OPTIONAL Label to prepend to output.
     * @return string
     */
    protected static $_tidy = True;
    public static function dump($var, $echo=false, $label=null)
    {
        if(!self::$_tidy)
        {
            return parent::Dump($var, $label, $echo);
        }
        else
        {
            $config = array(
                'output-xhtml' => true,
                'clean' => true,
                'show-body-only' => true,
                'logical-emphasis' => true,
                'show-body-only' => true,
                'quote-nbsp' => false// http://framework.zend.com/issues/browse/ZF-9566
            );
            $tidy = new tidy();
            $tidy->parseString(parent::Dump($var, $label, false), $config, strtolower(preg_replace('#-#', '', ENCODING)));
            $tidy->cleanRepair();

            $result = (string) $tidy;
            if($echo)
            {
                echo $result;
            }
            return $result;
        }

    }

    protected static $_logger;
    public static function log($var)
    {
        if(!self::$_logger)
        {
            $writer = new Zend_Log_Writer_Firebug();
            self::$_logger = new Zend_Log($writer);
            $writer->setPriorityStyle(8, 'TRACE');
            self::$_logger->addPriority('BROKENBYDESIGN', 8);
        }
        self::$_logger->brokenbydesign(html_entity_decode(strip_tags(self::dump($var))));
    }
}
