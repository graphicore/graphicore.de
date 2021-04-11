<?php
/*
 *
 * changelog
 * 2009/11/28
 *      created as copy of Formation_Modelctrl_I18n_Abstract
 *          and Backend_Model_Client
 * 2009/11/30
 *      created the rudimentary handling of has many relation manageing
 * 2009/12/17
 *      added filter and validator namespaces for Formation in init()
 */
abstract class Formation_Modelctrl_Simple_Abstract extends GC_Modelctrl_Abstract
{
    const RULE_CREATE = 'create';
    const RULE_UPDATE = 'update';

    protected $_filterOptions = array('escapeFilter' => 'NoFilter');
    protected $_messages = array();
    protected $_dcModelName = Null;
    protected $_uniqueKeys = array(
        self::RULE_UPDATE => array(),
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
    public function create(array $data, $namespace = 'create')
    {
        $failed = False;
        $model = Null;
        $this->_beforeValidate($model, self::RULE_CREATE, $data[$namespace]);
        if(!$this->validate(self::RULE_CREATE, $data[$namespace]))
        {
            $failed = True;
            /*
             * used to be
             * $this->_messages[$namespace] = $this->getFilter()->getMessages();
             * but if there was a message for password2 we would delete it that way
             */
            $this->_messages[$namespace] = (array_key_exists($namespace, $this->_messages) && is_array($this->_messages[$namespace])) ? $this->_messages[$namespace] : array();
            $this->_messages[$namespace] = array_merge($this->_messages[$namespace],$this->getFilter()->getMessages());
            return False;
        }
        if($failed)
        {
            return False;
        }
        $data = $this->getFilterData();
        $this->_afterValidate($model, self::RULE_CREATE, $data);
        $model = new $this->_dcModelName();
        if(!empty($this->_hasMany))
        {
            $relationalData = array();
            foreach($this->_hasMany as $relations)
            {
                $relationalData[$relations['relation']] = $data[$relations['relation']];
                unset($data[$relations['relation']]);
            }
        }
        foreach($data as $key => $value)
        {
            $model->$key = $value;
        }
        if(!$model->isValid(True))
        {
            $this->_messages[$namespace][] = 'the doctrine model says it\'s not valid';
            $this->_messages[$namespace][] = $model->getErrorStackAsString();
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
            return False;
        }
    }
    public function update($model, array $data, $namespace = 'update')
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
        $update = array();
        foreach($data[$namespace] as $key => $value)
        {
            //these keys are supposed to be PRESENCE_OPTIONAL
            if
            (
                is_array($this->_uniqueKeys[self::RULE_UPDATE])
                &&  in_array($key, $this->_uniqueKeys[self::RULE_UPDATE])
                &&  $value !== $model->$key
            )
            {
                //if value changed we'll validate it
                $update[$key] = $value;
            }
            if
            (
                //we don't want to change the models id
                'id' === $key
                || (
                    is_array($this->_uniqueKeys[self::RULE_UPDATE])
                    && in_array($key, $this->_uniqueKeys[self::RULE_UPDATE])
                )
            )
            {
                continue;
            }
            $update[$key] = $value;
        }
        $this->_beforeValidate($model, self::RULE_UPDATE, $update);
        if(!$this->validate(self::RULE_UPDATE, $update))
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

        if($failed)
        {
            return False;
        }
        $data = $this->getFilterData();
        $this->_afterValidate($model, self::RULE_UPDATE, $data);
        if(!empty($this->_hasMany))
        {
            $hadChanges = False;
            foreach($this->_hasMany as $relations)
            {
                $this->_updateHasMany($model, $relations, $data[$relations['relation']], $hadChanges);
                unset($data[$relations['relation']]);
            }
            if($hadChanges)
            {
                $model->refreshRelated();
            }
        }
        foreach($data as $key => $value)
        {
            $model->$key = $value;
        }
        if(!$model->isValid(True))
        {
            $this->_messages[$namespace][] = 'the doctrine model says it\'s not valid';
            $this->_messages[$namespace][] = $model->getErrorStackAsString();
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

    public function map4Form($model, $namespace)
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
        $data[$namespace] = $model->toArray(True);
        if(!empty($this->_hasMany))
        {
            foreach($this->_hasMany as $relation)
            {
                $new = array();
                foreach(array_keys($data[$namespace][$relation['relation']]) as $key)
                {
                    $data[$namespace]
                        [$relation['relation']]
                        [$key]
                            = $data[$namespace]
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
