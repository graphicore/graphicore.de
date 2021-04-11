<?php
class GC_Form_Helper
{
    //these "Helpers"  are actually no help but pretty hard to call
    static public function createCheckbox($names, $value, $checked = False, $label = NULL, $arrayKey = False ,$labelAfter = False)
    {
        if(!is_array($names))
        {
            $names = array(NULL,$names);
        }
        $checked = ($checked) ? True : False;
        $checkbox = array(
            'name' => $names[1],
            'type' => 'Checkbox',
            'value' => $value,
            'checked' => $checked,
        );
        if($name[0])
        {
            $checkbox['namespace'] = $names[0];
        }
        if($label !== NULL){
            $checkbox['label'] = $label;
        }
        if($arrayKey){
            $checkbox['arrayKey'] = $arrayKey;
        }
        if($labelAfter){
            $checkbox['labelAfter'] = $labelAfter;
        }
        return $checkbox;
    }
    static public function createCheckboxes($names,array $values, array $checked = array(), array $labels = array(), $labelAfter = False)
    {
        //we need a name
        if(!is_array($names))
        {
            $names = array(NULL,$names);
        }

        $checkboxes = array(
        //the first one we know already its necessary for elements of type checkboxes
            'name' => $names[1],
            'type' => 'Checkboxes',
        );
        if($name[0])
        {
            $checkbox['namespace'] = $names[0];
        }
        $elements = array($checkboxes);
        foreach($values as $key => $value)
        {
            $check = (in_array($value, $checked, True));
            $label = (isset($labes[$value]))? $labels[$value] : NULL;
            $arrayKey = (is_string($key) && !empty($key)) ? $key : False;

            $elements[] = self::createCheckbox(
                $names,
                $value,
                $check,
                $label,
                $arrayKey,
                $labelAfter
            );
        }
        return $elements;
    }
    public function makeList(array $elements, $skip = 0, $listTag = 'ul', $liTag = 'li')
    {
        //GC_DomForm::DZ, GC_DomForm::DZ_DEEPER, GC_DomForm::DZ_BACK
        $list = (is_array($listTag))? $listTag : array('tag' => $listTag);
        $li = (is_array($liTag))? $liTag : array('tag' => $liTag);
        $list['type'] = GC_DomForm::DZ_DEEPER;
        $li['type'] = GC_DomForm::DZ_DEEPER;
        $close = array('type' => GC_DomForm::DZ_BACK);
        $listedElements = array();
        foreach(array_keys($elements) as $key){
            if($skip > 0)
            {
                $listedElements[] = $elements[$key];
                $skip--;
                continue;
            }
            if($list){
                $listedElements[] = $list;
                $list = False;
            }
            $listedElements[] = $li;
            $listedElements[] = $elements[$key];
            $listedElements[] = $close;
        }
        $listedElements[] = $close;
        return $listedElements;
    }

}