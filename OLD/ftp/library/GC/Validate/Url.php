<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Identical.php 17684 2009-08-20 09:20:36Z yoshida@zend.co.jp $
 */

/** @see Zend_Validate_Abstract */
require_once 'Zend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Gc_Validate_Url extends Zend_Validate_Abstract
{
    /**
     * Error codes
     * @const string
     */
    const NOT_VALID      = 'notValid';

    /**
     * Error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_VALID      => "The provided url '%value%' does not match the pattern for an url.",
    );
    public function __construct()
    {}

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue((string) $value);

        #from http://www.phpcentral.com/208-url-validation-php.html

        // SCHEME
        $urlregex = "^(https?|ftp)\:\/\/";

        // USER AND PASS (optional)
        $urlregex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";

        // HOSTNAME OR IP
        $urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*";  // http://x = allowed (ex. http://localhost, http://routerlogin)
        //$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)+";  // http://x.x = minimum
        //$urlregex .= "([a-z0-9+\$_-]+\.)*[a-z0-9+\$_-]{2,3}";  // http://x.xx(x) = minimum
        //use only one of the above

        // PORT (optional)
        $urlregex .= "(\:[0-9]{2,5})?";
        // PATH  (optional)
        $urlregex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";
        // GET Query (optional)
        $urlregex .= "(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?";
        // ANCHOR (optional)
        $urlregex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?\$";

        // check
        if (!eregi($urlregex, $value))
        {
            $this->_error(self::NOT_VALID);
            return false;
        }
        return true;
    }
}
