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
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */


/**
 * @see Zend_Validate_Db_Abstract
 */
require_once 'GC/Validate/Db/CompoundKeyAbstract.php';
/**
 * Confirms a record exists in a table.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @uses       Zend_Validate_Db_Abstract
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class GC_Validate_Db_RecordExistsExcluding extends GC_Validate_Db_ExcludingAbstract
{
    public function isValid($value)
    {
        $valid = true;
        $this->_setValue($value);

        $result = $this->_query($value);
        if (!$result) {
            $valid = false;
            $this->_error(self::ERROR_NO_RECORD_FOUND);
        }

        return $valid;
    }
}
