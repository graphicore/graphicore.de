<?php
class Custom_MediaServer_Images_Portfolio extends Formation_MediaServer_Images_Abstract
{
    protected $_saveDerivedFiles = True;//False;
    protected $_source = array
    (
        'key' => 'original',
        /*
         * something like
         *      must be bigger than
         *      and have an aspect ratio of
         */
        'requirements' => array(
                /*
                 * $imagick->getImageFormat() as key;
                 * allowed extensions as value array
                 */
                'allowedFormats' => array
                (
                    'GIF' => array('.gif'),
                    'JPEG' => array('.jpg','.jpeg'),
                    'PNG' => array('.png'),
                ),
                'minWidth' => 640,//in px
                /*
                 * there is no minHeigt
                 * height is derived from aspectRatio
                 * aspectRatio is true if
                 * $actualheight === (int) round($actualWidth/$aspectRatio);
                 */
                'aspectRatio' => 1.8,// 9/5 
        ),
    );
    protected $_format2ContentTypeHeader = array(
        'GIF' => 'image/gif',
        'JPEG' => 'image/jpg',
        'PNG' => 'image/png'
    );
    protected $_derivatives = array
    (
        'big'     => array('resize' => array('width' => 1000, 'height' =>   556)),
        'stage'   => array('resize' => array('width' =>  720, 'height' =>   400)), //whole
        'half'    => array('resize' => array('width' =>  354, 'height' =>   197)), //half
        'team'    => array('resize' => array('width' =>  232, 'height' =>   129)), //third
        'preview' => array('resize' => array('width' =>  171, 'height' =>    95)), //fourth
        'sixth'   => array('resize' => array('width' =>  110, 'height' =>    61)), //sixth
        'thumb'   => array('resize' => array('width' =>   80, 'height' =>    44))  //eighth
    );
}
