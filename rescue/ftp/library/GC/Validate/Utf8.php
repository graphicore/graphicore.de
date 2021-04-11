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
class GC_Validate_Utf8 extends Zend_Validate_Abstract
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
        self::NOT_VALID      => "The provided value is not UTF-8 encoded.",
    );
    public function __construct()
    {}
    public static function isUtf8($string = '')
    {
      //see http://bugs.php.net/bug.php?id=45735
      //if $string is too long this will segfault php without any error

       $pattern = '%^(?:
            [\x09\x0A\x0D\x20-\x7E]            # ASCII
            | [\xC2-\xDF][\x80-\xBF]            # non-overlong 2-byte
            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
            |  \xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            |  \xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )*$%xs';
	while($string)
	{
            $test = mb_substr($string, 0, 4000);
            $string = mb_substr($string, 4000);
            if(!(bool) preg_match($pattern, $test))
           {
               return False;
           }
	}
        return True;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if $value is valid Utf-8
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue((string) $value);
        if (!self::isUtf8($value))
        {
            $this->_error(self::NOT_VALID);
            return false;
        }
        return true;
    }
}
