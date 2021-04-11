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

class GC_Image
{
    public $imageFolder = '/';//where the file can be found
    protected $_baseUrl = Null;
    public $publicImageFolder = '';//_baseUrl plus this is where the images are for the outside world
    public $extensions = array('png' => 1, 'jpg' => 2,'jpeg' => 2, 'gif' => 3);//the lightest available will be returned
    protected $_defaultName = '_default';
    protected $_initialised = False;
    /**
     * Constructor
     */
    public function __construct()
    {
        if (Null === $this->_baseUrl)
        {
            #this is broken
            $url = Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();
            $root = '/' . trim($url, '/');
            if ('/' == $root)
            {
                $root = '';
            }
            $this->_baseUrl = $root;
        }
        $this->init();
    }
    public function init()
    {}
    protected $_callbackCache = array();
    public function getFallbacks($language)
    {
        if(!array_key_exists($language, $this->_callbackCache))
        {
        //FIXME! store array with fallbacks for lang is stored somewhere
        //if there is no fallback return an empty array
        //$defLang = GC_I18n::getDefaultLang();
        //return ($defLang !== $language)? array($defLang) : array();
            $all = Zend_Registry::getInstance()->allowedLocales;
            foreach(array_keys($all) as $k)
            {
                if($all[$k] === $language)
                {
                    unset($all[$k]);
                }
            }
            $this->_callbackCache[$language] = $all;
        }
        return $this->_callbackCache[$language];

    }
    public function getImage($language, $namespace, $name, $useage = Null)
    {
        $fallbacks = $this->getFallbacks($language);
        $seenLangs = array();
        //don't use 2 times the same language...
        //this is considered as recursion
        while($language && !isset($seenLangs[$language]))
        {
            $seenLangs[$language] = True;
            $file = $this->getImageFile($language, $namespace, $name, $useage);
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
    public function getImageUrl($language, $namespace, $name, $useage = Null, $default = True)
    {
        $image  = $this->getImage($language, $namespace, $name, $useage);
        if(!$image && !$default)
        {
            return False;
        }
        $image = $image ? $image : $this->getImage($language, $namespace, $this->_defaultName, $useage);
        if(!$image)
        {
            throw new GC_Image_Exception(sprintf('There is no default Image for namespace "%1$s, %2$s, %3$s, %4$s"',$language, $namespace, $name, $useage));
        }
        return $this->_baseUrl.$this->publicImageFolder.$image;
    }
    public function getImageFile($language, $namespace, $name, $useage = Null)
    {
        $folderFormat = $this->_getFolderFormat($useage);
        $globFor = $this->imageFolder.'/'.sprintf($folderFormat, $language, $namespace, $name, $useage).'.*';
        $files = glob($globFor, GLOB_MARK);
        $files = is_array($files) ? $files : array();
        $file = False;
        foreach($files as $candidate)
        {
            $pInfo = pathinfo($candidate);
            if(!isset($this->extensions[$pInfo['extension']]))
            {
                continue;
            }
            $file = ($file === False || $file['weight'] > $this->extensions[$pInfo['extension']])
                ? array('info' => $pInfo ,'weight' => $this->extensions[$pInfo['extension']])
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
    protected function _getFolderFormat($useage)
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
