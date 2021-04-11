<?php
require_once 'GC/SetterGetter/Abstract.php';
abstract Class GC_DomForm_Element_Abstract extends GC_SetterGetter_Abstract
{
    protected $_tag;
    protected $_parent;
    protected $_namespace;
    protected $_name;
    protected $_whitelist = array('value','messageBox');
    protected $_messageBox;
    public $conf = array('labelAfter'=>NULL);

    public $DOMElement;
    public $DOMLabel;
    public $DOM;

    public function __construct(array $description, GC_DomForm $parent)
    {
        //not needed yet
        //$this->_dropZone = $dropzone;
        $this->_parent = $parent;
        $this->_namespace = $description['namespace'];
        $this->_name = $description['name'];

        $this->_setConf($description);
        foreach(array('label','value') as $val)
        {
            if(!isset($description[$val]))
            {
                $description[$val] = NULL;
            }
        }
        $this->_makeLabel($description['label']);
        $this->_makeElement($description);
        $this->_putTogether();
        $this->_appendThis();
    }
    protected function _appendThis(){
        $this->_parent->dropZone->appendChild($this->DOM);
    }
    public function getName(){
        return $this->_namespace.'['.$this->_name.']';
    }
    protected function _setAttributes(array $attributes)
    {
        foreach($attributes as $name => $value)
        {
            $this->DOMElement->setAttribute($name, $value);
        }
    }
    protected function _setConf(array $description)
    {
        foreach(array_keys($this->conf) as $conf)
        {
            if(array_key_exists($conf,$description))
            {
                $this->conf[$conf] = $description[$conf];
            }
        }
    }
    protected function _putTogether(){
        if($this->DOMLabel)
        {
            $this->DOM = $this->DOMLabel;
            if($this->conf['labelAfter'])
            {
                //element before Labletext
                $this->DOM->insertBefore($this->DOMElement,$this->DOM->firstChild);
            }
            else
            {
                //element after Labletext
                $this->DOM->appendChild($this->DOMElement);
            }
        }
        else
        {
            //could be a nodelist? something grouping
            $this->DOM = $this->DOMElement;
            //throw new GC_DomForm_Element_Exception('not Implemented');

        }
    }
    abstract protected function _makeElement(array $description);
    abstract protected function _setValue($value);
    abstract protected function _getValue();
    abstract public function restore();
    abstract public function possibleVal($value);
    protected function _makeLabel($lableText){
        if($lableText === NULL){
            return;
        }
        $this->DOMLabel = $this->_parent->DOMcreateElement('label');
        //set LableText
        if(empty($lableText)){
            $lableText = '';
        }
        $this->DOMLabel->appendChild(new DOMText($lableText));
    }

    public function setMessages($messages)
    {
        $this->messageBox->setMessages(array($this->_name => implode("\n",$messages)), $this->_namespace);
    }
    protected function _setMessageBox(){}
    protected function _getMessageBox()
    {
        if(!$this->_messageBox && $this->DOM)
        {
            require_once 'GC/DomForm/Element/MessageBox.php';
            $this->_messageBox = new GC_DomForm_Element_MessageBox($this->_parent);
            $this->DOM->parentNode->insertBefore($this->_messageBox->DOM, $this->DOM);
        }
        else
        {
            $this->_messageBox = $this->_parent->messageBox;
        }
        return $this->_messageBox;
    }

}
