<?php
//try to organize images in a useful manner

//this only uses a conventional file organisation and a lot of hardcoded information
//the idea is to build some more classes around that file organisaition

//folder language
//  folder namespace
//        file orignal.image
//        file original.otherimagefiletype
//        folder usage //thumbnail etc
//            file orignal.filetype

class GC_Image_Static
{
    public static $imageFolder = '/';//where the file can be found
    protected static $_baseUrl = Null;
    public static $publicImageFolder = '';//_baseUrl plus this is where the images are for the outside world
    public static $extensions = array('png' => 1, 'jpg' => 2,'jpeg' => 2, 'gif' => 3);//the lightest available will be returned
    protected static $_defaultName = '_default';
    protected static $_initialised = False;
    /**
     * Constructor
     */
    protected function __construct()
    {}
    public static function init()
    {
        if(self::$_initialised)
        {
            return;
        }
        if (null === self::$_baseUrl)
        {
            $url = Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();
            $root = '/' . trim($url, '/');
            if ('/' == $root)
            {
                $root = '';
            }
            self::$_baseUrl = $root . '/';
        }
        self::$_initialised = True;
    }
    
    public static function getFallbacks($language)
    {
        if(!self::$_initialised)
        {
            self::init();
        }
        //FIXME! store array with fallbacks for lang is stored somewhere
        //if there is no fallback return an empty array
        
        $defLang = GC_I18n::getDefaultLang();
        return ($defLang !== $language)? array($defLang) : array();
    }
    public static function getImage($language, $namespace, $name, $useage = Null)
    {
        if(!self::$_initialised)
        {
            self::init();
        }
        $fallbacks = self::getFallbacks($language);
        $seenLangs = array();
        //don't use 2 times the same language...
        //this is considered as recursion
        while($language && !isset($seenLangs[$language]))
        {
            $seenLangs[$language] = True;
            $file = self::getImageFile($language, $namespace, $name, $useage);
            if(!$file && $useage)
            {
                //if there is some code for the usage to be build from an original image
                //and if there is an original image
                //$original = $this->getImageFile($language, $namespace, $name);
                //make the useage from the original image and return its path
            }
            if($file)
            {
                return $file;
            }
            $language = array_shift($fallbacks);

        }
        return false;
    }
    public static function getImageUrl($language, $namespace, $name, $useage = Null, $default = True)
    {
		
        if(!self::$_initialised)
        {
			
            self::init();
            throw new GC_Exception('goin in');
        }
        $image  = self::getImage($language, $namespace, $name, $useage);
        if(!$image && !$default)
        {
            return False;
        }
        $image = $image ? $image : self::getImage($language, $namespace, self::$_defaultName, $useage);
        if(!$image)
        {
            throw new GC_Image_Exception(sprintf('There is no default Image for namespace "%1$s, %2$s, %3$s, %4$s"',$language, $namespace, $name, $useage));
        }
        return self::$baseUrl.self::$publicImageFolder.$image;
    }
    public static function getImageFile($language, $namespace, $name, $useage = Null)
    {
        if(!self::$_initialised)
        {
            self::init();
        }
        $folderFormat = self::_getFolderFormat($useage);
        $globFor = self::$imageFolder.'/'.sprintf($folderFormat, $language, $namespace, $name, $useage).'.*';
        $files = glob($globFor, GLOB_MARK);
        throw new GC_Exception(GC_Debug::Dump(self::$imageFolder));
        throw new Exception(GC_Debug::Dump($globFor));
        throw new Exception(GC_Debug::Dump($files));
        $file = False;
        foreach($files as $candidate)
        {
            $pInfo = pathinfo($candidate);
            if(!isset(self::$extensions[$pInfo['extension']]))
            {
                continue;
            }
            $file = ($file === False || $file['weight'] > self::$extensions[$pInfo['extension']])
                ? array('info' => $pInfo ,'weight' => self::$extensions[$pInfo['extension']])
                : $file;
        }
        if(!$file)
        {
            return False;
        }
        //found an image
        $name = $file['info']['basename'];
        return sprintf($folderFormat, $language, $namespace, $name, $useage);
    }
    protected static function _getFolderFormat($useage)
    {
        //format for: sprintf($folderFormat, $language, $namespace, $name, $useage)
        $folderFormat =
            ($useage
            ? '%1$s/%2$s/%4$s/%3$s'         //usage refers to a deeper folder
            : '%1$s/%2$s/%3$s'              //without usage
            )
        ;
        return $folderFormat;
    }
}
