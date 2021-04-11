<?php
class Custom_HTMLPurifier_Filter_Vimeo extends HTMLPurifier_Filter
{

    public $name = 'Vimeo';
    public function preFilter($html, $config, $context) {
        $pre_regex = '#<object[^>]+>.+?'.
            'http://vimeo.com/moogaloop.swf\?clip_id=([A-Za-z0-9\-_]+).+?</object>#s';
        $pre_replace = '<span class="vimeo-embed">\1</span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }

    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="vimeo-embed">([A-Za-z0-9\-_]+)</span>#';
        return preg_replace_callback($post_regex, array($this, 'postFilterCallback'), $html);
    }
    protected function armorUrl($url) {
        return str_replace('--', '-&#45;', $url);
    }
    protected function postFilterCallback($matches) {
        $url = $this->armorUrl($matches[1]);
        //custom styling and such
        $url .= '&amp;server=vimeo.com'
            .'&amp;show_title=1'
            .'show_byline=0'
            .'&amp;show_portrait=0'
            .'&amp;color=e2007a'
            .'&amp;fullscreen=1';
        $src = 'http://vimeo.com/moogaloop.swf?clip_id='.$url;
        return
        '<object width="500" height="281" '
            .'type="application/x-shockwave-flash" '
            .'data="'.$src.'">'
            .'<param name="allowfullscreen" value="true" />'
            .'<param name="allowscriptaccess" value="always" />'
            .'<param name="allowscriptaccess" value="always" />'
            .'<param name="movie" value="'.$src.'" />'
            .'<!--[if IE]>'
                .'<embed src="'.$src.'" '
                    .'type="application/x-shockwave-flash" '
                    .'allowfullscreen="true" '
                    .'allowScriptAccess="always" '
                    .'width="500" height="281">'
                .'</embed>'
            .'<![endif]-->'
        .'</object>';
    }
}