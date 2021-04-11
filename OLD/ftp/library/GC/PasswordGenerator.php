<?php
/**
 * Class Name: pwdGen
 * Version: 1.2
 * Author(s): David Hurst
 * Copyright belongs to the author, but this code may be freely distributed provided this header remains in tact.
 *
 * found at: http://www.davidhurst.co.uk/2006/09/21/php-random-secure-password-generator-class/
 *
 * modified by Lasse Fister lasse.fister@rotorberlin.de
 *
 * this might still not be random/secure enough
 */

class GC_PasswordGenerator
{
    /**
     * x - a lower case letter
     * X - an upper case letter
     * c - a lower case consonant
     * C - an upper case consonant
     * v - a lower case vowel
     * V - an upper case vowel
     * 0 - a number from 0-9
     * * - a symbol
     **/
    protected $_values = array(
        //vowels
        'v'     => array('a', 'e', 'i', 'o', 'u'),
        //consonants
        'c' => array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'),
        //letters
        'x'    => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'),
        //symbols
        '*'    => array('!', '@', '#', '.', '$', '/', '%', '&', '*', '(', ')', '[', ']', '+', '-', '_', '?', '='),
    );
    private $_randomGenerator = 'mt_rand';
    static protected $_randomDevice = '/dev/urandom';
    //to know the pattern makes it easier to guess the password of course
    //to know how the password was generated (this algorythm) makes it easier to guess the password as well.
    public $pattern;
    protected $_defaultPattern = 'xxXX**00';
    static public function generatePwd($length = 8, $pattern = '')
    {
        $pwdGen = new self($pattern);
        return $pwdGen->newPwd();
    }
    static public function getRandomBytes($length = 256)
    {
        if(!is_int($length))
        {
            throw new GC_Exception('$length must be integer');
        }
        $handle = @fopen (self::$_randomDevice, "rb");
        if($handle)
        {
            $bytes = fread ($handle, $length);
            fclose ($handle);
            return $bytes;
        }
        else
        {
            $bin = '';
            while($length)
            {
                $bin .= chr(mt_rand(0, 255));
                $length--;
            }
            return $bin;
        }
    }

    public function __construct($length = 8, $pattern = '')
    {
        if(is_int($length) && $length > 0)
        {
            $pattern = $this->patternGenerator($length, $pattern);
        }
        $this->pattern = (empty($pattern) || !is_string($pattern)) ? $this->_defaultPattern : $pattern;
    }

    public function patternGenerator($length, $pattern = '', array $parts = array('x','x','*','0','c','v'), $randCase = True)
    {
        //pattern can have parts that will be in there for sure
        $pattern = (!is_string($pattern)) ? '' : $pattern;
        if(!is_int($length))
        {
            throw new GC_Exception('$length must be integer');
        }
        if(empty($parts))
        {
            throw new GC_Exception('$parts must not be empty');
        }
        for($i = strlen($pattern); $i < $length; $i++)
        {
            $x = call_user_func($this->_randomGenerator, 0, (count($parts) - 1));
            //random upper/lower case
            if($randCase)
            {
                $pattern .= (call_user_func($this->_randomGenerator, 0, 1))
                   ? mb_strtolower($parts[$x])
                   : mb_strtoupper($parts[$x]);
                continue;
            }
            $pattern .= $parts[$x];
        }
        return $pattern;
    }
    public function newPwd()
    {
        $new_pwd = array();
        $pttn = $this->pattern;
        for($i = 0; $i < strlen($this->pattern); $i++)
        {
            $value = substr($pttn, 0, 1);
            $from = mb_strtolower($value);
            $pttn = substr($pttn, 1);
            if(array_key_exists($from, $this->_values))
            {
                $randChar = $this->_randomSign($from);
                //if $value is uppercase
                if($from !== $value)
                {
                    $randChar = mb_strtoupper($randChar);
                }
            }
            else if($value === '0')
            {
                $randChar = (string) call_user_func($this->_randomGenerator, 0, 9);
            }
            else
            {
                throw new GC_Exception(sprintf('%1$s is no valid pattern identifier', $value));
            }
            $new_pwd[] = $randChar;
        }
        //randomize the order of the generated characters
        usort($new_pwd, array($this, 'randSort'));
        return join('', $new_pwd);
    }
    public function randSort()
    {
        return call_user_func($this->_randomGenerator, 0, 2)-1;
    }
    private function _randomSign($from)
    {
        $i = call_user_func($this->_randomGenerator, 0, (count($this->_values[$from]) - 1));
        return $this->_values[$from][$i];
    }
}
