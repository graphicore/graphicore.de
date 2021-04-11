<?php
require_once 'GC/DomForm/Element/Abstract.php';
//if used in XML parsing Mode dont give it empty elements that are not made to bve empty in html
//eg '<span style="color:purple"><span>' will become '<span style="color:purple"/>'
//what is in fact valid xhtml markup i guess
//if "Content-Type: application/xhtml+xml is set in the header firefox does anything correct
//however if its text/html the rendering is broken
//Content negotioation will help here...
class GC_DomForm_Element_String extends GC_DomForm_Element_Abstract
{
    protected $_tag = '';
    protected $_restoreValue;
    protected $_value;
    protected $_isXml = False;
    protected $_nodes = array();
    protected $_dropZone;
    protected function _appendThis()
    {
        $this->_dropZone->appendChild($this->DOM);
    }
    public function __construct(array $description, GC_DomForm $parent)
    {
        $this->_dropZone = $parent->dropZone;
        parent::__construct($description,$parent);
    }

    protected function _makeLabel($lableText){}
    protected function _makeElement(array $description)
    {
        $this->_isXml = (array_key_exists('isXML', $description)) ? $description['isXML'] : $this->_isXml;
        $this->DOMElement = ($this->_isXml)? NULL : new DOMText('');
        $this->_setValue($description['value']);
        $this->_restoreValue = $description['value'];
    }
    public function restore()
    {
        $this->_setValue($this->_restoreValue);
    }
    protected function _eraseChildNodes()
    {
        while(NULL !== $this->DOMElement->firstChild)
        {
            $this->DOMElement->deleteData(0,$this->DOMElement->length);
        }
    }
    protected function _setValue($value)
    {
        $this->_value = $value;
        //if its an array we assume its: array(format string, values
        //see sprintf for that
        if(is_array($value)){
            $value = call_user_func_array('sprintf', $value);
        }
        $value = (string) $value;

        if(!$this->_isXml){
            $this->DOMElement->replaceData(0, $this->DOMElement->length,$value);
            return;
        }
        //if($this->_isXml){

        // this is a small hack indeed
        // since we have to save the nodes produced by DOMDocumentFragment::appendXML
        // in here, to be able to replace them later
        // this->DOMElement and this->DOM are getting useless after the first appending
        // because the appending document is removing the children of the DOMDocumentFragment
        // and making them its children...
        // FIXME: a solution could be to give every element the power to append itself and to remove
        // itself.
        // then the parent elemtent couldn't be allowed to use things like the DOM or the DOMElement members

        // first could be done by using dropzone in the constructer of the element//done
        // seccond would need a new method like remove() making the element removing itself from its dropzone
        // $this->dropZone->removeChild($this->DOM)
        // a insertBefore and insertAfter and replace could be done that way too
        // but this is not needed now

        $text = $this->_parent->doc->createDocumentFragment();
        $text->appendXML($value);
        if($text->childNodes->length === 0)
        {
            //to ensure that there is always a $this->_nodes[0] 'firstChild'
            //think of it as a placeholder
            $text->appendChild(new DOMText(''));
        }
        //where the oldnodes end, we need them to remove em
        $length = count($this->_nodes);
        foreach($text->childNodes as $child)
        {
            $this->_nodes[] = $child;
        }
        if(!$this->DOMElement){
            //inital setting values
            $this->DOMElement = $text;
        }else{
            //insert the new nodes
            $this->_dropZone->insertBefore($text,$this->_nodes[0]);
            foreach($this->_nodes as $key => $child){
                if($length === $key){
                    //now the new nodes are starting
                    //remove the nodes and make new keys
                    array_splice($this->_nodes,0 ,$length);
                    break;
                }
                //remove the old node
                $this->_dropZone->removeChild($child);
            }
        }
    }
    protected function _getValue()
    {
        //these elements don't send anything, thus no value will be returned
        return NULL;
    }
    public function possibleVal($value)
    {
        //these elements don't send anything, thus no value is possible
        return (Null === $value);
    }
        protected function _getMessageBox()
    {
        if(!$this->_messageBox)
        {
            $this->_messageBox = $this->_parent->messageBox;
        }
        return $this->_messageBox;
    }
}