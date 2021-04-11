<?php
/*
#that's a blog entry.
DcDiary:
    actAs:
        Timestampable:
        I18n:
            fields: [title, htmlContent, teaser, published]
            length: 5
            actAs:
                Timestampable:
    columns:
        id:
            type: integer
            notnull: true
            autoincrement: true
            primary: true
        urlId:
            type: string(255)
            notnull: true
        #YYYY-MM-DD HH:MI:SS. no timezone! so it should be always utc ? or
        timestamp: timestamp
        #for the Id in the atom feed, language and such will be added later
        tagUrlPart:
            type: string(255)
            notnull: true
        #title like for the <title> tag
        title:
            type: string(255)
            notnull: true
        htmlContent: clob
        teaser: clob
        #translations wich are not published will not appear, so they can be done without a hurry
        #remeber: make a preview possible for the (loged in) author
        published:
            type: boolean
            default: False
    indexes:
        tagUrlPart:
        #won't be changeable after its set
            fields: [tagUrlPart]
            type: unique
        timestamp:
            #DESC
            fields: [timestamp]
            sorting: ASC
        urlId:
        #makes it easier to link to ... /lang/controller/action/urlId
            fields: [urlId]
            #type: unique
    relations:
        Filters:
            class: DcFilter
            local: diary_id
            foreign: filter_id
            refClass: DcDiaryFilter
 */
class Backend_Model_Diary extends Formation_Modelctrl_I18n_Abstract
{
    protected $_tagUrlFormat = 'tag:graphicore.de,%1$s:/diary/%2$s%3$s';
    protected $_dcModelName = 'DcDiary';
    protected $_uniqueKeys = array(
        self::RULE_UPDATE => array('urlId', 'date'),// and 'tagUrlPart' which will not be altered after creation and has no input field
        self::RULE_UPDATEI18N => array(),
    );

    protected $_hasMany = array(
        array(
            'relation' => 'Filters',
            'class' => 'DcFilter',
            'local' => 'diary_id',
            'foreign' => 'filter_id',
            'foreignId' => 'id',
            'refClass' =>'DcDiaryFilter',
        ),
    );
    protected function _getTagUrl()
    {
        //this should be unique forever ... well
        return sprintf($this->_tagUrlFormat, date('Y-m-d'), date('YmdHis'), uniqid());
    }

    public function init()
    {
        parent::init();

        $this->_filters = array(
            self::RULE_CREATE => array(
                'urlId' => array(
                    'LatinAllnumMinusUscore',
                ),
            ),
            self::RULE_CREATEI18N => array(
                'title' => array(
                    'StripTags',
                    'StringTrim',
                    'StripNewlines',
                    array('HtmlSpecialChars',array('charset' => ENCODING, 'doublequote' => False)),//Alpha should th even more than this but Dipl. Des does not work
                ),
                'htmlContent' => array(
                    'HTMLPurifier_Div',
                ),
                'teaser' => array(
                    'HTMLPurifier_Inline',
                ),
                'published' => array(
                    'Int'// Returns (int) $value
                ),
            ),
            self::RULE_UPDATE => array(
                'urlId' => array(
                    'LatinAllnumMinusUscore',
                ),
            ),
            self::RULE_UPDATEI18N => array(
                'title' => array(
                    'StripTags',
                    'StringTrim',
                    'StripNewlines',
                    array('HtmlSpecialChars',array('charset' => ENCODING, 'doublequote' => False)),//Alpha should th even more than this but Dipl. Des does not work
                ),
                'htmlContent' => array(
                    'HTMLPurifier_Div',
                ),
                'teaser' => array(
                    'HTMLPurifier_Inline',
                ),
                'published' => array(
                    'Int'// Returns (int) $value
                ),
            ),
        );
        $this->_validators = array(
            self::RULE_CREATE => array(
                'timestamp' => array
                (
                    'NotEmpty',
                    'dbDate',
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'urlId' => array(
                    'NotEmpty',
                    //'Utf8',//not needed is latinAllnum
                    array('StringLength',1, 255),
                    array('Doctrine_NoRecordExists', $this->_dcModelName, 'urlId'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'tagUrlPart' => array(
                    'NotEmpty',
                    array('StringLength', 1, 255),
                    array('Doctrine_NoRecordExists',$this->_dcModelName,'tagUrlPart'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'Filters' => array(
                    'allowEmpty' => True,
                    'isArray',
                    //might not be needed since these values have been checked
                    //when they where compaired to the possible form values
                    //we do this because !!!! if there is no Project the default
                    //formfield is used here wich does a fk constraint violation
                    //because its value is not(never, its a string) an id in DcProject
                    //and we do this because there might be a circumstance where the source is not filtering if it is the source
                    array('Doctrine_AllRecordsExist','DcFilter','id'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
            ),
            self::RULE_CREATEI18N => array(
                'title' => array(
                    'NotEmpty',
                    'Utf8',
                    array('StringLength', 1, 255),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'htmlContent' => array(
                    'allowEmpty' => true,
                    //'Utf8',
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'teaser' => array(
                    'allowEmpty' => true,
                    //'Utf8',
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'published' => array(
                    array('Between', 0, 1),//The comparison is inclusive by default ($value may equal a boundary value)
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
            ),
            self::RULE_UPDATE => array(
                'timestamp' => array
                (
                    'NotEmpty',
                    'dbDate',
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'urlId' => array(
                    'NotEmpty',
                    //'Utf8',
                    array('StringLength',1, 255),
                    array('Doctrine_NoRecordExists', $this->_dcModelName,'urlId'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
                'Filters' => array(
                    'allowEmpty' => True,
                    'isArray',
                    //might not be needed since these values have been checked
                    //when they where compaired to the possible form values
                    //we do this because !!!! if there is no Project the default
                    //formfield is used here wich does a fk constraint violation
                    //because its value is not(never, its a string) an id in DcProject
                    //and we do this because there might be a circumstance where the source is not filtering if it is the source
                    array('Doctrine_AllRecordsExist','DcFilter','id'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
            ),
            self::RULE_UPDATEI18N => array(
                'title' => array(
                    'NotEmpty',
                    'Utf8',
                    array('StringLength',1, 255),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'htmlContent' => array(
                    'allowEmpty' => true,
                    //'Utf8',
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'teaser' => array(
                    'allowEmpty' => true,
                    //'Utf8',
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'published' => array(
                    array('Between', 0, 1),//The comparison is inclusive by default ($value may equal a boundary value)
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
            ),
        );
    }
    protected function _beforeValidate($model, $rulesKey, &$data)
    {
        switch ($rulesKey)
        {
            case self::RULE_CREATE:
                    $data['tagUrlPart'] = $this->_getTagUrl();
            case self::RULE_UPDATE:
            //fix for some bad Zend Behavior, might break in other cases
                    $key = 'Filters';
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
            //fix for some bad Zend Behavior
                    $key = 'Filters';
                    if(array_key_exists($key, $data))
                    {
                        $data[$key] = reset($data[$key]);
                    }
                break;
            default:
                break;
        }
    }



    public function find($id)
    {
        return Doctrine_Query::create()
        ->select('d.*, f.id, t.*')
        ->from('DcDiary d')
        ->leftJoin('d.Filters f')
        ->leftJoin('d.Translation t')
        ->where('d.id = ?',$id)
        ->fetchOne();
    }
    //the optional parameters are used by the index controller of the frontend
    //the backend works with the default values
    //i think and hope the backend does not use this method at all :-|
    //now not longer used by the frontend
    public function findOneBySlug($slug, $hydration = Doctrine::HYDRATE_RECORD, $withTranslation = False)
    {
        throw new GC_Deprecated_Exception(__CLASS__.'::'.__FUNCTION__.' is deprecated, use findOneByUrlId instead');
    }

    public function findOneByUrlId($urlId, $hydration = Doctrine::HYDRATE_RECORD, $withTranslation = False)
    {
        $q = Doctrine_Query::create()
        ->setHydrationMode($hydration)
        ->select('d.*, t.*')
        ->from('DcDiary d')
        ->where('d.urlId = ?', $urlId);
        if($withTranslation)
        {
            $q->leftJoin('d.Translation t');
        }
        return $q->fetchOne();
    }
    public function findAll($hydration = Doctrine::HYDRATE_ARRAY)
    {
        $all = Doctrine_Query::create()
        ->select('d.id, d.urlId, t.title, t.teaser, t.lang')
        ->from('DcDiary d')
        ->leftJoin('d.Translation t')
        ->orderBy('d.timestamp DESC')
        ->setHydrationMode($hydration)
        ->execute();
        return $this->_i18nFlatten($all);
    }
    protected function _i18nFlatten($data)
    {
        foreach(array_keys($data) as $key)
        {
            $keys = array_keys($data[$key]['Translation']);
            $lang = GC_I18n::getLang();
            if(!in_array($lang, $keys))
            {
                $lang = current($keys);
            }
            foreach($data[$key]['Translation'][$lang] as $k => $v)
            {
                $data[$key][$k] = $v;
            }
            $data[$key]['_allTranslations'] = join(', ', $keys);
        }
        return $data;
    }
}
