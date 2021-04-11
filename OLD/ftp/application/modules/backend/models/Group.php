<?php
/*
DcGroup:
    columns:
        id:
            type: integer(4)
            notnull: true
            autoincrement: true
            primary: true
        name:
            type: string(255)
            notnull: true
        description: clob
    indexes:
        intern:
            #two groups shall not have the same name
            fields: [name]
            type: unique
 *
 * changelog
 * 2009/11/23
 *      created as copy from Backend_Model_User
 *      added rules for update
 *      added rules for create
 * 2009/11/28
 *      updated to extend Formation_Modelctrl_Simple_Abstract
 */

class Backend_Model_Group extends Formation_Modelctrl_Simple_Abstract
{
    protected $_dcModelName = 'DcGroup';
    protected $_uniqueKeys = array(
        self::RULE_UPDATE => array('name'),
    );
    public function init()
    {
        parent::init();

        $this->_filters = array(
            self::RULE_CREATE => array(
            //filter intern as A-Za-z0-9
                'name' => array(
                    'LatinAllnum',
                ),
                'description' => array(
                    'HTMLPurifier_Inline',
                ),
            ),
            self::RULE_UPDATE => array(
            //filter intern as A-Za-z0-9
                'name' => array(
                    'LatinAllnum',
                ),
                'description' => array(
                    'HTMLPurifier_Inline',
                ),
            ),
        );
        $this->_validators = array(
            //intern is used as a key so it must be unique (on creation)
            self::RULE_CREATE => array(
                'name' => array(
                    'NotEmpty',
                    array('StringLength',1, 255),
                    array('Doctrine_NoRecordExists','DcGroup','name'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'description' => array(
                    'allowEmpty' => true,
                    array('StringLength',0, 500),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
            ),
            self::RULE_UPDATE => array(
                'name' => array(
                    //it's PRESENCE_OPTIONAL so if it did not change we won't validate it
                    'NotEmpty',
                    array('StringLength',1, 255),
                    //if name isset no record with name may exist
                    array('Doctrine_NoRecordExists','DcGroup','name'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
                'description' => array(
                    'allowEmpty' => true,
                    array('StringLength',0, 500),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
            ),
        );
    }
    public function find($id)
    {
        $dcGroup = Doctrine_Query::create()
        ->from('DcGroup g')
        ->where('g.id = ?',$id)
        ->fetchOne();
        return $dcGroup;
    }
    public function findOneByName($name)
    {
        $dcGroup = Doctrine_Query::create()
        ->from('DcGroup g')
        ->where('g.name = ?', $name)
        //fetchOne() returns Array or Doctrine_Collection, depending on hydration mode. False if no result.
        ->fetchOne();
        return $dcGroup;
    }
    public function findAll($hydration = Doctrine::HYDRATE_ARRAY)
    {
        $dcGroups = Doctrine_Query::create()
        ->select('g.name, g.id')
        ->from('DcGroup g')
        ->setHydrationMode($hydration)
        ->execute();
        return $dcGroups;
    }
}
