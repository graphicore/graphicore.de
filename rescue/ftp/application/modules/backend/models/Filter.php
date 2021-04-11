<?php
/*
DcFilter:
    actAs:
        I18n:
            fields: [name, description]
            length: 5
    columns:
        id:
            type: integer
            notnull: true
            autoincrement: true
            primary: true
        urlId:
            type: string(255)
            notnull: true
        weight:
            type: float
            default: 50.00
        name:
            type: string(255)
            notnull: true
        description: clob
        published:
            type: boolean
            default: False
    indexes:
        urlId:
            fields: [urlId]
    relations:
        Diaries:
            class: DcDiary
            local: filter_id
            foreign: diary_id
            refClass: DcDiaryFilter
 * changelog
 * 2009/11/25
 *      created as copy from Backend_Model_Page
 */
class Backend_Model_Filter extends Formation_Modelctrl_I18n_Abstract
{
    protected $_dcModelName = 'DcFilter';
    protected $_uniqueKeys = array(
        self::RULE_UPDATE => array('urlId'),
        self::RULE_UPDATEI18N => array(),
    );
/*
    protected $_hasMany = array(
        array(
            'relation' => 'Diaries',
            'class' => 'DcDiary',
            'foreign' => 'diary_id',
            'local' => 'filter_id',
            'foreignId' => 'id',
            'refClass' =>'DcDiaryFilter',
        ),
    );
*/
    public function init()
    {
        parent::init();
        $this->_filters = array(
            self::RULE_CREATE => array(
                'urlId' => array(
                    'LatinAllnumMinusUscore',
                ),
                'published' => array(
                    'Int'// Returns (int) $value
                ),
                'weight' => array(
                    array('GetFloat', Zend_Registry::get('Zend_Locale'), False),
                )
            ),
            self::RULE_CREATEI18N => array(
                'description' => array(
                    'HTMLPurifier_Inline',
                ),
                'name' => array(
                    'HTMLPurifier_Inline',
                    'StripTags',
                    'StringTrim',
                    'StripNewlines',
                    array('HtmlSpecialChars',array('charset' => ENCODING, 'doublequote' => False)),//Alpha should do even more than this but "Dipl. Des" does not work then
                )
            ),
            self::RULE_UPDATE => array(
                'urlId' => array(
                    'LatinAllnumMinusUscore',
                ),
                'published' => array(
                    'Int'// Returns (int) $value
                ),
                'weight' => array(
                    array('GetFloat', Zend_Registry::get('Zend_Locale'), False),
                )
            ),
            self::RULE_UPDATEI18N => array(
                'description' => array(
                    'HTMLPurifier_Inline',
                ),
                'name' => array(
                    'HTMLPurifier_Inline',
                    'StripTags',
                    'StringTrim',
                    'StripNewlines',
                    array('HtmlSpecialChars',array('charset' => ENCODING, 'doublequote' => False)),//Alpha should do even more than this but "Dipl. Des" does not work then
                )
            ),
        );
        $this->_validators = array(
            self::RULE_CREATE => array(
                'urlId' => array(
                    'NotEmpty',
                    //'Utf8',//not needed is latinAllnum
                    array('StringLength',1, 255),
                    array('Doctrine_NoRecordExists', $this->_dcModelName, 'urlId'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'published' => array(
                    array('Between', 0, 1),//The comparison is inclusive by default ($value may equal a boundary value)
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'weight' => array(
                    array('Float'),
                    array('Between',-999.99, 999.99),//float 5,2
                    //Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
/*
                'Diaries' => array(
                    'allowEmpty' => True,
                    'isArray',
                    //might not be needed since these values have been checken
                    //when they where compaired to the possible form values
                    //we do this because !!!! if there is no Project the default
                    //formfield is used here wich does a fk constraint violation
                    //because its value is not(never, its a string) an id in DcProject
                    //and we do this because there might be a circumstance where the source is not filtering if it is the source
                    array('Doctrine_AllRecordsExist','DcDiary','id'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
*/
            ),
            self::RULE_CREATEI18N => array(
                'name' => array(
                    'NotEmpty',
                    array('StringLength',1, 255),
                    //array('Doctrine_NoRecordExists', $this->_dcModelName, 'name', True),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                    //unique
                ),
                'description' => array(
                    'allowEmpty' => true,
                    //'Utf8',
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
            ),
            self::RULE_UPDATE => array(
                'urlId' => array(
                    'NotEmpty',
                    //'Utf8',
                    array('StringLength',1, 255),
                    array('Doctrine_NoRecordExists', $this->_dcModelName,'urlId'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
                'published' => array(
                    array('Between', 0, 1),//The comparison is inclusive by default ($value may equal a boundary value)
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'weight' => array(
                    array('Float'),
                    array('Between',-999.99, 999.99),//float 5,2
                    //Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
/*
                'Diaries' => array(
                    'allowEmpty' => True,
                    'isArray',
                    //might not be needed since these values have been checken
                    //when they where compaired to the possible form values
                    //we do this because !!!! if there is no Project the default
                    //formfield is used here wich does a fk constraint violation
                    //because its value is not(never, its a string) an id in DcProject
                    //and we do this because there might be a circumstance where the source is not filtering if it is the source
                    array('Doctrine_AllRecordsExist','DcDiary','id'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
*/
            ),
            self::RULE_UPDATEI18N => array(
                'name' => array(
                    'NotEmpty',
                    array('StringLength',1, 255),
                    //array('Doctrine_NoRecordExists', $this->_dcModelName, 'name', True),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'description' => array(
                    'allowEmpty' => true,
                    //'Utf8',
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
            )
        );
    }

/*
    protected function _beforeValidate($model, $rulesKey, &$data)
    {
        switch ($rulesKey)
        {
            case self::RULE_CREATE:
            case self::RULE_UPDATE:
            //fix for some bad Zend Behavior, might break in other cases
                    $key = 'Diaries';
                    if(array_key_exists($key, $data))
                    {
                        $data[$key] = array($data[$key]);
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
            case self::RULE_UPDATE:
            //fix for some bad Zend Behavior reversed
                    $key = 'Diaries';
                    if(array_key_exists($key, $data))
                    {
                        $data[$key] = reset($data[$key]);
                    }
                break;
            default:
                break;
        }
    }
*/
    public function find($id)
    {
        $dcModel = Doctrine_Query::create()
        ->select('f.*, t.*')
        ->from('DcFilter f')
        ->leftJoin('f.Translation t')
        ->where('f.id = ?', $id)
        ->fetchOne();
        return $dcModel;
    }
    //the optional parameters are used by the index controller of the frontend
    //the backend works with the default values
    //i think and hope the backend does not use this method at all :-|
    //now not longer used by the frontend
     public function findOneBySlug($slug, $hydration = Doctrine::HYDRATE_RECORD)
    {
        throw new GC_Deprecated_Exception(__CLASS__.'::'.__FUNCTION__.' is deprecated, use findOneByUrlId instead');
    }

    public function findOneByUrlId ($urlId, $hydration = Doctrine::HYDRATE_RECORD)
    {
        $dcModel = Doctrine_Query::create()
        ->setHydrationMode($hydration)
        ->select('f.*, t.*')
        ->from('DcFilter f')
        ->leftJoin('f.Translation t')
        ->where('f.urlId = ?', $urlId)
        //fetchOne() returns Array or Doctrine_Collection, depending on hydration mode. False if no result.
        ->fetchOne();
        return $dcModel;
    }
    public function findAll($hydration = Doctrine::HYDRATE_ARRAY)
    {
        $dcModels = Doctrine_Query::create()
        ->select('f.*, t.*, t.name as name')
        ->from('DcFilter f')
        ->leftJoin('f.Translation t')
        //how to find out
        ->orderBy('f.weight')
        ->setHydrationMode($hydration)
        ->execute();
        return $dcModels;
    }
}
