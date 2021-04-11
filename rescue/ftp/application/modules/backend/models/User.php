<?php
/*
DcUser:
    columns:
        id:
            type: integer
            notnull: true
            autoincrement: true
            primary: true
        #user might have no group
        dcGroupId:
            type: integer
        #login name
        name:
            type: string(255)
            notnull: true
        #password, salted
        password:
            type: blob(40)
            notnull: true
        salt:
            type: blob(255)
            notnull: true
        email:
            type: string(255)
            #validate as email
            email: true
            notnull: true
    indexes:
        intern:
            #no two users shall have the same name
            fields: [name]
            type: unique
        name_mail:
            fields: [name, email]
    relations:
        DcGroup:
            class:       DcGroup
            local:       dcGroupId
            foreign:     id
            onDelete:    SET NULL
            onUpdate:    CASCADE
 *
 * changelog
 * 2009/11/20
 *       added rules for create
 *       public function create works so far
 *       added public function findOneByName($name)
 *       rules for update need to be made
 * 2009/11/21
 *      added rules for update
 *      added public function find($d)
 *      added public function map4Form(DcUser $dcModel, $namespace)
 * 2009/11/23
 *      added password_confirm field to the create method
 * 2009/11/28
 *      updated to extend Formation_Modelctrl_Simple_Abstract
 * 2010/01/19
 *      copied from formation ...
 *      removed relation to profile
 * still missing:
 *      per user permissions (to change email and passwords but not his identity or group)
 */
class Backend_Model_User extends Formation_Modelctrl_Simple_Abstract
{
    protected $_dcModelName = 'DcUser';
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
                'email' => array(
                     array('StringToLower', ENCODING)
                ),
                'dcGroupId' => array(
                    'Int',
                    'Zero2Null',
                ),
            ),
            self::RULE_UPDATE => array(
            //filter intern as A-Za-z0-9
                'name' => array(
                    'LatinAllnum',
                ),
                'email' => array(
                     array('StringToLower', ENCODING)
                ),
                'dcGroupId' => array(
                    'Int',
                    'Zero2Null',
                ),
            ),
        );
        $this->_validators = array(
            //intern is used as a key so it must be unique (on creation)
            self::RULE_CREATE => array(
                'name' => array(
                    'NotEmpty',
                    array('StringLength',array('min'=>1, 'max' => 255, 'encoding' => ENCODING)),
                    array('Doctrine_NoRecordExists','DcUser','name'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'dcGroupId' => array(
                    'allowEmpty' => true,
                    array('Doctrine_RecordExists','DcGroup','id'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
                'email' => array(
                    'NotEmpty',
                    array('StringLength',array('min'=>1, 'max' => 255, 'encoding' => ENCODING)),
                    'EmailAddress',
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'password' => array(
                    'NotEmpty',
                    array('StringLengthPwd',array('min'=>8, 'max' => 255, 'encoding' => ENCODING)),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'password_confirm' => array(
                    'NotEmpty',
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'password_password' => array(
                    'PasswordConfirmation',
                    Zend_Filter_Input::FIELDS => array('password', 'password_confirm'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
            ),
            self::RULE_UPDATE => array(
                'name' => array(
                    //it's PRESENCE_OPTIONAL so if it did not change we won't validate it
                    'NotEmpty',
                    array('StringLength',array('min'=>1, 'max' => 255, 'encoding' => ENCODING)),
                    //if name isset no record with name may exist
                    array('Doctrine_NoRecordExists','DcUser','name'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
                'dcGroupId' => array(
                    'allowEmpty' => true,
                    array('Doctrine_RecordExists','DcGroup','id'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
                'email' => array(
                    'NotEmpty',
                    array('StringLength',array('min'=>1, 'max' => 255, 'encoding' => ENCODING)),
                    'EmailAddress',
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'password' => array(
                    'NotEmpty',
                    array('StringLengthPwd' ,array('min'=>8, 'max' => 255, 'encoding' => ENCODING)),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
                'password_confirm' => array(
                    'NotEmpty',
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
                'password_password' => array(
                    'PasswordConfirmation',
                    Zend_Filter_Input::FIELDS => array('password', 'password_confirm'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
            ),
        );
    }
    protected function _beforeValidate($model, $rulesKey, &$data)
    {
        switch ($rulesKey)
        {
            case self::RULE_CREATE:
                break;
            case self::RULE_UPDATE:
                if($data['password'] === '')
                {
                    unset($data['password'], $data['password_confirm']);
                }
                break;
            default:
                break;
        }
    }
    protected function _afterValidate($model, $rulesKey, &$data)
    {
        switch ($rulesKey)
        {
            case self::RULE_CREATE:
                unset($data['password_confirm']);
                break;
            case self::RULE_UPDATE:
                //password is not set if it was empty. see _beforeValidate
                unset($data['password_confirm']);
                break;
            default:
                break;
        }
    }
    public function find($id)
    {
        $dcUser = Doctrine_Query::create()
        ->from('DcUser u')
        ->where('u.id = ?',$id)
        ->leftJoin('u.DcGroup g')
        ->fetchOne();
        return $dcUser;
    }

    public function findByNameEmail($name, $email)
    {
        $dcUser = Doctrine_Query::create()
        ->from('DcUser u')
        ->where('u.name = ? AND u.email = ?', array($name, $email))
        ->leftJoin('u.DcGroup g')
        ->fetchOne();
        return $dcUser;
    }

    public function findOneByName($name)
    {
        $dcUser = Doctrine_Query::create()
        ->from('DcUser u')
        ->where('u.name = ?', $name)
        ->leftJoin('u.DcGroup g')
        //fetchOne() returns Array or Doctrine_Collection, depending on hydration mode. False if no result.
        ->fetchOne();
        return $dcUser;
    }
    public function findAll($hydration = Doctrine::HYDRATE_ARRAY)
    {
        $dcUsers = Doctrine_Query::create()
        //name of the user and only name of group
        ->select('u.name, g.name')
        ->from('DcUser u')
        ->leftJoin('u.DcGroup g')
        ->setHydrationMode($hydration)
        ->execute();
        return $dcUsers;
    }
}
