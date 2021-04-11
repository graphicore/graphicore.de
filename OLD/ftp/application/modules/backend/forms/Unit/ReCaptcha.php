<?php
class Backend_Form_Unit_ReCaptcha extends GC_DomForm_Subset
{
    protected $_elements = array();
    public function init()
    {
        $translate = GC_Translate::get();
        $this->_elements = array(
            array(
                'type' => GC_DomForm::DZ_DEEPER,
                'tag' => 'fieldset',
            ),
            array(
                'type' => '!child',
                'tag' => 'legend',
                'text' => $translate->_('Type the two words'),
            ),
            'reCaptcha',
            array(
                'namespace' => 'recaptcha_response_field',
                'name' => 'reCaptcha',
                'type' => 'ReCaptcha',
                'value' => ''
            ),
            array(
                'type' => GC_DomForm::DZ_RESTORE,
            ),
        );
        $reCaptchaElement = array(
            'namespace' => 'recaptcha_challenge_field',
            'name' => 'reCaptcha',
            'type' => 'ReCaptcha',
            'isXML' => True,
            'value' => ''
        );
        $this->_getReCaptcha()->setParam('xhtml', True);
        $reCaptchaElement['value'] = $this->_getReCaptcha()->getHTML();
        $this->fill('reCaptcha',array($reCaptchaElement));
    }
    protected $_reCaptchaElement;
    protected function _getReCaptcha()
    {
        if(Null === $this->_reCaptchaElement)
        {
            $reCaptchaConf = Zend_Registry::getInstance()->config->reCaptcha;
            $pubKey = $reCaptchaConf->pubKey;
            $privKey = $reCaptchaConf->privKey;
            $this->_reCaptchaElement = new GC_Service_ReCaptcha($pubKey, $privKey);
            //throw new Exception(GC_Debug::Dump($reCaptchaConf->options));
            if(isset($reCaptchaConf->options))
            {
                //throw new Exception(GC_Debug::Dump($reCaptchaConf->options->toarray()));
                $this->_reCaptchaElement->setOptions($reCaptchaConf->options);
            }
        }
        return $this->_reCaptchaElement;
    }
    public function isHuman(array $post)
    {
        $recaptcha = $this->_getReCaptcha();
        if(!array_key_exists('recaptcha_challenge_field',$post)
            || !array_key_exists('recaptcha_response_field',$post))
        {
            return False;
        }
        try
        {
            $result = $recaptcha->verify(
                $post['recaptcha_challenge_field'],
                $post['recaptcha_response_field']
            );
            $isHuman = $result->isValid();
        }
        catch(Zend_Service_ReCaptcha_Exception $e)
        {
            $isHuman = False;
        }
        return $isHuman;
    }
}
