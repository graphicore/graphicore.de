<?php
//like checkboxes and radiobuttons that are either checked or not
interface GC_DomForm_Interfaces_Boolean{}
// this can't be described with an php interface! so this interface is symbolic and "convention"
// Conventions
//
//
// protected $_whitelist = array('value','checked');
// protected $_silentGetterFail = array('DOM');
// protected function _setChecked($value)
// protected function _getChecked()
//
// because the interface issue is solved via getters and setters
// and these are currently "hidden" //FIXME: make them public?
// important is the 'checked' in $_whitelist! and the
// according functions _setChecked() and _getChecked
// the 'value' in $_whitelist is from GC_DomForm_Element_Abstract

