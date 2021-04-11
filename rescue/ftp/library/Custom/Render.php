<?php
/*
 * This is something like the Template script for this page
 * its not elegant but everything is in one place
 * contrary to the most Templating solutions it *tries* to be DRY "Don't Repeat Yourself"
 */
class Custom_Render
{
    protected $_helperUrl;
    protected $_baseUrl;
    protected $_ImgGetter;
    protected $_host;
    //'vimeo' => '#%(vimeo)\s*:\s*([0-9]+)%#iu',
    //'youtube' => '#%(youtube)\s*:\s*([A-Z0-9\-_]+)%#iu',
    public $urlArrays = array
    (
        'filter' => array(
            'module' => 'default',
            'controller' => 'index',
            'action' => 'diary',
        ),
        'diaryEntry' => array(
            'module' => 'default',
            'controller' => 'index',
            'action' => 'archive'
        ),
        'page' => array(
            'module' => 'default',
            'controller' => 'index',
            'action' => 'page'
        ),
    );
    protected $_specials = array(
        'patterns' => array(
            //'noPre' => '#(<pre[^>]*>.*?)%(.*?)%(.*?</pre\s*>)#siu',
            'vimeo' => '#%(vimeo)\s*:\s*([0-9]+)%#iu',
            'youtube' => '#%(youtube)\s*:\s*([A-Z0-9\-_]+)%#iu',
            'paypal' => '#%(donate)\s*:\s*([A-Z0-9]+)%#iu',
            //'inject' => '#%(inject)\s*:\s*([A-Z0-9\-_.]+)%#iu',
            //'local' => '#%(local)\s*:\s*([A-Z0-9\-_]+)%#iu',
            //'picasa' => '#%(picasa)\s*:\s*([A-Z0-9\-._]+)\s*:\s*([A-Z0-9\-._]+)%#iu',//%picasa : lassefister : 5462951217841410689%
            //'flickr' => '#%(flickr)\s*:\s*([0-9]+)%#iu',
        ),
        'replacements' => array(
            //'noPre' => '$1&#37;$2&#37;$3 !',
            //'vimeo' => '',
            //'youtube' => '',
            //'paypal' => '',
            //'local' => '',
            //'flickr' => ''

        ),
        'callbacks' => array(
        ),
    );
    protected $_theAllFiltersFilter = Null;
    public function addSpecialsCallback($callback, $regex)
    {
        if(!is_callable($callback))
        {
            throw new GC_Exception('callback is not callable');
        }
        $this->_specials['callbacks'][] = array(
            'callback' => $callback,
            'regex' => $regex,
        );
    }
    public function __construct()
    {
        $translate = GC_Translate::get();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $this->_baseUrl =  $request->getBaseUrl();
        //object tags are not allowed inside of pre tags
        //we replcace matches for % width &#37; preventing

        $this->_specials['callbacks'][] = array(
            'callback' => array($this, 'protectMatches'),
            'regex' => '#<pre[^>]*>.*?(%.*?:.*?%)+.*?</pre\s*>#siu',
        );
        $this->_specials['callbacks'][] = array(
            'callback' => array($this, 'twitterGetCached'),
            'regex' => '#%(twitter)\s*:\s*([A-Z0-9\-_]+)%#iu',//username
        );
        $this->_specials['callbacks'][] = array(
            'callback' => array($this, 'linker'),
            'regex' => '#@(linker)/([/A-Z0-9\-_+]+)@#iu',//tricky
        );
        $this->_specials['callbacks'][] = array(
            'callback' => array($this, 'inject'),
            'regex' => '#%(inject)\s*:\s*([A-Z0-9\-_.\\\/]+)%#iu',//a file in the injections folder
        );
        $this->_specials['callbacks'][] = array(
            'callback' => array($this, 'htmlUserId'),
            'regex' => '#(uid)_([A-Z0-9\-_]+)#iu',//a file in the injections folder
        );

/* old, width embed etc.*/
        $src = 'http://vimeo.com/moogaloop.swf?clip_id=$2'
        //some styling
            .'&amp;server=vimeo.com'
            .'&amp;show_title=1'
            .'&amp;show_byline=0'
            .'&amp;show_portrait=0'
            .'&amp;color=e2007a'
            .'&amp;fullscreen=1'
            ;
        //height used to be 281 before
        $this->_specials['replacements']['vimeo'] =
        '<object width="584" height="384" '
            .'type="application/x-shockwave-flash" '
            .'data="'.$src.'">'
            .'<param name="wmode" value="opaque" />'
            .'<param name="allowfullscreen" value="true" />'
            .'<param name="allowscriptaccess" value="always" />'
            .'<param name="movie" value="'.$src.'" />'
            .'<!--[if IE]>'
                .'<embed src="'.$src.'" '
                    .'type="application/x-shockwave-flash" '
                    .'allowfullscreen="true" '
                    .'allowScriptAccess="always" '
                    .'width="584" height="384">'
                .'</embed>'
            .'<![endif]-->'
        .'</object>';

/*
        //the new style from vimeos oEmbed
        $src = 'clip_id=$2'
        //some styling
            .'&amp;server=vimeo.com'
            .'&amp;show_title=1'
            .'&amp;show_byline=0'
            .'&amp;show_portrait=0'
            .'&amp;color=e2007a'
            .'&amp;fullscreen=1'
            ;
        //height used to be 281 before
        $this->_specials['replacements']['vimeo'] =
        '<object type="application/x-shockwave-flash" '
            .'data="http://vimeo.com/moogaloop.swf" '
            .'width="500" height="375">'
            .'<param name="allowscriptaccess" value="always"/>'
            .'<param name="allowfullscreen" value="true"/>'
            .'<param name="movie" value="http://vimeo.com/moogaloop.swf"/>'
            .'<param name="flashvars" value="'.$src.'"/>'
            .'<!--[if lt IE 8]>'
                .'<embed src="http://vimeo.com/moogaloop.swf?'.$src.'" '
                    .'type="application/x-shockwave-flash" '
                    .'allowfullscreen="true" '
                    .'allowScriptAccess="always" '
                    .'width="500" height="375">'
                .'</embed>'
            .'<![endif]-->'
        .'</object>';
*/

        $src ='http://www.youtube.com/v/$2'
            //and some styling
            .urlencode('&fs=1&color1=0xcc2550&color2=0xe87a9f');
        $this->_specials['replacements']['youtube'] =
        '<object width="584" height="384" '
            .'type="application/x-shockwave-flash" '
            .'data="'.$src.'">'
            .'<param name="wmode" value="opaque" />'
            .'<param name="movie" value="'.$src.'"></param>'
            .'<param name="allowFullScreen" value="true"></param>'
            .'<param name="allowScriptAccess" value="always"></param>'
            .'<!--[if IE]>'
                .'<embed src="'.$src.'" '
                    .'type="application/x-shockwave-flash" '
                    .'allowfullscreen="true" '
                    .'allowScriptAccess="always" '
                    .'width="584" height="384">'
                .'</embed>'
            .'<![endif]-->'
        .'</object>';

        $this->_specials['replacements']['paypal'] =
        '<form class="ppDonate" action="https://www.paypal.com/cgi-bin/webscr" method="post"><div>'
            .'<input type="hidden" name="cmd" value="_s-xclick"/>'
            .'<input type="hidden" name="hosted_button_id" value="$2"/>'
            .'<button title="'.$translate->_('Donate with PayPal.').'" name="submit" type="submit"><span>'.$translate->_('Donate').'</span></button>'
            .'<img alt="" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1"/>'
        .'</div></form>';
    }
    protected $_addSpecialsUrl = Null;
    public function addSpecials(array $url, $str = '')
    {
        $this->_addSpecialsUrl = $url;
        foreach($this->_specials['callbacks'] as $cb)
        {
            $str = preg_replace_callback($cb['regex'], $cb['callback'], $str);
        }
        $this->_addSpecialsUrl = Null;
        return preg_replace($this->_specials['patterns'], $this->_specials['replacements'], $str);
    }
    //callbacks for addSpecials
    public static function protectMatches(array $matches)
    {
        return preg_replace('#%#u', '&#37;', $matches[0]);
    }
    public function htmlUserId(array $matches)
    {
        // #@(uid_))([/A-Z0-9\-_]*)@#iu
        return 'uid_'.$this->uniqueHtmlID($this->_addSpecialsUrl).'_'.$matches[2];
    }
    public function linker(array $matches)
    {
        // #@(linker)/([/A-Z0-9\-_]*)@#iu
        $keyWords = array
        (
            'images' => $this->_baseUrl.'/images/',
            'download' => $this->getHost().$this->_baseUrl.'/downloads/',
            'switchGrid' => "javascript: dojo.toggleClass(dojo.body(),'grid')",
        );
        if(array_key_exists( $matches[2], $keyWords))
        {
            return $keyWords[$matches[2]];
        }
        return $this->localUrl($matches);

    }
    public function inject(array $matches)
    {
        $rawInjectionsDir = Zend_Registry::get('config')->injectionsDir;
        $injectionsDir = realpath($rawInjectionsDir);
        if(!$rawInjectionsDir || !$injectionsDir)
        {
            return $matches[0];
        }
        $raw = $injectionsDir.'/'.$matches[2];
        $file  = realpath($raw);
        if(
            !$file //non existant
            || !($injectionsDir == mb_substr($file, 0, mb_strlen($injectionsDir))) //not in injections dir
            || (mb_stripos($file,'/.') !== False) //hidden
            || !is_file($file)
            || !is_readable($file)
        )
        {
            return sprintf('not found(%1$s)',$matches[0]);
        }
        return file_get_contents($file);
    }

    public function localUrl(array $matches)
    {
        $routeName = 'i18n';
        $default = array(
            'i18n' => array
            (
                'lang' => GC_I18n::getLang(),
                'action'=> 'index',//default
                'key' => Null,
            ),
            'modules_i18n' => array
            (
                'module' => 'default',
                'controller' => 'index',
                'action' => 'index',
                'lang' => GC_I18n::getLang(),
            )
        );
        $special = array
        (
            'images' => 'images'
        );
        $host = '';
        $vals = explode('/', $matches[2]);

        $values = array();
        for($i = 0; $i < count($vals); $i++)
        {
            $keyValue = explode('+', $vals[$i]);
            if(count($keyValue) > 1)
            {
                $values[$keyValue[0]] = $keyValue[1];
            }
        };
        if(array_key_exists('host', $values) && mb_strtolower($values['host']) == 'absolute')
        {
            $host = $this->getHost();
            unset($values['host']);
        }

        if(array_key_exists('special', $values) && array_key_exists($values['special'], $special))
        {
            return $host.$this->_baseUrl.'/'. $special[$values['special']].'/';
        }

        if(array_key_exists('route', $values) && array_key_exists($values['route'], $default))
        {
            $routeName = $values['route'];
            unset($values['route']);
        }
        $route = $default[$routeName];
        foreach($values as $key => $val)
        {
            if(array_key_exists($key, $route))
            {
                $route[$key] = $val;
            }
        }
        return $host.$this->url($route, $routeName, False);
    }
    public static function twitterGetCached(array $matches)
    {
        $screenName = $matches[2];
        $cacheID = 'twitter_status_userTimeline_'.GC_I18n::getLang().'_'.$screenName;

        $twitterCache = Zend_Registry::get('config')->twitterCache;
        $frontendOptions = $twitterCache->frontendOptions->toArray();
        if ('development' === APPLICATION_ENV)
        {
            $frontendOptions['logger'] = new Zend_Log(new Zend_Log_Writer_Firebug());
            $frontendOptions['logging'] = true;
        }
        $backendOptions = $twitterCache->backendOptions->toArray();
        $cache = Zend_Cache::factory
        (
            $twitterCache->frontendName,
            $twitterCache->backendName
            ,$frontendOptions
            ,$backendOptions
        );
        $twitter = $cache->load($cacheID);
        #so if we have heavy load load and twitter is down we are dos attacking them
        $RetryId = 'Retry_'.$cacheID;
        $lastTry = (int) $cache->load($RetryId);
        $allowRetry = (time() > $twitterCache->requestRetry + $lastTry);
        if($allowRetry)
        {
            //if twitter is not available the cache will stay valid
            $cacheMeta = $cache->getMetadatas($cacheID);

            if(!is_array($cacheMeta)){$cacheMeta = array('mtime'=> 0);}
            if( (time() > $cacheMeta['mtime'] + $twitterCache->timeout)
            || !$cache->test($cacheID) )
            {
                $cache->save((string)time(), $RetryId);
                $newTwitter = self::getTwitter($screenName);
                if($newTwitter)
                {
                    $safed = $cache->save($newTwitter, $cacheID);
                    $twitter = $newTwitter;
                }
            }
        }
        //if (!$cache->test($cacheID))
        //{
        //    $twitter = self::getTwitter($screenName);
        //    $safed = $cache->save($twitter, $cacheID);
        //}
        return $twitter;
    }
    public static function getTwitter($screenName)
    {
        $twitter = new Zend_Service_Twitter();
        //Requires Authentication (about authentication): true, if requesting a protected user's timeline
        try
        {
            $response = $twitter->status->userTimeline
            (
                array('screen_name' => $screenName)
            );

        }
        catch(Zend_Http_Client_Adapter_Exception $e)
        {
            return '';
        }
        if(!isset($response->status))
        {
            return '';
        }
        $messages = array();
        foreach($response->status as $message)
        {
            $date = new Zend_Date(strtotime($message->created_at));//Wed Nov 18 18:36:34 +0000 2009;
            $date->setTimeZone(Zend_Registry::get('config')->timezone);
            $messages[] = sprintf('<span class="text">%1$s</span> <span class="created_at">%2$s</span>',
                self::twitterDecorate($message->text),
                $date->toString(Zend_Date::DATETIME_FULL)
            );
        }
        if(empty($messages))
        {
            return '';
        }
        $checkoutTime = new Zend_Date();
        $result = '<ul class="twitterStatus"><!--fetched at '.$checkoutTime->toString().'--><li class="tweet">'.join('</li><li>', $messages).'</li></ul>';
        return self::protectMatches(array($result));
    }
    public static function twitterDecorate($tweet)
    {
        return preg_replace(
            array
            (
                'urls' => '#(http|https|ftp)://([A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&\?/.=]+)#iu',//is relatively simple...
                'userNames' => '#\s@([A-Z0-9_]+)#iu',
                'hashTags' => '/\B#([äöüßA-Za-z0-9-_]+)/iu',
            ),
            array
            (
                'urls' => '<a href="$0">$0</a>',
                'userNames' => ' <a href="http://twitter.com/$1">@$1</a>',
                'hashTags' => ' <a href="http://twitter.com/search?q=%23${1}">#${1}</a>'
            ),
            $tweet);
    }

/*
    public static function twitterRelativeTime(time_value)
    {
        var values = time_value.split(" ");
        time_value = values[1] + " " + values[2] + ", " + values[5] + " " + values[3];
        var parsed_date = Date.parse(time_value);
        var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
        var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);
        delta = delta + (relative_to.getTimezoneOffset() * 60);
        if (delta < 60) {
            return 'less than a minute ago';
        } else if(delta < 120) {
            return 'about a minute ago';
        } else if(delta < (60*60)) {
            return (parseInt(delta / 60)).toString() + ' minutes ago';
        } else if(delta < (120*60)) {
            return 'about an hour ago';
        } else if(delta < (24*60*60)) {
            return 'about ' + (parseInt(delta / 3600)).toString() + ' hours ago';
        } else if(delta < (48*60*60)) {
            return '1 day ago';
        } else {
            return (parseInt(delta / 86400)).toString() + ' days ago';
        }
    }
*/
    protected function _getImgGetter()
    {
        if(!$this->_ImgGetter)
        {
            $this->_ImgGetter = new Custom_Image();
        }
        return $this->_ImgGetter;
    }

    public function url()
    {
        $passedArgs = func_get_args();
        if(!$this->_helperUrl)
        {
            $this->_helperUrl = new Zend_View_Helper_Url();
        }
        return call_user_func_array(array($this->_helperUrl, 'url'), $passedArgs);
    }
    public function uniqueHtmlID(array $urlArray, $prefix = Null)
    {
        if(!$prefix)
        {
            $prefix = Zend_Registry::get('config')->htmlIdPrefix;
        }
        return $prefix.preg_replace(
            '/[^A-Za-z0-9._:\-]/',
            ':',
            $this->url(array_merge($urlArray, array('lang' => GC_I18n::getLang())), 'i18n', true)
        );
    }
    public function getHost()
    {
        if(!$this->_host)
        {
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $this->_host = $request->getScheme().'://'.$request->getHttpHost();
        }
        return $this->_host;
    }
//    public function addThis($url, $title, $description)
//    {
//        $url = $this->getHost().$url;
//        $translate = GC_Translate::get();
//        $api = 'http://api.addthis.com/oexchange/0.8';
//        //the add this menu
//        //This endpoint renders a full-page sharing menu with buttons for all of our destination services
//        $offer = $api.'/offer?%2$s';
//        //Share Forward
//        //This endpoint redirects the browser to the specified site to complete a share. Use this endpoint when you know which URL is being shared and the desired destination.
//        $share = $api.'/forward/%1$s/offer?%2$s';//, %1$s = SERVICE_CODE, http://www.addthis.com/services/list
//        $dataArr = array();
//        foreach(array(
//            'url' => $url,
//            'title' => $title,
//            'description' => $description,
//            //'username' => 'lassefister',
//        ) as $key => $value)
//        {
//            $dataArr[] = $key.'='.urlencode($value);
//        }
//
//        $services = array(
//            'twitter' => array(
//                'moreData' => '&amp;template='.$translate->_('Look at this:').' {{title}}: {{url}}',
//                'text' => 'tweet this!',
//                'name' => 'Twitter',
//            ),
//            'identica' => array
//            (
//                'name' => 'identi.ca',
//            ),
//            'delicious' => array
//            (
//                'text' => $translate->_('Bookmark this on Delicious'),
//                'name' => 'Delicious',
//            ),
//            'digg' => array
//            (
//                'text' => 'digg this!',
//                'name' => 'Digg',
//            ),
//            'hackernews' => array
//            (
//                'name' => 'Hacker News',
//            ),
//            'reddit' => array(
//                'text' => 'reddit this!',
//                'name' => 'Reddit',
//            ),
//            'facebook' => array
//            (
//                'name' => 'Facebook',
//            ),
//            'googlebuzz' => array
//            (
//                'name' => 'Google Buzz',
//            ),
//            'thewebblend' => array
//            (
//                'text' => 'Blend It!',
//                'name' => 'The Web Blend',
//            ),
//            'stumbleupon' => array
//            (
//                'name' => 'StumbleUpon',
//            ),
//            'misterwong' => array(
//                'text' => $translate->_('Add this page to Mister Wong'),
//                'name' => 'Mister Wong',
//            ),
//            'addthis' => array(
//                'format' => $offer,
//                'name' => 'AddThis',
//                'text' => $translate->_('Share this with AddThis'),
//            ),
//        );
//
//        $link = '<a class="sharelink %3$s" title="%1$s" href="%2$s"><span>%1$s</span></a>';
//        $result = array();
//
//        $textFormat = $translate->_('Share this on %1$s');
//        $simpleData = join('&amp;', $dataArr);
//        foreach($services as $key => $service)
//        {
//            if(is_int($key))
//            {
//                $format = $share;
//                $key = $service;
//                $service = array();
//            }
//            $name = array_key_exists('name', $service) ? $service['name'] : $key;
//            $format = array_key_exists('format', $service) ? $service['format'] : $share;
//            $text = array_key_exists('text', $service) ? $service['text'] : sprintf($textFormat, $name);
//            $data = array_key_exists('moreData', $service) ? $simpleData.$service['moreData'] : $simpleData;
//
//            $result[] = sprintf($link,
//                $text,
//                sprintf($format, $key, $data),
//                $key
//            );
//        }
//        return sprintf('<ul class="sharelinks"><li>%1$s</li></ul>', join('</li><li>', $result));
//    }
    public static function getTranslationKey(array $keys)
    {
        //the actual language
        $key = GC_I18n::getLang();
        if(in_array($key, $keys))
        {
            return $key;
        }
        //the first key
        //the query should return first the oldest translation aka first version of the article
        $key = $keys[0];
        if(GC_I18n::isLocale($key))
        {
            return $key;
        }
        //at the moment here may not be an "empty" story
        throw new Custom_Exception('Fix Database Query, here may not be an Element without Default language');
    }

    public function staticPage(array $data)
    {
        //$translate = GC_Translate::get();
        $entry = $this->_getStatic($data);
        $return = array('title' => $entry['title']);
        $return['content'] = sprintf(
            '<div class="contentContainer staticContainer" xml:lang="%2$s" lang="%2$s">%1$s</div>',
            $entry['htmlContent'],
            $entry['langAttr']
            //<h1>%3$s</h1>
            //,($entry['published'])
            //    ? $entry['title']
            //    : sprintf('(%1$s)<span title="this is not published">*</span>', $entry['title'])
        );
        return $return;
    }
    protected function _getStatic(array $data)
    {
        /*
         * extracting:
         *      urlAlias
         *      title
         *      htmlContent
         *      langAttr
         */

        $item = array();
        $lang = self::getTranslationKey(array_keys($data['Translation']));

        $item['urlId'] = $data['urlId'];
        $item['published'] = $data['Translation'][$lang]['published'];

        $item['published'] = $data['Translation'][$lang]['published'];
        $item['title'] = $data['Translation'][$lang]['title'];
        $item['langAttr'] = is_string($lang) ? $lang : GC_I18n::getDefaultLang();

        $url = $this->urlArrays['page'];
        $url['key'] =  $item['urlId'];

        $item['htmlContent'] = is_string($lang)
            /*inject some objects*/
            ? $this->addSpecials($url, $data['Translation'][$lang]['htmlContent'])
            : '';
        return $item;
    }


    /*
     * $data ia an array of diaryEntries
     */
    public function diary(array $data, array $metaData, array $filters = Null, $pager = '')
    {
        $diary = $this->_getDiary($data, $filters);
        if('' !== $pager)
        {
            if($pager instanceof Doctrine_Pager_Layout)
            {
                $pager = $this->renderPager($pager);
            }
            if(is_string($pager))
            {
                $pager = '<ul class="pager">'.$pager.'</ul>';
            }
        }

        $return = array();
        //no lang attribute is set, its set on each entry
        $return['content'] = sprintf(
            '<div class="contentContainer diaryList">%3$s%1$s%2$s</div>',
            $diary['htmlContent'],
            $pager,
            array_key_exists('title', $metaData) ? '<h1>'.$metaData['title'].'</h1>' : ''
        );

        return $return;
    }
     /*
     * $data ia an array of diaryEntries
     */
    public function diaryLatest(array $data, array $metaData, array $filters = Null)
    {
        $diary = $this->_getDiary($data, $filters);
        $return = array();
        //no lang attribute is set, its set on each entry
        $return['content'] = sprintf(
            '<div class="latest diaryList">%2$s%1$s</div>',
            $diary['htmlContent'],
            array_key_exists('title', $metaData) ? '<h1>'.$metaData['title'].'</h1>' : ''
        );

        return $return;
    }
    /*
     * $data is an array of diaryEntries
     */
    protected function _getDiary(array $data, array $filters = Null)
    {
        $items = array();
        foreach(array_keys($data) as $entryKey)
        {
            $entry = $this->diaryEntryTeaser($data[$entryKey], $filters, 'diaryEntryTeaser');
            $items[] = $entry['content'];
        }
        return array('htmlContent' => sprintf('<ol class="entries">%1$s</ol>', join('', $items)));
    }

    public function diaryEntryTeaser(array $data, array $filters = Null, $classes = False)
    {
        $entry = $this->_getDiaryEntry($data, $filters);
        $return = array(
            'title' => $entry['title'],
            'urlId' => $entry['urlId'],
        );

        try
        {
            $imgSrc = $this->_getImgGetter()->getImageUrl(GC_I18n::getLang(), 'diaryTeaser', $entry['urlId'], Null, False);
            $img  = ($imgSrc) ? sprintf('<img alt="%2$s" src="%1$s" />', $imgSrc, basename($imgSrc)) : '';
            //$img = '';
        }
        catch(GC_Image_Exception $e)
        {
            $img  = '';
        }

        $return['content'] =sprintf(
            '<li class="%5$s" xml:lang="%2$s" lang="%2$s">'
                .'<a class="entryLink" href="%6$s">'
                    .'%4$s'
                    .'<span class="title">%3$s</span> '
                    .'<span class="teaser">%1$s</span>'
                .'</a>'
                .'%7$s'
            .'</li>',
            $entry['teaser'], //1
            $entry['langAttr'],//2
            ($entry['published'])//3
                ? $entry['title']
                : sprintf('(%1$s)<span title="this is not published">*</span>', $entry['title']),
            $img,//4
            is_string($classes) ? $classes : 'contentContainer diaryEntryTeaser',//5
            $entry['url'],//6
            $this->diaryEntryFooter($entry['timestamp'], $entry['filters'], $entry['url'])//7
        );
        return $return;
    }

    public function feed(array $data)
    {
        $return = array();
        foreach(array_keys($data) as $entryKey)
        {
            $entry = $this->_getDiaryEntry($data[$entryKey]);
            $entry['htmlContent'] = sprintf(
                '<div class="diaryEntryContainer" xml:lang="%2$s" lang="%2$s"><h1>%3$s</h1>%1$s</div>',
                $entry['htmlContent'],
                $entry['langAttr'],
                ($entry['published']) ? $entry['title'] : sprintf('(%1$s)<span title="this is not published">*</span>', $entry['title'])

            );
            $return[] = $entry;
        }
        return $return;
    }

    //render an container around the page for stand allone representation
    public function diaryEntry(array $data, array $filters = Null, $classes = False)
    {

        $entry = $this->_getDiaryEntry($data, $filters);
        $return = array(
            'title' => $entry['title'],
            'urlId' => $entry['urlId'],
        );
        $return['content'] =sprintf(
            '<div class="%4$s" xml:lang="%2$s" lang="%2$s">'
                .'<h1>%3$s</h1>'
                .'<p class="teaser">%5$s</p>'
                .'%1$s'
                .'%6$s'
            .'</div>',
            $entry['htmlContent'],
            $entry['langAttr'],
            ($entry['published'])
                ? $entry['title']
                : sprintf('(%1$s)<span title="this is not published">*</span>', $entry['title']),
            is_string($classes) ? $classes : 'contentContainer diaryEntry',
            $entry['teaser'],
            $this->diaryEntryFooter(
                $entry['timestamp'],
                $entry['filters'],
                $entry['url']
//                ,$this->addThis($entry['url'], $entry['title'], $entry['teaser'])
                )
        );
        return $return;
    }
    public function diaryEntryFooter($timestamp, array $filters, $url, $inject = '')
    {
        $translate = GC_Translate::get();
        $literalFilters = $translate->_('Filters');
        $literalPermalink = $translate->_('Permalink');

        $date = new Zend_Date($timestamp, Zend_Date::ISO_8601);
        $date->setTimeZone(Zend_Registry::get('config')->timezone);
/*
        $dateString = sprintf(
            '%1$s (%2$s, %3$s, UTC%4$s)',
            $date,
            $date->get(Zend_Date::TIMEZONE),
            $date->get(Zend_Date::TIMEZONE_NAME),
            $date->get(Zend_Date::GMT_DIFF_SEP)
        );
*/

        $dateString = $date->toString(Zend_Date::DATETIME_FULL);
        return sprintf('<div class="diaryEntryFooter">%2$s %1$s%3$s%4$s</div>',
            $dateString,
            sprintf('<a class="permalink" href="%1$s">%2$s</a>', $url, $literalPermalink),
            count($filters)
                ? sprintf('<div class="filters">%2$s: <ul><li>%1$s</li></ul></div>',
                    join('</li><li>', $filters),
                    $literalFilters)
                : '',
            $inject
        );
    }

    //return an array with certain keys
    protected function _getDiaryEntry(array $data, array $filters = Null)
    {
        /*
         * extracting:
         *      title
         *      htmlContent
         *      langAttr
         *      urlId
         *      published
         *      timestamp
         *      updated_at
         *      created_at
         */
        $translate = GC_Translate::get();
        $item = array();
        $lang = self::getTranslationKey(array_keys($data['Translation']));
        $item['timestamp'] = $data['timestamp'];
        $item['updated_at'] = ($data['updated_at'] > $data['Translation'][$lang]['updated_at']) ? $data['updated_at'] : $data['Translation'][$lang]['updated_at'];
        $item['created_at'] = ($data['created_at'] > $data['Translation'][$lang]['created_at']) ? $data['created_at'] : $data['Translation'][$lang]['created_at'];


        $item['tagUrlPart'] = $data['tagUrlPart'];
        $item['langAttr'] = is_string($lang) ? $lang : GC_I18n::getDefaultLang();
        $item['title'] = $data['Translation'][$lang]['title'];
        $item['urlId'] = $data['urlId'];

        $item['teaser'] = $data['Translation'][$lang]['teaser'];
        $item['published'] = $data['Translation'][$lang]['published'];

        $url = $this->urlArrays['diaryEntry'];
        $url['key'] =  $item['urlId'];
        $item['url'] = $this->url($url, 'i18n', True);

        /*inject some objects*/
        $item['htmlContent'] = $this->addSpecials($url, $data['Translation'][$lang]['htmlContent']);

        //$theAllFiltersFilter = $this->getTheAllFiltersFilter();
        //$item['filters'] = array($theAllFiltersFilter['link']);

        //ad the filters to the $item
        $item['filters'] = array();
        if(!is_array($filters)
        || (!array_key_exists('Filters', $data) || !is_array($data['Filters']))
        )
        {
            $item['filters'] = array($translate->_('none'));
            return $item;
        }
        foreach($data['Filters'] as $filter)
        {
            if(array_key_exists($filter['id'], $filters))
            {
                $item['filters'][] = $filters[$filter['id']]['link'];
            }
        }
        return $item;
    }

    public function getTheAllFiltersFilter()
    {
        if(!$this->_theAllFiltersFilter)
        {
            $linkPattern = '<a href="%1$s">%2$s</a>';
            $translate = GC_Translate::get();
            $this->_theAllFiltersFilter = array(
                'name' => $translate->_('all Entries')
            );
            $url = $this->urlArrays['filter'];
            $this->_theAllFiltersFilter['url'] =  $this->url($url, 'i18n', True);
            $this->_theAllFiltersFilter['link'] =  sprintf(
                $linkPattern,
                $this->_theAllFiltersFilter['url'],
                $this->_theAllFiltersFilter['name']
            );
        }
        return $this->_theAllFiltersFilter;
    }
    public function filters(array $data)
    {
        ///<a rel="profile" href="http://microformats.org/wiki/rel-tag-profile">
        //http://microformats.org/wiki/rel-tag-profile
        //
        //$linkPattern = '<a rel="tag" title="%3$s" href="%1$s">%2$s</a>';
        //no title
        $linkPattern = '<a rel="tag" href="%1$s">%2$s</a>';
        $url = $this->urlArrays['filter'];
        foreach(array_keys($data) as $key)
        {
            $lang = self::getTranslationKey(array_keys($data[$key]['Translation']));
            $data[$key] = array_merge($data[$key], $data[$key]['Translation'][$lang]);

            $url['key'] = $data[$key]['urlId'];
            $data[$key]['url'] =  $this->url($url, 'i18n', True);
            $data[$key]['link'] =  sprintf($linkPattern, $data[$key]['url'], $data[$key]['name'], $data[$key]['description']);
        }
        return $data;
    }
    public function diarySubmenu($action, array $filters, $filter = Null)
    {
        $translate = GC_Translate::get();
        $navi = Zend_Registry::getInstance()->Zend_Navigation;
        // get the (first) id == blog Element, this was set to find it here
        $diary = $navi->findOneBy('internalId', 'blog');
        if(Null === $diary)
        {
            return False;
        }
        $diary->setActive(True);//makes the diary in the main menu active
        $pattern = array(
            'type'       => 'mvc',
            'label'      => $translate->_('All'),
            'route'      => 'i18n',
            'module'     => 'default',
            'controller' => 'index',
            'action'     => 'diary',
            'title'      => '',
            //the filter param here isn't pretty,
            //but zend compares the keys and values of the params,
            //and i want this active only if there is no other filter selected
            //this will not add anything to the url
            //this will possibly break if someone changes the behavior
            //of comparison of routes etc.
            //Works in Zend ZendFramework-1.10.3
            'params'     => array('' => ''),//, 'page' => '1'),
            'active'     => (Null === $filter && $action === 'diary')//make "show all entries" active if $filter is Null
        );

        if(Null === $filter && $action === 'diary')//make the first entry inactive
        {
            $allLink = $diary->findOneBy('internalId', 'blogAllLink');
            $allLink->setActive(true);
        }

        //throw new GC_Debug_Exception($allLink->getLabel());
        //$diary->addPage(new Zend_Navigation_Page_Mvc($pattern));
        unset($pattern['active']);
        $pattern['params'] = array('key' => '');
        $notGuest = ('guest' === Zend_Registry::getInstance()->userRole) ? false : true;
        foreach($filters as $aFilter)
        {
            $pattern['label'] = ($notGuest && !$aFilter['published'])
                ? '('.$aFilter['name'].')'
                : $aFilter['name'];

            $pattern['title'] = $aFilter['description'];
            $pattern['params']['key'] = $aFilter['urlId'];
            $diary->addPage(new Zend_Navigation_Page_Mvc($pattern));
        }
        return True;
    }
    public function renderPager(Doctrine_Pager_Layout $that, $options = array())
    {
        $translate = GC_Translate::get();
        $that->setSelectedTemplate('<li class="active">{%page}</li>');

        $pager = $that->getPager();
        if(!$pager->haveToPaginate())
        {
            return;
        }
        $str = '';

        // First page
        $title = $translate->_('Go to the first page.');
        $that->setTemplate(sprintf('<li><a href="{%%url}" title="%1$s">{%%page}</a></li>', $title));
        $that->addMaskReplacement('page', '«', true);
        $options['page_number'] = $pager->getFirstPage();
        $str .= $that->processPage($options);

        // Previous page
        $title = $translate->_('Go to the previous page (number {%page_number}).');
        $that->setTemplate(sprintf('<li><a href="{%%url}" title="%1$s">{%%page}</a></li>', $title));
        $that->addMaskReplacement('page', '‹', true);
        $options['page_number'] = $pager->getPreviousPage();
        $str .= $that->processPage($options);

        // Pages listing
        $title = $translate->_('Go to page number {%page}.');
        $that->setTemplate(sprintf('<li><a href="{%%url}" title="%1$s">{%%page}</a></li>', $title));
        unset($options['page_number']);
        $that->removeMaskReplacement('page');
        $str .= $that->display($options, true);

        // Next page
        $title = $translate->_('Go to the next page (number {%page_number}).');
        $that->setTemplate(sprintf('<li><a href="{%%url}" title="%1$s">{%%page}</a></li>', $title));
        $that->addMaskReplacement('page', '›', true);
        $options['page_number'] = $pager->getNextPage();
        $str .= $that->processPage($options);

        // Last page
        $title = $translate->_('Go to the last page (number {%page_number}).');
        $that->setTemplate(sprintf('<li><a href="{%%url}" title="%1$s">{%%page}</a></li>', $title));
        $that->addMaskReplacement('page', '»', true);
        $options['page_number'] = $pager->getLastPage();
        $str .= $that->processPage($options);

        return $str;
    }
}
