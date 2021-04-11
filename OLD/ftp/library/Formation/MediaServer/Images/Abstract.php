<?php
abstract class Formation_MediaServer_Images_Abstract
extends Formation_MediaServer_Abstract
{
        /*
     * the realpath to the folder managed by this class
     * realpath(APPLICATION_PATH.'/public/media/portfolio/images)
     */
    protected $_realPath = '';
    /*
     * so that appending /thumbnail/sexlabel.jpg leads to the image
     * without the url, but with the relative rest
     */
    protected $_webPath = '';
    protected $_saveDerivedFiles = False;//False;
    protected $_source = array
    (
        'key' => 'original',
        /*
         * something like
         *      must be bigger than
         *      and have an aspect ratio of
         */
        'requirements' => array()
    );
    protected $_format2ContentTypeHeader = array();
    protected $_derivatives = array();
    protected $_messages = array();

    //the possible values of $resourceArray[1]
    protected $_resources = array();
    //the possible values of $resourceArray[0]
    protected $_usages = array();
    //sort by default: latest image first
    protected $_sortByKey = 'date';
    protected $_sortReverse = True;
    protected $_sort = True;
    public function __construct( $realPath, $webPath )
    {
        Formation_MediaServer::checkRealPath($realPath);
        $this->_realPath = $realPath;
        $this->_webPath = $webPath;
        if(array_key_exists($this->_source['key'], $this->_derivatives))
        {
            throw new Formation_MediaServer_Exception('source key must not exist as key in _derivatives', 500);
        }
        $this->setUpPathes();
    }
    public function setUpPathes()
    {
        $keys = array_keys($this->_derivatives);
        $keys[] = $this->_source['key'];
        foreach($keys as $dirName)
        {
            $dir = $this->_realPath.'/'.$dirName;
            /*
             * because of phps umask the permissions will propably be 0755 here
             * depends on the server settings
             * but 0755 is actually ok i guess
             */
            if( !is_dir($dir) && !mkdir($dir, 0777, true))
            {
                throw new Formation_MediaServer_Exception
                (
                    sprintf(
                        '"%1$s" is no dir and can\'t be created',
                        htmlspecialchars($dir)
                    ), 500
                );
            }
        }
    }

    /*
     * for usort
     */
    protected function _sortSources(array $a, array $b)
    {
        switch($this->_sortByKey)
        {
            case('name'):
                $valA = strtolower($a[$this->_sortByKey]);
                $valB = strtolower($b[$this->_sortByKey]);
                break;
            case('size'):
            case('date'):
                $valA = $a[$this->_sortByKey];
                $valB = $b[$this->_sortByKey];
                break;
            default:
                throw new Formation_MediaServer_Exception(
                    sprintf(
                        'unkown sort style "%1$s"',
                        htmlspecialchars($this->_sortByKey)
                    ), 500
                );
                break;
        }
        if ($valA == $valB)
        {
            return 0;
        }
        $sorted = ($valA < $valB) ? -1 : 1;
        return ($this->_sortReverse) ? ($sorted * -1) : $sorted;
    }
    public function getSources()
    {
        $offset = (func_num_args() > 0) ? (string) func_get_arg(0): '';
        $dirName = join('/', array(
            $this->_realPath,
            $this->_source['key'],
        ));
        $sourceFiles = array();
        $msgs = array();

        $dir = new DirectoryIterator($dirName);
        foreach ($dir as $fileInfo)
        {
            if(
                $fileInfo->isDir()
                || $fileInfo->isDot()
                /*
                 * ui uiui expensive heh
                 */
                 || !$this->isValidSource($fileInfo->getRealPath())


                 /*
                  * but now broken files are shown as well
                  * like wrong types or whatever a source must be
                  */
                || !$this->validFilename($fileInfo->getFilename())
                )
            {
                continue;
            }
            if(!$this->_sort)
            {
                $sourceFiles[] = $offset.$fileInfo->getFilename();
                continue;
            }
            $sourceFiles[] = array(
                'name'  => $fileInfo->getFilename(),
                'size'  => $fileInfo->getSize(),
                'date' => $fileInfo->getMTime(),
            );
        }
        if(!$this->_sort)
        {
            return $sourceFiles;
        }
        usort($sourceFiles, array($this, '_sortSources'));
        $return = array();
        foreach($sourceFiles as $file)
        {
            $return[] = $offset.$file['name'];
        }
        return $return;
    }

    public function getUsages()
    {
        if(empty($this->_usages))
        {
            $usages = array_merge(array($this->_source['key']), array_keys($this->_derivatives));
            $this->_usages = array();
            foreach($usages as $usage)
            {
                $this->_usages[$usage] = $usage;
            }
        }
        return $this->_usages;
    }
    public function getWebPath($source, $usage = Null)
    {
        $resourceArray = is_array($source)
            ? array_values($source)
            : Formation_MediaServer::getResourceArray($source);
        if( empty($usage) )
        {
            $usage = $this->_source['key'];
        }
        array_unshift($resourceArray ,$usage);
        /*
         * FIXME: make some options for missing sources
         * like returning false, throwing an eexception or returning some default image
         *
         * especially in production, we don't want the error to bubble up to the user!
         * catching the error would help
         * this function might better stay "raw" we could make a convinience function


         * its bad for performance to do this always...
        if(!$this->resourceExists($resourceArray))
        {
            throw new Formation_MediaServer_Exception
            (
                'Source not found! '
                .join("\n", $this->getMessages())
                , 404
            );
        }
        */
        return join('/', array(
            $this->_webPath,
            $resourceArray[0],
            $resourceArray[1])
        );
    }
    public function serve( $pathInfo = '' )
    {
        //throw new GC_Debug_Exception($this->getSources());
        //throw new GC_Debug_Exception($this->getUsages());
        $resourceArray = is_array($pathInfo)
            ? array_values($pathInfo)
            : Formation_MediaServer::getResourceArray($pathInfo);
        if( !$this->resourceExists($resourceArray) )
        {
            $messages = '';
            if($this->hasMessages())
            {
                $messages = join("\n", $this->getMessages());
            }
            throw new Formation_MediaServer_Exception
            (
                sprintf(
                    'resource not found (%1$s) %2$s',
                    htmlspecialchars(join(', ', $resourceArray)),
                    $messages
                ), 404
            );
        }
        /* if there is a file at $resourceArray this shouldn't have been called
         *      but if there is one we should serve that
         * else if a _derivative was requested
         *      we should get the source
         *      derive the derivative
         *          save it if that's turned on
         *              think about race conditions
         *  finally serve it
         *      thats setting the header
         *      and doing echo $imagickObjeckt;
         */
        $fileName = realpath(
            join('/',
                array(
                    $this->_realPath,
                    $resourceArray[0],
                    $resourceArray[1]
                )
            )
        );
        if($fileName && is_file($fileName))
        {
            $imagick = new Imagick($fileName);
        }
        else
        {
            $imagick = $this->_derive($resourceArray);
            if($this->_saveDerivedFiles)
            {
                $imagick->writeImage();
            }
        }
        $imageFormat = $imagick->getImageFormat();
        $contentType = array_key_exists($imageFormat, $this->_format2ContentTypeHeader)
            ? $this->_format2ContentTypeHeader[$imageFormat]
            : False;
        if(!$contentType)
        {
            throw new Formation_MediaServer_Exception
            (
                sprintf(
                    'content type for %1$s is missing',
                    $imageFormat
                ), 500
            );
        }
        header( 'Content-Type: '.$contentType );
        echo $imagick;
    }

    protected function _getSource($resourceArray)
    {
        //if ran after resourceExists this will work
        $fileName = realpath(
            join('/',
                array(
                    $this->_realPath,
                    $this->_source['key'],
                    $resourceArray[1]
                )
            )
        );
        return new Imagick($fileName);
    }
    protected function _derive($resourceArray)
    {
        $source = $this->_getSource($resourceArray);
        $newFileName = join(
            '/',
            array(
                $this->_realPath,
                $resourceArray[0],
                $resourceArray[1]
            )
        );
        $source->setImageFilename($newFileName);
        foreach($this->_derivatives[$resourceArray[0]] as $ruleName => $ruleValue)
        {
            $derive = 'derive'.ucFirst($ruleName);
            if(!method_exists($this, $derive))
            {
                throw new Formation_MediaServer_Exception
                (
                    sprintf(
                        'Derivator is missing. Method "%1$s" not in "%2$s"',
                        htmlspecialchars($derive),
                        className($this)
                    ), 500
                );
            }
            $this->$derive($source, $ruleValue);
        }
        return $source;
    }

    public function deriveResize( Imagick $imagick, array $values )
    {
        $imagick->scaleImage($values['width'], $values['height']);
    }
    public function getMessages($asString = False)
    {
        if($asString)
        {
            return join("\n", $this->_messages);
        }
        return $this->_messages;
    }
    public function hasMessages()
    {
        return ( count($this->_messages) > 0 );
    }


    /*this method seems very slow*/
    public function validateAllowedFormats( Imagick $imagick, array $values )
    {
        $image_format = $imagick->getImageFormat();

        if( !array_key_exists($image_format, $values) )
        {
            $translate = GC_Translate::get();
            $this->_messages[] = sprintf(
                $translate->_('The Image has a wrong Format (%1$s) is none of %2$s'),
                $image_format,
                join(', ',array_keys($values))
            );
            return False;
        }
        $filename = $imagick->getImageFilename();
        $extension = substr($filename , strrpos($filename, '.'));
        if( !$extension
        ||  ! in_array(
                strtolower($extension),
                array_map('strtolower', $values[$image_format]),
                True) )
        {
            $translate = GC_Translate::get();
            $this->_messages[] = sprintf(
                $translate->_('The Image has a wrong Extension (%1$s) is none of %2$s'),
                $extension,
                join(', ',$values[$image_format])
            );
            return False;
        }
        return True;
    }
    public function validateMinWidth( Imagick $imagick, $minWidth )
    {

        $width = $imagick->getImageWidth();
        if( $width < $minWidth )
        {
            $translate = GC_Translate::get();
            $this->_messages[] = sprintf(
                $translate->_('The Image is not wide enough %1$spx but minimum width is %2$spx.'),
                $width,
                $minWidth
            );
            return False;
        }
        return True;
    }
    public function validateAspectRatio( Imagick $imagick, $aspectRatio )
    {
        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();
        $rightHeight = (int) round($width / $aspectRatio);
        if( $height !== $rightHeight)
        {
            $translate = GC_Translate::get();
            $this->_messages[] = sprintf(
                $translate->_('The Image has the wrong height %1$spx but must be %2$spx high.'),
                $height,
                $rightHeight
            );
            return False;
        }
        return True;
    }


    public function validFilename($str = '')
    {
        // what is a valid filename for here
        // ad rules as you need
        // a more compact regex would maybe fit better
        if( !is_string($str)        //must be string
        || '' === $str              //must not empty string
        || 0 === strpos($str, '.')  //must not start with a dot
        || False !== strpos($str, '..')
        || False !== strpos($str, '/')
        || False !== strpos($str, '\\')
        )
        {
            $translate = GC_Translate::get();
            $this->_messages[] = sprintf(
                $translate->_('"%1$s" is no valid filename.'),
                htmlspecialchars($str)
            );
            return False;
        }
        return True;
    }
    /*
     *  $sourceString exists if
     *      - it doesn't start with a . (dot)
     *      - there is a source file named after $sourceString
     *      - the sourcefile meets the requirements of a source (is valid)
     *          otherwise its no source and thus not existant as source
     *                the question is sourceExists and NOT somethingExists
     */
    public function sourceExists( $sourceString )
    {
        /*
         * unsetting (and setting) messages is a side effect
         * got no idea for a better solution
         *      it will help to understand why $pathToFile is no valid SourceFile
         */

        $this->_messages = array();
        if(!$this->validFilename($sourceString))
        {
            return False;
        }
        $fileName = realpath(
            join('/',
                array(
                    $this->_realPath,
                    $this->_source['key'],
                    $sourceString
                )
            )
        );
        //realpath may return false
        if(!$fileName || !is_file($fileName))
        {
            $translate = GC_Translate::get();
            $this->_messages[] = sprintf(
                $translate->_('%1$s has no source file'),
                htmlspecialchars($sourceString)
            );
            return False;
        }
        if(!$this->isValidSource($fileName))
        {
            return False;
        }
        return True;
    }

    public function resourceExists( array $resourceArray )
    {
        /*
         * unsetting (and setting) messages is a side effect
         * got no idea for a better solution
         *      it will help to understand why $pathToFile is no valid SourceFile
         */
        $this->_messages = array();
        /*
         * if we would serve a directory listing
         * count($resourceArray) < 1 would propably be enough
         * but that's not implemented yet
         */
        if(count($resourceArray) < 2)
        {
            $translate = GC_Translate::get();
            $this->_messages[] = sprintf(
                $translate->_('not enough information in the request')
            );
            return False;
        }
        if(!array_key_exists($resourceArray[0], $this->getUsages()))
        {
            $translate = GC_Translate::get();
            $this->_messages[] = sprintf(
                $translate->_('%1$s is unknown as usage.'),
                htmlspecialchars($resourceArray[0])
            );
            return False;
        }
        /*
         * $resourceArray[0] exists
         * $resourceArray[1] exists if $this->sourceExists($resourceArray[1]);
         */
        return $this->sourceExists($resourceArray[1]);
    }
    public function isValidSource( $pathToFile )
    {
        /*
         * unsetting (and setting) messages is a side effect
         * got no idea for a better solution
         *      it will help to understand why $pathToFile is no valid SourceFile
         */
        $this->_messages = array();
        try
        {
            if(is_string($pathToFile))
            {
                if('' === $pathToFile || !is_file($pathToFile))
                {
                    $translate = GC_Translate::get();
                    $this->_messages[] = sprintf(
                        $translate->_('%1$s is no file'),
                        htmlspecialchars($pathToFile)
                    );
                    return false;
                }
                $imagick = new Imagick();
                $ping = $imagick->pingImage($pathToFile);
                if(!$ping)
                {
                    $translate = GC_Translate::get();
                    $this->_messages[] = sprintf(
                        $translate->_('Don\'t understand %1$s.'),
                        htmlspecialchars($pathToFile)
                    );
                    return False;
                }
            }
            else if($pathToFile instanceof Imagick)
            {
                $imagick = $pathToFile;
                $pathToFile = $imagick->getImageFilename();
            }
            else
            {
                $type = is_object($pathToFile)
                    ? 'object: '.className($pathToFile)
                    : gettype($pathToFile);
                $translate = GC_Translate::get();
                    $this->_messages[] = sprintf(
                        $translate->_('$pathToFile is neither a path to a file nor an Imagick Object but %1$s'),
                        $type
                    );
                return False;
            }
            //$image_info = $imagick->identifyImage();
            //thats a good one for debugging etc.
            foreach($this->_source['requirements'] as $ruleName => $ruleValue)
            {
                $validate = 'validate'.ucFirst($ruleName);
                if(!method_exists($this, $validate))
                {
                    throw new Formation_MediaServer_Exception
                    (
                        sprintf(
                            'Validator is missing. Method "%1$s" not in "%2$s"',
                            htmlspecialchars($validate),
                            className($this)
                        ), 500
                    );
                }
                if(!$this->$validate($imagick, $ruleValue))
                {
                    return False;
                };
            }
        }
        /*
         * don't catch the Formation_MediaServer_Exception here
         * the problem must be solved in here
         */
        catch(ImagickException $e)
        {
            /*
             * FIXME: LOG THIS SOMEWHERE
             *
             * it should be reported somewhere that there is an invalid file
             * for imagick dependent on when this method was called.
             * so the caller should report, or the first instance that knows the context
             */
            $translate = GC_Translate::get();
            $this->_messages[] = sprintf(
                $translate->_('Invalid file %1$s.'),
                htmlspecialchars($pathToFile)
            );
            //return False;
            throw $e;
        }
        return True;
    }
}
