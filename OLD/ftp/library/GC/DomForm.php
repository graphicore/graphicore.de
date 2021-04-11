<?php
require_once 'GC/SetterGetter/Abstract.php';
Class GC_DomForm extends GC_SetterGetter_Abstract
{
    const DEFAULT_NS = 'global';
    const I18NNAMESPACESUFFIX = 'I18n';
    const NO_FORM_ELEMENT = '!';
    const DZ            = '!dropZone';//creates a new dropzone in the form element
    const DZ_DEEPER     = '!./dropZone';//creates a new dropzone in dropzone,
    const DZ_RESTORE    = '!/dropZone';//restores the root dropzone
    const DZ_BACK       = '!../dropZone';//goes back from the curren dropzone to the last one
    const CHILD         = '!child';

    const TRUEVAL = '1';
    const FALSEVAL = '0';

    protected $_whitelist = array('formatOutput','action','defaultNS','namespace', 'namespaceI18n','messageBox');
    protected $_dropZones = array();
    protected $_messageBox;
    protected $_defaultNS = GC_DomForm::DEFAULT_NS;
    protected $_elements = array();
    protected $_docElementTag = 'form';
    protected $_namespaceI18n = Null;
    protected $_defaultAtrributes = array
    (
        'enctype'   => 'application/x-www-form-urlencoded',//'multipart/form-data' if with file upload// 'text/plain' is also possible
        'method'    => 'post',
        'action'    => '',
    );
    protected $_defaultFormatOutput = False;/*there are some problems with stylesheet styling and whitespace sometimes, so we don't indent the form by default*/
    protected $_elementClassPrefix = 'GC_DomForm_Element_';
    protected $_elementClassPath ='GC/DomForm/Element/';

    public $doc;//new DOMDocument('1.0', ENCODING);//
    public $docElement;
    public $dropZone;
    public $elements = array();

    public $isXML = True;//make this False if the document will not be XHTML (with a content-type like application/xhtml+xml)
    protected function _setFormatOutput($val)
    {
        $this->doc->formatOutput = ($val) ? True : False;
    }
    protected function _getFormatOutput()
    {
        return $this->doc->formatOutput;
    }
    protected function _setAction($val)
    {
        $this->setOptions( array('action' => $val) );
    }
    public function setAction($val)
    {
        $this->action = $val;
        return $this;
    }

    protected function _getAction()
    {
        return $this->docElement->getAttribute('action');
    }
    protected function _setDefaultNS($val = NULL)
    {
        if($val === NULL)
        //reset
        {
            $this->_defaultNS = self::DEFAULT_NS;
            return;
        }
        $this->_defaultNS = (is_string($val) && !empty($val)) ? $val : self::DEFAULT_NS;
        if($this->_defaultNS !== $val)
        {
            require_once 'GC/DomForm/Exception.php';
            throw new GC_DomForm_Exception('defaultNs must be a not Empty string');
        }
    }
    //the namespace Getter and Setter do the same like the defaultNS Getter and Setter
    protected function _setNamespace($val)
    {
        $this->defaultNS = $val;
    }
    protected function _getDefaultNS()
    {
        return $this->_defaultNS;
    }
    protected function _getNamespace()
    {
        return $this->_defaultNS;
    }
    protected function _setNamespaceI18n($var)
    {
        if(Null !== $var || (!is_string($var) || empty($var)))
        {
            throw new GC_DomForm_Exception('namespaceI18n needs to be a not empty string or Null');
        }
        $this->_namespaceI18n = $var;
    }
    protected function _getNamespaceI18n()
    {
        return ($this->_namespaceI18n === Null)
            ? $this->namespace.GC_DomForm::I18NNAMESPACESUFFIX
            : $this->_namespaceI18n
        ;
    }

    public function __construct(array $options = NULL)
    {
        $this->doc = new DOMDocument();//
        $this->docElement = $this->DOMcreateElement($this->_docElementTag);
        $this->formatOutput = $this->_defaultFormatOutput;
        //dropZone will later hold things like Fieldsets or other Boxes
        //where the Elements will live in
        //dropZone will be replaced buy those Elements
        //of course, a dropZone should be appended to docElement
        $this->dropZone = $this->docElement;
        $this->doc->appendChild($this->docElement);
        $this->setOptions($this->_defaultAtrributes);
        $this->setOptions($options);

        $this->init();
    }

    protected function init()
    {}

    public function __toString()
    {
        if(!$this->isXML){
            return  preg_replace(
                '/^<!DOCTYPE.+?>/',
                 '',
                 str_replace(array('<html>', '</html>', '<body>', '</body>'),
                array('', '', '', ''),
                $this->doc->saveHTML())
                );
        }
        return $this->doc->saveXML($this->docElement);
    }
    public function output()
    {
        if(!$this->isXML)
        {
            echo  preg_replace(
                '/^<!DOCTYPE.+?>/',
                 '',
                 str_replace(array('<html>', '</html>', '<body>', '</body>'),
                array('', '', '', ''),
                $this->doc->saveHTML())
                );
        }
        //echo $this->doc->saveHTML($this->docElement);
        echo $this->doc->saveXML($this->docElement);
    }
    public function setOptions(array $options = NULL)
    {
        if($options !== NULL){
            foreach ($options as $key => $val){
                $this->docElement->setAttribute($key, $val);
            }
        }
        return $this;
    }
    public function DOMcreateElement($tag)
    {
        return $this->doc->createElement($tag);
    }

    //somehow there is a need for namespaces etc
    //the name tag should be used here! since that one is the key for the data anyway
    //names are then like name, form[name] my[big][array][]
    //login[password]
    //list[values][]
    //
    //the following is scary:
    //list[][objectName]
    //list[][objectValueOne]
    //list[][objectProperty]
    //and would produce:
    //array(0 => array('objectName'=> value),1=>array('objectValueOne' => value))
    /* array(
        'name' => 'name'
        'namespace' => 'namespace'
        'asArray' => true,//will do this only if i need it when i need it
        'key' => //se asArray,
        )
    if!namespace
        namespace = global//
        name = name
    if('namespace') name = namespace[name]
    if array name = name[] || namespace[name][]
        if key name = name[key] ||namespace[name][key]
    */



    //the element description has now
    /*
    $element = array(
        'name' => 'textfield',
        'type' => 'Text'
        'value' => 'Hello World',
        'label' => 'Text Element Label'
        'labelAfter' => false
    ),
    */
    //$element['namespace']//defaults to self::DEFAULT_NS and must not be set
    //$element['name']//not empty string
    //$element['ClassPrefix'],//defaults to $this->_elementClassPrefix
    //$element['type'],// string // the first character should be uppercase
    //      no magic here, if its lowercase it will stay lowercase
    //      the element class will be $element['ClassPrefix'].$element['type']
    //
    //$element['label'] // the text of the label
    //$element['value'] // the text of the value
    //$element['labelAfter']//not set by default the labelText is before, if($element['labelAfter']) the label is after the element
    // added only for checkboxes right now:
    //$element['isArray'] //will produce namespace[name][]
    //$element['arrayKey']//if is array will produce namespace[name][arrayKey]

    public function addElements(array $elements){
        foreach($elements as $element){
            $this->addElement($element);
        }
        return $this;
    }
    public function addElement(array $element){
        if(is_string($element['type'])
            && self::NO_FORM_ELEMENT === substr($element['type'], 0, strlen(self::NO_FORM_ELEMENT))
            )
        {
            if(in_array($element['type'], array(self::DZ, self::DZ_DEEPER, self::DZ_RESTORE, self::DZ_BACK), True))
            {
                $this->makeDropZone($element);
                return;
            }
            if($element['type'] === self::CHILD)
            {
                $this->makeChild($element);
                return;
            }
        }
        $this->_makeName($element);
        $namespace = $element['namespace'];
        $name = $element['name'];
        if(!array_key_exists($namespace,$this->_elements))
        {
            $this->_elements[$namespace] = array();
        }
        //checkboxes require the same name and namespace if they belong together
        //checkboxes might be spread over the whole form
        //radiobuttons(of course others too) may be submitted as array too

        if(array_key_exists($name,$this->_elements[$namespace]))
        {
            //if there is an Element with a addElement
            //method_exists and is_callable will return true for protected/private functions too
            if($this->_elements[$namespace][$name] instanceof GC_DomForm_Interfaces_Multiplier)
            {
                //this will append the made element where ever it wants
                $this->_elements[$namespace][$name]->addElement($element);
            }else{
                require_once 'GC/DomForm/Exception.php';
                throw new GC_DomForm_Exception('An element "'.$name.'" in namespace "'.$namespace.'" exists already');
            }

        }
        else
        {
            $this->_elements[$namespace][$name] = $this->makeElement($element);
            if($this->_elements[$namespace][$name]->DOM
                && $this->_elements[$namespace][$name]->DOM instanceof DOMNode)
            {
                //$this->dropZone->appendChild($this->_elements[$namespace][$name]->DOM);
                //$this->_elements[$namespace][$name]->dropZone = $this->dropZone;
            }

        }
        return $this;
    }

    public function makeDropZone(array $element)
    {
        //self::DZ, self::DZ_DEEPER, self::DZ_BACK
        if($element['type'] === self::DZ_BACK)
        {
            //pop the latest dropzone from the dropzone stack and return
            if(empty($this->_dropZones))
            {
                throw new GC_DomForm_Exception('There is no dropzone to go back');
            }
            $this->dropZone = array_pop($this->_dropZones);
            return;
        }
        if($element['type'] === self::DZ_RESTORE)
        {
            //dropzone is the first element in dropzonestack or dropzone
            //clear the stack and return
            $this->dropZone = (!empty($this->_dropZones)) ? $this->_dropZones[0] : $this->dropZone;
            $this->_dropZones = array();
            return;
        }
        if($element['type'] === self::DZ_DEEPER)
        {
            //push dropzone on the stack
            //create a new dropzone
            array_push($this->_dropZones,$this->dropZone);
            $this->dropZone->appendChild($this->DOMcreateElement($element['tag']));
            $this->dropZone = $this->dropZone->lastChild;
        }
        if($element['type'] === self::DZ)
        {
            //clear the stack
            //create a new dropzone
            $this->_dropZones = array();
            $this->dropZone = $this->DOMcreateElement($element['tag']);
            $this->docElement->appendChild($this->dropZone);
        }


        if(array_key_exists('attributes', $element) && is_array($element['attributes']))
        {
            foreach($element['attributes'] as $attr => $content)
            {
                $this->dropZone->setAttribute($attr, $content);
            }
        }
    }

    //if $element['type'] => '!child'
    //if !$element['tag']
    //    if$element['text']=> make a textNode
    //$element['tag'] => tagname
    //$element['attributes'] => array('attribute name' => 'attribute content')
    //$element['text'] => textContent
    //$element['text'] => textIsXML
    public function makeChild($element)
    {
        $text = False;
        if(array_key_exists('text',$element) && $element['text'])
        {
            if(array_key_exists('textIsXML',$element) && $element['textIsXML'])
            {
                $text = $this->doc->createDocumentFragment();
                $text->appendXML($element['text']);
            }
            else
            {
                $text = new DOMText($element['text']);
            }
        }

        if(!array_key_exists('tag',$element))
        {
            if($text)
            {
                $child = $text;
            }
        }
        else
        {
            $child = $this->DOMcreateElement($element['tag']);
            if(array_key_exists('attributes', $element) && is_array($element['attributes']))
            {
                foreach($element['attributes'] as $attr => $content)
                {
                    $child->setAttribute($attr, $content);
                }
            }
            if($text){
                $child->appendChild($text);
            }
        }
        $this->dropZone->appendChild($child);
    }
    //?add a inbetween class DomFormNS that handles just one namespace?
    //      this class would handle the DomFormNS-ses and provide accsess to them
    //      thus complexity would be shared.
    //otherwise, a DomFormNS would group all Elements of NS together, taking flexibillity from the System


    public function makeElement(array $element)
    {
        //something like the plugin System of the Zend framework could Help here, to build custom Elements Somewhere else
        if(isset($element['ClassPrefix']))
        {
            //if this isset the autoloader shuld be aware of what is to do! or the class is loaded already
            $class = $element['ClassPrefix'].$element['type'];
        }
        else
        {
            $class = $this->_elementClassPrefix.$element['type'];
            require_once $this->_elementClassPath.$element['type'].'.php';
        }
        return new $class($element,$this);
    }
    //every name without namespace will be global[name](self::DEFAULT_NS[name])
    protected function _makeName(array &$element)
    {
        if(!array_key_exists('namespace', $element) || empty($element['namespace']))
        {
            $element['namespace'] = $this->defaultNS;
        }
        foreach(array('namespace','name') as $val)
        {
            if(!array_key_exists($val, $element) || !is_string($element[$val]) || empty($element[$val]))
            {
                require_once 'GC/DomForm/Exception.php';
                throw new GC_DomForm_Exception('$element['.$val.'] must be set, string and not empty'.GC_Debug::Dump($element,False,'$element'));
            }
        }
    }
    //return values as array
    //all fields will give a value back...?
    //no magic here! if a value was not set it will not be returned
    //  because the model does the validation and will have to handle not set values anyway
    public function getValues($singleNs = NULL)
    {
        $singleNs = ( is_string($singleNs) && !empty($singleNs) ) ? $singleNs : False;
        $array = array();
        foreach($this->_elements as $ns => $elements)
        {
            //we want only one namespace, skip all other
            if($singleNs && $singleNs !== $ns)
            {
                continue;
            }
            if(!array_key_exists($ns,$array))
            {
                $array[$ns] = array();
            }
            foreach($elements as $name => $element)
            {
                //GC_DomForm_Interfaces_Boolean defines nothing but classes that implement it
                //have values that are set or not
                if($element instanceof GC_DomForm_Interfaces_Boolean && !$element->checked)
                {
                    continue;
                }
                if(NULL !== $element->value){
                    $array[$ns][$name] = $element->value;
                }
                if(($element instanceof GC_DomForm_Interfaces_Multiplier)
                    && empty($array[$ns][$name])
                    && !is_string($array[$ns][$name])// empty string is a valid value i think, so if there is an element checked with an empty string as value, that should be returned
                    )
                {
                    unset($array[$ns][$name]);
                }
            }
            if(empty($array[$ns])){
                unset($array[$ns]);
            }
        }


        if($singleNs)
        //we want only one namespace
        {
            $array = array_key_exists($singleNs, $array) ? $array[$singleNs] : array();

        }
        return $array;
    }
    //there are at least three kinds of fields:
    //
    // static
    // hidden:
    // must be set and we know the value
    //
    // AND
    //
    // determined:
    // checkboxes, radiobuttons, select lists, (submitbuttons)
    // these are set or not, but we know all possible values here
    // with checkboxes we can say that at least one of a group must be set (not set is not possible)
    //
    // AND
    //
    // undetermined:
    // text, textarea, (?file upload),
    // the value is always set if the form was submitted but can be empty or everything else

    //currently here is no check if the given array is plausible!
    //      if i give this form an empty array it will return an empty array
    //      a check for plausibillity would include if it is possible that there was nothing sent by this form
    //      e.g.
    //      a hidden field cant be not set nor have another value
    //      a checkbox or a group of checkboxes can't be not set if there was a selected checkbox
    //      a textfield or textarea must have a set value at least ''
    //      etc.

    // FIXME: ?Treat Not Set as a Value!

    //takes an array like the $request->getPost() array
    //only existing fields will be set
    //no validation here
    //this is used like "setDefaults", too, since no filtering is done here

    //beware of odd behavior with e.g. selectboxes
    //the result of a multiple select would be an array, but only with the selected entries
    //NOT with all available options!
    //there is of course no problem with "input type text" wehre allways only one value is provided

    //maybe a setDefaults() method is needed here, since we will only get the selectd values back
        //setDefaults() can be implemented on a custom basis
    public function setValue($value, $name ,$ns = Null)
    {
        $ns = (isset($ns)) ? $ns : $this->defaultNS;
        if(!$this->namespaceExists(array($ns))
            || (!array_key_exists($ns,$this->_elements)
            || !array_key_exists($name,$this->_elements[$ns])))
        {
            throw new GC_DomForm_Exception('the element "'.$ns.'['.$name.']" does not exist');
        }
        $element = $this->_elements[$ns][$name];
        if($element instanceof GC_DomForm_Interfaces_Boolean)
        {
            $element->checked = ($element->value === $value);
        }
        $element->value = $value;
    }
    public function getValue($name ,$ns = Null)
    {
        $ns = (isset($ns)) ? $ns : $this->defaultNS;
        if(!$this->namespaceExists(array($ns))
            || (!array_key_exists($ns,$this->_elements)
            || !array_key_exists($name,$this->_elements[$ns])))
        {
            throw new GC_DomForm_Exception('the element "'.$ns.'['.$name.']" does not exist');
        }
        return $this->_elements[$ns][$name]->value;
    }

    public function getElement($name ,$ns = Null)
    {
        $ns = (isset($ns)) ? $ns : $this->defaultNS;
        if(!$this->namespaceExists(array($ns))
            || (!array_key_exists($ns,$this->_elements)
            || !array_key_exists($name,$this->_elements[$ns])))
        {
            throw new GC_DomForm_Exception('the element "'.$ns.'['.$name.']" does not exist');
        }
        return $this->_elements[$ns][$name];
    }

    public function setValues(array $values, $singleNs = Null){
        if($singleNs !== NULL)
        {
            $singleNs = ( is_string($singleNs) && !empty($singleNs) )? $singleNs : False;
            if(!$singleNs)
            {
                require_once 'GC/DomForm/Exception.php';
                throw new GC_DomForm_Exception('$singleNs was set but not string or empty.');
            }
            $values = array($singleNs => $values);
        }


        //this will filter Posted values
        //that means:
        // it filters by possible values that might have been submitted by the form
        // if nothing was submitted by this form
        // and $_POST == an empty array is given as value , this will return an
        // e.g. setValues(array())
        // array() will be returned by getValues()
        // if values are given, but they don't fit to the behavior of the according element
        // that value will be treated as not submitted
        // e.g.
        //      if a <input type='checkbox' name='global[box]' value='MyVal'/>
        //      returns $_POST['global']['box'] = 'anotherVal'
        //      the key ['box'] will not be set in $_POST['global']
        //      because a not checked checkbox does not create any key
        //      and a checked checkbox does not change its value
        //
        // another way would be to fill values with there defaults if something had to be submitted
        // e.g. a multiplier of radio buttons at least one was marked as checked
        // there is no way to uncheck it in the browser without extra tools (web dev toolbar or firebug)

        //we go through the elements since the element count is determined
        //looping through $values would mean to go through everything the user submits
        foreach(array_keys($this->_elements) as $ns)
        {
            if($singleNs && $singleNs !== $ns)
            {
                continue;
            }
            foreach($this->_elements[$ns] as $name => $element)
            {

                // interesting enough: the following form seems to submit global[submit] by default (pressing enter in a <input type="text" /> field)
                //      [submit] => Abschicken
                // while pressing global[submit2] will not submit name="global[submit]
                //      [submit2] => Abschicken2
                // seems like the first submit button does the race (firefox, opera, midori(webkit))
                // ms ie does however submit no button if no button was pressed!
                // <input type="submit" value="Abschicken" name="global[submit]"/>
                // <input type="submit" value="Abschicken2" name="global[submit2]"/>
                // input type='submit' is a false boolean so...
                // conclusion: don't rely on buttons beeing pressed or not!, if you do make the first one the button that causes less frustration? [bug me not!] [take all my money]
                // TRY: a hidden field before the submit button with the same name?
                // Possible: don't set a name attribute on the element these simple submit buttons
                //      and make sure they are never set!(through some magic in here)
                // Build something else to get data about a pressed button
                if($element instanceof GC_DomForm_Interfaces_Boolean)
                //checkbox, radio, submit
                //they are checked if $_POST['namespace']['value'] exists and if it is the elements value
                {
                    $element->checked = (
                        array_key_exists($ns,$values)
                        && array_key_exists($name,$values[$ns])
                        && $element->value === $values[$ns][$name]
                        );

                }
                else if(array_key_exists($ns,$values)
                        && array_key_exists($name, $values[$ns]))
                // everything else will handle the value itself
                //
                {
                    $element->value = $values[$ns][$name];
                }
                else//not submitted fields will be empty if($element instanceof GC_DomForm_Interfaces_Multiplier)
                //this will make text and textarea have empty strings as values
                //if thats not good a validator has to bark
                //if a Multiplier was not submitted that one has to handle it
                //a class implementing GC_DomForm_Interfaces_Multiplier(currently Checkboxes and Radios) will uncheck all values and return something empty (array() or "")
                {
                    $element->value = NULL;
                }
            }
        }
        return $this;
    }

    //a form was sent if
    //  1. in $_POST is nothing else than possibly could have been sent
    //  2. all namespaces in _Post are keys in $this->_elements
    //  3. all values in form that had to be set are set//hidden types, radio buttons
    //  4. all values that have been sent are possible values
    public function wasSent($post){
        if(!is_array($post))
        {
            return False;
        }
        if(!$this->namespaceExists(array_keys($post)))
        {
            return False;
        }
        //ask every value if
        //  it had to be set (if NULL as value is possible)
        //  if value is possible
        // is-

        foreach(array_keys($this->_elements) as $ns)
        {
            foreach($this->_elements[$ns] as $name => $element)
            {
                $value = (array_key_exists($ns, $post)
                    && is_array($post[$ns])
                    && array_key_exists($name, $post[$ns]))
                        ? $post[$ns][$name]
                        : NULL;
                if(!$element->possibleVal($value)){
                    return False;
                }
            }
        }
        return True;
    }
    public function namespaceExists($nsArray){
        $nsArray = (is_string($nsArray)) ? array($nsArray) : $nsArray;
        foreach ($nsArray as $ns)
        {
            if(!array_key_exists($ns,$this->_elements))
            {
                return False;
            }
        }
        return True;
    }

    public function restore(){
        foreach(array_keys($this->_elements) as $ns)
        {
            foreach($this->_elements[$ns] as $name => $element)
            {
                $element->restore();
            }
        }
        return $this;
    }
    //set Validation messages at the Element
    //therefore a method must be provided by each Element itself!
    public function setMessages(array $messages , $namespace = Null){
        if(is_string($namespace)){
            $messages = array($namespace => $messages);
        }

        foreach(array_keys($messages) as $ns)
        {
            foreach(array_keys($messages[$ns]) as $name){
                if(array_key_exists($ns,$this->_elements) && array_key_exists($name, $this->_elements[$ns])&& isset($this->_elements[$ns][$name])){
                        $this->_elements[$ns][$name]->setMessages($messages[$ns][$name]);
                        unset($messages[$ns][$name]);
                }
            }
            if(!empty($messages[$ns]))
            {
                $this->messageBox->setMessages($messages[$ns], $ns);
            }
            unset($messages[$ns]);
        }
        return $this;
    }
    protected function _setMessageBox(){}
    protected function _getMessageBox(){
        if(!$this->_messageBox){
            require_once 'GC/DomForm/Element/MessageBox.php';
            $this->_messageBox = new GC_DomForm_Element_MessageBox($this);
            $this->docElement->insertBefore($this->_messageBox->DOM, $this->docElement->firstChild);
        }
        return $this->_messageBox;
    }




}
