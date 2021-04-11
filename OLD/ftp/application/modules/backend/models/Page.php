<?php
/*
DcPage:
    actAs:
        Timestampable:
        I18n:
            fields: [title, htmlContent, published]
            length: 5
            actAs:
                Timestampable:
    columns:
        id:
            type: integer
            notnull: true
            autoincrement: true
            primary: true
        #where this page can be found
        urlId:
            type: string(255)
            notnull: true
        #title like for the <title> tag
        title:
            type: string(255)
            notnull: true
        htmlContent: clob
        #translations wich are not published will not appear, so they can be done without a hurry
        #remeber: make a preview possible for the (loged in) author
        published:
            type: boolean
            default: False
    indexes:
        intern:
        #makes it easier to link to ... /lang/controller/action/urlId
            fields: [urlId]
            #type: unique
 *
 * changelog
 * 2010/01/19
 *      created as copy from Backend_Model_Page from Formation
 */
class Backend_Model_Page extends Formation_Modelctrl_I18n_Abstract
{
    protected $_dcModelName = 'DcPage';
    protected $_uniqueKeys = array(
        self::RULE_UPDATE => array('urlId'),
        self::RULE_UPDATEI18N => array(),
    );
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
                'published' => array(
                    'Int'// Returns (int) $value
                ),
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
            ),
            self::RULE_CREATEI18N => array(
                'title' => array(
                    'allowEmpty' => true,
                    'Utf8',
                    array('StringLength',1, 255),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
                'htmlContent' => array(
                    'allowEmpty' => true,
                    //'Utf8',//htmlpurifier does this
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
                'urlId' => array(
                    'NotEmpty',
                    //'Utf8',
                    array('StringLength',1, 255),
                    array('Doctrine_NoRecordExists', $this->_dcModelName,'urlId'),
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                ),
            ),
            self::RULE_UPDATEI18N => array(
                'title' => array(
                    'allowEmpty' => true,
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
                'published' => array(
                    array('Between', 0, 1),//The comparison is inclusive by default ($value may equal a boundary value)
                    Zend_Filter_Input::BREAK_CHAIN => true,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                ),
            ),
        );
    }
    public function find($id)
    {
        $dcModel = Doctrine_Query::create()
        ->from($this->_dcModelName.' ds')
        ->where('ds.id = ?',$id)
        ->leftJoin('ds.Translation t')
        ->fetchOne();
        return $dcModel;
    }
    //the optional parameters are used by the index controller of the frontend
    //the backend works with the default values
    //i think and hope the backend does not use this method at all :-|
    //now not longer used by the frontend

    public function findOneByUrlAlias($urlId, $hydration = Doctrine::HYDRATE_RECORD , $withTranslation = False)
    {
        throw new GC_Deprecated_Exception(__CLASS__.'::'.__FUNCTION__.' is deprecated, use findOneByUrlId instead');
    }

    public function findOneByUrlId($urlId, $hydration = Doctrine::HYDRATE_RECORD , $withTranslation = False)
    {
        $q = Doctrine_Query::create()
        ->setHydrationMode($hydration)
        ->from($this->_dcModelName.' ds')
        ->where('ds.urlId = ?', $urlId);
        if($withTranslation)
        {
            $q->leftJoin('ds.Translation t');
        }

        //fetchOne() returns Array or Doctrine_Collection, depending on hydration mode. False if no result.
        $dcModel = $q->fetchOne();
        return $dcModel;
    }
    public function findAll($hydration = Doctrine::HYDRATE_ARRAY)
    {
        $dcModels = Doctrine_Query::create()
        ->select('ds.urlId, ds.id')
        ->from($this->_dcModelName.' ds')
        ->setHydrationMode($hydration)
        ->execute();
        return $dcModels;
    }
}
