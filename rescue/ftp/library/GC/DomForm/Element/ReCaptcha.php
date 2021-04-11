<?php
// this is a not so strict input field!
// value can be set with _setValue
// value is only checked for type with possibleVal()
// it's meant for dynamic filling after wasSent of form was called
require_once 'GC/DomForm/Element/AbstractInput.php';
class GC_DomForm_Element_ReCaptcha extends GC_DomForm_Element_String
{
    public function possibleVal($value)
    {
        return (is_string($value) || NULL === $value);
    }
}