<?php
interface Formation_MediaServer_Interface
{
    public function __construct($realPath, $webPath);
    public function serve($pathInfo = '');
    public function resourceExists( array $resourceArray);
    public function sourceExists( $sourceString );
    public function getSources();
    public function getWebPath($source, $usage = Null);
    public function getMessages($asString = False);
    public function hasMessages();
}
