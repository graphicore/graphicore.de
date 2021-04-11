<?php
// interesting enough: the following form seems to submit global[submit]
// <input type="submit" value="Abschicken" name="global[submit]"/>
// <input type="submit" value="Abschicken2" name="global[submit2]"/>
// by default (pressing enter in a <input type="text" /> field)
//      [submit] => Abschicken
// while pressing global[submit2] will not submit name="global[submit]
//      [submit2] => Abschicken2
// seems like the first submit button does the race (firefox, opera, midori(webkit))
// ms ie does however submit no button if no button was pressed!
// input type='submit' is no boolean so...
// conclusion: don't rely on buttons beeing pressed or not!, if you do
// make the first one the button that causes less frustration? [bug me not!] [take all my money]
// TRY: a hidden field before the submit button with the same name?
// Possible: don't set a name attribute on the element these simple submit buttons
//      and make sure they are never set!(through some magic in here)
// Build something else to get data about a pressed button

//this is a hack to make the easiest submit button possible
//it will never be checked
require_once 'GC/DomForm/Element/AbstractInput.php';
require_once 'GC/DomForm/Interfaces/Boolean.php';
class GC_DomForm_Element_Submit extends GC_DomForm_Element_AbstractInput implements GC_DomForm_Interfaces_Boolean
{
    protected $_type = 'submit';
    protected $_whitelist = array('value','checked');
    //this is a workaround to make this appear to be a boolean
    //no value will be set but checked if the value was submitted
    protected function _setChecked($value){}
    protected function _getChecked(){return False;}
    protected function _getValue(){return Null;}
    //don't set a name => no value will be submitted
    protected function _setName(){}
    public function restore(){}
    public function possibleVal($value){
        return (Null === $value);
    }
}