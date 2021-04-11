<?php
/*
 * some convenience methods
 */
class Formation_MediaServer
{
    private function __construct(){
        throw new Formation_MediaServer_Exception(
            sprintf('class %1$s is static',
                    __CLASS__
                ), 500
        );
    }
    static public function getResourceArray($pathInfo = '')
    {
        if(!is_string($pathInfo))
        {
            throw new Formation_MediaServer_Exception(
                sprintf(
                    '$pathInfo must be string but is %1$s',
                    gettype($pathInfo)
                ), 500
            );
        }
        /* remove double and multi slashes*/
        $pathRequested =  preg_replace('/\/+/', '/', $pathInfo);
        $requested = trim($pathRequested, '/');
        return explode('/', $requested);
    }
    static public function checkRealPath($realPath)
    {
        if(realpath($realPath) !== $realPath)
        {
            throw new Formation_MediaServer_Exception
            (
                sprintf
                (
                    '$realPath is not realpath($realPath) $realPath was "%1$s"',
                    htmlspecialchars($realPath)
                ), 500
            );
        }
        if(!is_dir($realPath))
        {
            throw new Formation_MediaServer_Exception
            (
                sprintf
                (
                    '$realPath "%1$s" is no directory',
                    htmlspecialchars($realPath)
                ), 500
            );
        }
        return True;
    }
}
