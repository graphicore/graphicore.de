<?php
//will produce an Element <Input type="text"
require_once 'GC/DomForm/Element/AbstractInput.php';
class GC_DomForm_Element_Text extends GC_DomForm_Element_AbstractInput
{
    protected $_type = 'text';
}