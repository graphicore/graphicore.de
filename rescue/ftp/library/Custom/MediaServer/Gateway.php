<?php
class Custom_MediaServer_Gateway
extends Formation_MediaServer_Gateway_Abstract
{
    /*
     * pathes like these:
     * portfolio/images/
     * portfolio/videos/
     * portfolio/plaintext/
     */
    protected $_resources = array(
        'portfolio' => array
        (
            'images' => 'Custom_MediaServer_Images_Portfolio',
            /*
             * To become real
             * 'videos' => 'Custom_MediaServer_Videos_Portfolio',
             */
        ),
    );
}
