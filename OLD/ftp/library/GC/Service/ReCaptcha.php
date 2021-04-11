<?php
class GC_Service_ReCaptcha extends Zend_Service_ReCaptcha
{
    /**
     * Get the HTML code for the captcha
     *
     * This method uses the public key to fetch a recaptcha form.
     *
     * @return string
     * @throws Zend_Service_ReCaptcha_Exception
     */
    public function getHtml()
    {
        if ($this->_publicKey === null) {
            /** @see Zend_Service_ReCaptcha_Exception */
            require_once 'Zend/Service/ReCaptcha/Exception.php';

            throw new Zend_Service_ReCaptcha_Exception('Missing public key');
        }

        $host = self::API_SERVER;

        if ($this->_params['ssl'] === true) {
            $host = self::API_SECURE_SERVER;
        }

        $htmlBreak = '<br>';
        $htmlInputClosing = '>';

        if ($this->_params['xhtml'] === true) {
            $htmlBreak = '<br />';
            $htmlInputClosing = '/>';
        }

        $errorPart = '';

        if (!empty($this->_params['error'])) {
            $errorPart = '&error=' . urlencode($this->_params['error']);
        }

        $reCaptchaOptions = '';

        if (!empty($this->_options)) {
            $encoded = Zend_Json::encode($this->_options);
            $reCaptchaOptions = <<<SCRIPT
<script type="text/javascript">
    var RecaptchaOptions = {$encoded};
</script>
SCRIPT;
        }

        $return = $reCaptchaOptions;
        $return .= <<<HTML
<script type="text/javascript"
   src="{$host}/challenge?k={$this->_publicKey}{$errorPart}">
</script>
HTML;

        $return .= <<<HTML
<noscript>
    <object style="border:1px solid red" type="text/html" data="{$host}/noscript?k={$this->_publicKey}{$errorPart}"
        height="300" width="500">
        <!-- User's browser is really broken, most likely IE
        lets try embedding an IFRAME for IE using condition comments,
        if they're using a browser other than IE now, or a version of IE
        without IFRAME support they're screwed -->
        <!--[if IE]>
            <iframe src="{$host}/noscript?k={$this->_publicKey}{$errorPart}"
            height="300" width="500" frameborder="0">
            </iframe>{$htmlBreak}
        <![endif]-->
    </object>
    <textarea name="recaptcha_challenge_field" rows="3" cols="40"> </textarea>
    <input type="hidden" name="recaptcha_response_field"
       value="manual_challenge"{$htmlInputClosing}
</noscript>
HTML;
        return $return;
    }
}