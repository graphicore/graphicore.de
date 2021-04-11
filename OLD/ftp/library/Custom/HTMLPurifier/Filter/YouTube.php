<?php
class Custom_HTMLPurifier_Filter_YouTube extends HTMLPurifier_Filter
{

    public $name = 'YouTube';
    public function preFilter($html, $config, $context) {
        $pre_regex = '#<object[^>]+>.+?'.
            'http://www.youtube.com/v/([A-Za-z0-9\-_]+).+?</object>#s';
        $pre_replace = '<span class="youtube-embed">\1</span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }

    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="youtube-embed">([A-Za-z0-9\-_]+)</span>#';
        return preg_replace_callback($post_regex, array($this, 'postFilterCallback'), $html);
    }

    protected function armorUrl($url) {
        return str_replace('--', '-&#45;', $url);
    }

    protected function postFilterCallback($matches) {
        $url = $this->armorUrl($matches[1]);
        //pink
        //$url.= '&fs=1&color1=0xcc2550&color2=0xe87a9f&border=1';
        //light blue
        //$url.='&fs=1&color1=0x006699&color2=0x54abd6&border=1';
        $url.='&fs=1&color1=0xcc2550&color2=0xe87a9f';
        return
        '<object width="500" height="405" '
            .'type="application/x-shockwave-flash" '
            .'data="http://www.youtube.com/v/'.$url.'">'
            .'<param name="movie" value="http://www.youtube.com/v/'.$url.'"></param>'
            .'<param name="allowFullScreen" value="true"></param>'
            .'<param name="allowScriptAccess" value="always"></param>'
            .'<!--[if IE]>'
                .'<embed src="http://www.youtube.com/v/'.$url.'" '
                    .'type="application/x-shockwave-flash" '
                    .'allowfullscreen="true" '
                    .'allowScriptAccess="always" '
                    .'width="500" height="405">'
                .'</embed>'
            .'<![endif]-->'
        .'</object>';



    }
}

// vim: et sw=4 sts=4
