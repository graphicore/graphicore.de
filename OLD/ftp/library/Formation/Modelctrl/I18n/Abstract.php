<?php
/*
 *
 * changelog
 * 2009/11/23
 *      created as copy from Backend_Model_Group
 *      added RULE_CREATE and RULE_CREATEI18N
 *      added RULE_UPDATE and RULE_UPDATEI18N
 *      added Sluggable behavior to the model
 *      changed update, create, map4Form etc. to work with the i18n Behavior
 * 2009/11/25
 *      made from Backend_Model_Profile
 * 2009/11/27
 *      added _beforeValidate and _afterValidate
 * 2009/12/01
 *      copied the has many handling from the Formation_Modelctrl_Simple_Abstract
 *             here is no support for I18N having Many relations
 *      a merge of the classes might be helpful
 * 2009/12/17
 *      added filter and validator namespaces for Formation in init()
 */
abstract class Formation_Modelctrl_I18n_Abstract extends GC_Modelctrl_Abstract
{
    const RULE_CREATE = 'create';
    const RULE_CREATEI18N = 'createI18n';
    const RULE_UPDATE = 'update';
    const RULE_UPDATEI18N = 'updateI18n';

    protected $_filterOptions = array('escapeFilter' => 'NoFilter');
    protected $_messages = array();
    protected $_dcModelName = Null;
    protected $_uniqueKeys = array(
        self::RULE_UPDATE => array(),
        self::RULE_UPDATEI18N => array(),
    );
    protected $_hasMany = array();//as long as empty($this->_hasMany) nothing will happen
    public function init()
    {
        parent::init();
        $this->_addFilterOptions(
            array(
                Zend_Filter_Input::FILTER_NAMESPACE => array('Formation_Filter'),
                Zend_Filter_Input::VALIDATOR_NAMESPACE => array('Formation_Validate'),
            )
        );
    }
    public function create(array $data, $editingLanguage, $namespaceInt = 'create', $namespaceI18n = 'createI18n')
    {
        $failed = False;
        $validations = array(
            self::RULE_CREATE => $namespaceInt,
            self::RULE_CREATEI18N => $namespaceI18n,
        );
        $model = Null;
        foreach($validations as $rulesKey => $namespace)
        {
            if(!array_key_exists($namespace, $data))
            {
                throw new GC_Modelctrl_Exception(
                    sprintf('$namespace %1$s is not present in $data but must be there.', $namespace));
            }
            if(!is_array($data[$namespace]))
            {
                throw new GC_Modelctrl_Exception(sprintf('$data[%1$s]must be array,'), $namespace);
            }
            $this->_beforeValidate($model, $rulesKey, $data[$namespace]);
            //$this->validate overwrites the old filter data
            if(!$this->validate($rulesKey, $data[$namespace]))
            {
                $failed = True;
                /*
                * used to be
                * $this->_messages[$namespace] = $this->getFilter()->getMessages();
                * but if there was a message for password2 we would delete it that way
                */
                $this->_messages[$namespace] = (array_key_exists($namespace, $this->_messages) && is_array($this->_messages[$namespace])) ? $this->_messages[$namespace] : array();
                $this->_messages[$namespace] = array_merge($this->_messages[$namespace],$this->getFilter()->getMessages());
            }
            $data[$namespace] = $this->getFilterData();
            $this->_afterValidate($model, $rulesKey, $data[$namespace]);
        }
        if($failed)
        {
            return False;
        }
        $model = new $this->_dcModelName();
        if(!empty($this->_hasMany))
        {
            $relationalData = array();
            foreach($this->_hasMany as $relations)
            {
                $relationalData[$relations['relation']] = $data[$namespaceInt][$relations['relation']];
                unset($data[$namespaceInt][$relations['relation']]);
            }
        }
        foreach($data[$namespaceInt] as $key => $value)
        {
            $model->$key = $value;
        }
        foreach($data[$namespaceI18n] as $key => $value)
        {
            $model->Translation[$editingLanguage]->$key = $value;
        }
        if(!$model->isValid(True))
        {
            $this->_messages[$namespaceInt][] = 'the doctrine model says it\'s not valid';
            $this->_messages[$namespaceInt][] = $model->getErrorStackAsString();
            return False;
        }
        try
        {
            $model->save();
            if(!empty($this->_hasMany))
            {
                $hadChanges = False;
                foreach($this->_hasMany as $relations)
                {
                    $this->_updateHasMany($model, $relations, $relationalData[$relations['relation']], $hadChanges);
                }
                if($hadChanges)
                {
                    $model->refreshRelated();
                }
            }
            return $model;
        }
        catch(Doctrine_Connection_Exception $e)
        {
            $this->_messages['Exception'][] = ''.get_class($e).': '.$e->getMessage();
            $this->_messages['Exception'][] = $model->getErrorStackAsString();
            return False;
        }
    }
    public function update($model, array $data, $editingLanguage, $namespaceInt = 'update', $namespaceI18n = 'updateI18n')
    {
        if(!is_object($model) || !($model instanceof $this->_dcModelName))
        {
            throw new GC_Modelctrl_Exception
            (
                sprintf('$dcModel is not an instance of %1$s but %2$s',
                $this->_dcModelName,
                (is_object($model) ? get_class($model) : gettype($model)))
            );
        }
        $failed = False;
        $validations = array(
            self::RULE_UPDATE => $namespaceInt,
            self::RULE_UPDATEI18N => $namespaceI18n,
        );
        //$this->validate should overwrite the old filter data
        $update = array(
            $namespaceInt => array(),
            $namespaceI18n => array(),
        );
        foreach($validations as $rulesKey => $namespace)
        {
            if(!array_key_exists($namespace, $data))
            {
                throw new GC_Modelctrl_Exception(sprintf('$namespace %1$s is not present in $data but must be there.'), $namespace, $data);
            }
            if(!is_array($data[$namespace]))
            {
                throw new GC_Modelctrl_Exception(sprintf('$data[%1$s]must be array,'), $namespace);
            }
            foreach($data[$namespace] as $key => $value)
            {
                //these keys are supposed to be PRESENCE_OPTIONAL
                if
                (
                    is_array($this->_uniqueKeys[$rulesKey])
                    &&  in_array($key, $this->_uniqueKeys[$rulesKey])
                    && (
                            (self::RULE_UPDATE === $rulesKey && $value !== $model->$key)
                            || (self::RULE_UPDATEI18N === $rulesKey && $model->Translation[$editingLanguage]->$key !== $value)
                    )
                )
                {
                    //if value changed we'll validate it
                    $update[$namespace][$key] = $value;
                }
                if
                (
                    //we don't want to change the models id
                    'id' === $key
                    || (
                        is_array($this->_uniqueKeys[$rulesKey])
                        && in_array($key, $this->_uniqueKeys[$rulesKey])
                    )
                )
                {
                    continue;
                }
                $update[$namespace][$key] = $value;
            }
            $this->_beforeValidate($model, $rulesKey, $update[$namespace]);

if($rulesKey !== 'update'){define('goody', $rulesKey);}
            $this->validate($rulesKey, $update[$namespace]);
            if(!$this->validate($rulesKey, $update[$namespace]))
            {
                $failed = True;
                /*
                 * used to be
                 * $this->_messages[$namespace] = $this->getFilter()->getMessages();
                 * but if there was a message for password2 we would delete it that way
                 */
                $this->_messages[$namespace] = (array_key_exists($namespace, $this->_messages) && is_array($this->_messages[$namespace])) ? $this->_messages[$namespace] : array();
                $this->_messages[$namespace] = array_merge($this->_messages[$namespace],$this->getFilter()->getMessages());
            }
            $data[$namespace] = $this->getFilterData();
            $this->_afterValidate($model, $rulesKey, $data[$namespace]);
        }
        if($failed)
        {
            return False;
        }
        if(!empty($this->_hasMany))
        {
            $hadChanges = False;
            foreach($this->_hasMany as $relations)
            {
                $this->_updateHasMany($model, $relations, $data[$namespaceInt][$relations['relation']], $hadChanges);
                unset($data[$namespaceInt][$relations['relation']]);
            }
            if($hadChanges)
            {
                $model->refreshRelated();
            }
        }
        foreach($data[$namespaceInt] as $key => $value)
        {
            $model->$key = $value;
        }

        foreach($data[$namespaceI18n] as $key => $value)
        {
            $model->Translation[$editingLanguage]->$key = $value;
        }
        if(!$model->isValid(True))
        {
            $this->_messages[$namespaceInt][] = 'the doctrine model says it\'s not valid';
            $this->_messages[$namespaceInt][] = $model->getErrorStackAsString();
            return False;
        }
        try
        {
            $model->save();
            return $model;
        }
        catch(Doctrine_Connection_Exception $e)
        {
            $this->_messages['Exception'][] = ''.get_class($e).': '.$e->getMessage();
            $this->_messages['Exception'][] = $model->getErrorStackAsString();
            return False;
        }
    }
    protected function _updateHasMany($model, $relationData, &$data, &$hadChanges)
    {
        if(!is_array($data))
        {
            $data = (array) $data;
        }
        /*
         * should make available:
         *      $relation //the key wich stores the relation in $model
         *      $class //the type (Doctrine Model) of which one related object is
         *      $local //the name of the id of the model in $refClass
         *      $foreign //the name of the id of the referenced object in $refClass
         *      $foreignId //the name of the id of the referenced model in $class
         *      $refClass // the referencing class where $local is the id of $model and $foreign is the id of $DcProject
         *
         * FIXME: it should be possible to obtain most of these from $model
         *
         */
        extract($relationData, EXTR_PREFIX_SAME, 'exists');
        $has = array();
        $delete = array();
        foreach($model[$relation] as $aRel)
        {
            //if $aRel[$foreignId] is not in data put it to delete
            if(!in_array($aRel[$foreignId], $data))
            {
                $delete[] = $aRel[$foreignId];
            }
            else
            {
                $has[] = $aRel[$foreignId];
            }
        }
        foreach($data as $key)
        {
            if(in_array($key, $has))
            {
                //no need to change anything
                continue;
            }
            $hadChanges = True;
            $rel = new $refClass();//$rel = new DcClientProject();
            $rel->$local = $model->id;
            $rel->$foreign = $key;
            $rel->save();
        }
        if(count($delete) > 0)
        {
            $hadChanges = True;
            $q = Doctrine_Query::create()
                ->delete($refClass)
                ->addWhere($local.' = ?', $model->id)
                ->whereIn($foreign, $delete)
                ->execute();
        }
    }
    protected function _beforeValidate($model, $rulesKey, &$data)
    {}
    protected function _afterValidate($model, $rulesKey, &$data)
    {}
    public function map4Form($model, $editingLanguage, $namespaceInt = 'update', $namespaceI18n = 'updateI18n')
    {
        if(!is_object($model) || !($model instanceof $this->_dcModelName))
        {
            throw new GC_Modelctrl_Exception
            (
                sprintf('$dcModel is not an instance of %1$s but %2$s',
                $this->_dcModelName,
                (is_object($model) ? get_class($model) : gettype($model)))
            );
        }
        $data[$namespaceInt] = $model->toArray();
        $data[$namespaceI18n] = isset($data[$namespaceInt]['Translation'][$editingLanguage])
            ? $data[$namespaceInt]['Translation'][$editingLanguage]
            : array()
        ;
        unset($data[$namespaceInt]['Translation']);

        if(!empty($this->_hasMany))
        {
            //this is just flatening the keys to work with the select classes of GC_DomForm_Subset::setDefaults
            //here is no support for I18N having Many relations
            foreach($this->_hasMany as $relation)
            {
                $new = array();
                foreach(array_keys($data[$namespaceInt][$relation['relation']]) as $key)
                {
                    $data[$namespaceInt]
                        [$relation['relation']]
                        [$key]
                            = $data[$namespaceInt]
                                [$relation['relation']]
                                [$key]
                                [$relation['foreignId']];
                }
            }
        }
        return $data;
    }
    public function getMessages()
    {
         return $this->_messages;
    }
}
