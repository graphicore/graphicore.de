<?php
require_once 'GC/SetterGetter/Abstract.php';
Class GC_DomForm_Element_MessageBox extends GC_SetterGetter_Abstract
{
    public $DOM;
    protected $_tag = 'ul';
    protected $_messageTag = 'li';
    protected $_parent;

    public function __construct(GC_DomForm $parent){
        $this->_parent = $parent;
        $this->DOM = $this->_parent->DOMcreateElement($this->_tag);
        $this->DOM->setAttribute('class','message_box');
    }
    public function setMessages($messages, $namespace = ''){
        if(!is_string($namespace))
        {
            throw new GC_DomForm_Element_Exception('$namespace must be string');
        }
        if(!empty($namespace))
        {
            $namespace = '<em>'.htmlspecialchars($namespace).'</em> ';
        }
        foreach($messages as $key => $message)
        {
            $container = $this->_parent->DOMcreateElement($this->_messageTag);
            $text = $this->_parent->doc->createDocumentFragment();
            $message = (!is_string($message)) ? implode(', ',$message) : $message;
            //use this line for debugging, it tells where the message comes from
            //$text->appendXML( $namespace.'<strong>'.htmlspecialchars($key).'</strong>: '.htmlspecialchars((string) $message));
            $text->appendXML(htmlspecialchars((string) $message));
            $container->appendChild($text);
            $this->DOM->appendChild($container);
        }
    }
}