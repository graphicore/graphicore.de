/*jshint laxbreak:true, browser:true, indent:4, white:false */

if (!Function.prototype.bind) {
    Function.prototype.bind = function(oThis) {
        if (typeof this !== "function") {
            // closest thing possible to the ECMAScript 5 internal
            // IsCallable function
            throw new TypeError("Function.prototype.bind - what is "
                + "trying to be bound is not callable");
        }

        var aArgs = Array.prototype.slice.call(arguments, 1),
            fToBind = this,
            fNOP = function() {},
            fBound = function() {
                return fToBind.apply(this instanceof fNOP && oThis
                    ? this
                    : oThis,
                    aArgs.concat(Array.prototype.slice.call(arguments)));
            };

        fNOP.prototype = this.prototype;
        fBound.prototype = new fNOP();

        return fBound;
    };
}
/**
 * this was taken from dojox.fx.scroll and modified
 * its very different now, the arguments don't mean the same anymore
 */
define('graphicore/scrollTo',
    [
    "dojo/_base/lang", "dojo/_base/fx", "dojox/fx/_core"
    ], function (
    lang, baseFx, Line
) {
    var scroll = function(/* Object */args) {
        var target = { x: args.target.x, y: args.target.y };
        var _anim = (function(val) {
                args.win.scrollLeft = val[0];
                args.win.scrollTop = val[1];
            });
        var anim = new baseFx.Animation(lang.mixin({
            beforeBegin: function(){
                if(this.curve){ delete this.curve; }
                var current = {x: args.win.scrollLeft, y: args.win.scrollTop};
                anim.curve = new Line([current.x,current.y],[target.x, target.y]);
            },
            onAnimate: _anim
        }, args));
        return anim; // dojo.Animation
    };
    return scroll;
});

require([
    'dojo',
    'dojo/on',
    'dojox/fx',
    'graphicore/scrollTo',
    'dojo/fx/easing',
    'dijit/Tooltip'
], function(
    dojo,
    on,
    fx,
    scrollTo,
    easing,
    Tooltip
) {
    "use strict";
    /**
    * used like this:
    * dojo.query('a[href^="/"]')
    * .not('.language-switch a, .header .menu-bar .filters.menu a')
    */
    dojo.extend(dojo.NodeList, {
        not: function(query, root)
        {
            var substract = dojo.query(query, root);
            return this.filter(function (node)
            {
                return substract.indexOf(node) == -1;
            });
        }
    });

    window._gaq = window._gaq || [];

    if(dojo.isIE <= 7)
        dojo.config.dojoBlankHtmlUrl = '/js/blank.html';

    var djConfig = {
        isDebug: false,
        graphicoreMaxContainers : 30,
        graphicoreContentWipeInTime : 1000,
        graphicoreContentWipeOutTime : 1000,
        graphicoreSrollDuration: 500,
        graphicoreScrollEasing: easing.expoInOut
    },
    pageData = {
        titles: {},
        languageSwitches: {}
    },
    /// all that functions that invoke the javascript stuff ////////////
    allYourBase = {
        areBelongToUs: function(context)
        {
            for (var i = 0; i < this.transformers.length; i++)
            {
                this.transformers[i].perform(context);
            }
        },
        // they all have a perform method that takes one argument:
        // context. if context is undefined the whole document will be
        // used
        transformers: [],
        add: function(obj)
        {
            this.transformers.push(obj);
        }
    },
    /// every title attribute becomes a tooltip ////////////////////////
    titleReplacement = {
        perform: function(context) {
            dojo.query('*[title]', context).forEach(function(elem) {
                var title;
                if(!elem.hasAttribute('title'))
                    return;
                title = elem.getAttribute('title');
                if(title === "")
                    return;
                elem.removeAttribute('title');
                new Tooltip({
                    connectId: elem,
                    label: title,
                    position: ['above', 'below'],
                    showDelay: 100,
                    duration: 200
                });
            });
        }
    },
    ///make real emails from the mailto links///////////////////////////
    makeMail = {
        perform: function(context) {
            dojo.query('.vcard a.email[href^=mailto]', context)
            .forEach(function(node) {
                makeMail.vcardEmail(node);
            });

            dojo.query('span.no-spam', context)
            .forEach(function(node) {
                makeMail.text(node);
            });
        },
        vcardEmail: function(node) {
            var href = this.transform(node.textContent);
            dojo.attr(node, 'href', 'mailto:' + encodeURI(href));
            node.textContent = href;
        },
        text: function(node) {
            if(!node.textContent)
                return;
            var href = this.transform(node.textContent),
                a = dojo.create('a', {
                    href: 'mailto:' + encodeURI(href),
                    textContent: href,
                    'class': dojo.attr(node, 'class')
                });
            dojo.place(a, node, 'replace');
        },
        transform: function(str) {
            if(!dojo.isString(str)){ return ''; }
            return str.toLowerCase()
                .replace(/\s+/g, '')
                .replace(/[\[({]at[})\]]/, '@')
                .replace(/[\[({]dot[})\]]/,'.');
        }
    },
    /// let the up arrow scroll ////////////////////////////////////////
    scrollToTopHandler = function(evt) {
        evt.preventDefault();
        scrollTo({
            target: {x:0, y:0},
            win: document.body,
            duration: djConfig.graphicoreSrollDuration,
            easing: djConfig.graphicoreScrollEasing
        }).play();
    },
    /// manage ajax loading of content /////////////////////////////////
    contentLoader = {
        _loading : {},
        _setPage: function(href, hashVal, noHistoryPush) {
            var title = pageData.titles[hashVal],
                langSwitch = pageData.languageSwitches[hashVal];

            // track href
            window._gaq.push(['_trackPageview', href]);

            if(!noHistoryPush && history.pushState)
                history.pushState({}, title, href);

            content.updateMenu(href);
            content.setTitle(title);
            content.loadLanguageSwitch(langSwitch);
        },
        _getUrl: function(href) {
            // There are .json data files for each url
            if(href === '/') href = '/index';
            return [
                href , '.json'
            ].join('');
        },
        /**
         * scroll to a node that was already loaded
         */
        _scrollToNode: function(node) {
            scrollTo({
                // win: window,
                win: document.body,
                target: {
                    x: 0,
                    // scroll to node or scroll to top
                    y: node.getBoundingClientRect().top - 22 + document.body.scrollTop
                },
                duration: djConfig.graphicoreSrollDuration,
                easing: djConfig.graphicoreScrollEasing
            }).play();
        },
        get: function(href, noHistoryPush) {
            var url = this._getUrl(href),
                hashVal,
                node,
                horst = dojo.doc.location.protocol
                    +'//'
                    + dojo.doc.location.host;
            // ie7 does such
            if(href.indexOf(horst) !== -1)
                href = href.substring(horst.length);
            hashVal = 'graphicore_url_'
                + href.replace(/[^A-Za-z0-9._:\-]/g, ':');
            node = dojo.byId(hashVal);
            if(node) {
                this._scrollToNode(node);
                this._setPage(href, hashVal, noHistoryPush);
            }
            else {
                // window.scrollTo(0, 0);
                document.body.scrollTop = 0;
                this._loadPage(url, href, hashVal, noHistoryPush);
            }
        },
        _loadPage: function(url, href, hashVal, noHistoryPush) {
            // prevent multiple loading due to multiple clicks on the
            // links before the page is loaded and the id exists
            if(this._loading[url])
                return;
            this._loading[url] = true;
            var onLoad = this._onNodeLoad.bind(
                    this, url, href, hashVal,noHistoryPush),
                onError = this._onNodeLoadError.bind(this, url, onLoad);
            //Call the asynchronous xhrGet
            var deferred = dojo.xhrGet({
                url: url,
                handleAs: 'json',
                failOk: true,
                timeout: 15000,
                load: onLoad,
                error: onError
            });
        },
        _onNodeLoad: function(url, href, hashVal, noHistoryPush, data,
            ioargs
        ) {
            delete(this._loading[url]);
            // pageData caches stuff that gets lost wehn new content
            // is loaded
            pageData.titles[hashVal] = data.title;
            pageData.languageSwitches[hashVal] = data.languageSwitch;
            // this is like a cache, the html will be kept for a while
            content.loadContent(hashVal, data.html, data.type);
            this._setPage(href, hashVal, noHistoryPush);
        },
        _onNodeLoadError: function(url, onLoad, error, ioargs) {
            var contentType = ioargs.xhr.getResponseHeader("Content-Type"),
                data;
            delete(this._loading[url]);
            if(contentType === 'application/json')
                data = dojo.fromJson(error.responseText);
            else if( parseInt(ioargs.xhr.status, 10) === 200 ) {
                data = {
                    title: 'Error | Wrong Contenttype',
                    html: '<h1>Ooops: Error | Wrong Content-Type</h1><p>'
                        + 'The page was not prepared for this Ajax style.</p>',
                    type: 'error'
                };
                if(djConfig.isDebug)
                    data.html += '<p> Content-Type was ' + contentType
                        + '</p>';
            }
            else {
                data = {
                    title: error.name + ((error.status)
                        ? ' ' + error.status
                        : ''),
                    html: '<h1>Ooops: '
                        + error.name
                        + (error.status ? ' ' + error.status : '')
                        + '</h1>',
                    type: 'error'
                };
                if(djConfig.isDebug)
                data.html += '<p>' + error.message + '</p>';
            }
            onLoad(data);
        }
    },
    /// this makes the links using the contentLoader ///////////////////
    ajaxLinks = {
        perform : function(context) {
            dojo.query('a[href^="/"]', context)
                .not('.language-switch a', context)
                .not('a.permalink', context)
                .on('click', this._getHandler.bind(this));
        },
        _getHandler: function(evt) {
            var href = dojo.attr(evt.currentTarget, 'href');
            if(href === '/'
                    || href.indexOf('/en/page') === 0
                    || href.indexOf('/de/page') === 0
                    || href.indexOf('/en/archive') === 0
                    || href.indexOf('/de/archive') === 0
                    || href.indexOf('/en/diary') === 0
                    || href.indexOf('/de/diary') === 0
                    || href.indexOf('/en.') === 0
                    || href.indexOf('/de.') === 0
                    || href.indexOf('/index.') === 0) {
                evt.preventDefault();
                contentLoader.get(href);
            }
        }
    },
    /// content api, manipulates the dom ///////////////////////////////
    content = {
        header: undefined,
        stage: undefined,
        _getContentContainer: function() {
            var container = dojo.create('div');
            dojo.addClass(container, 'content');
            dojo.style(container, 'display', 'none');
            dojo.place(container, this.header, 'after');
            return container;
        },
        removeContainer: function(count) {
            dojo.query('.content', this.stage)
            .not('.content.dying', this.stage)
            .splice(count)
            .forEach(function(node)
            {
                dojo.addClass(node, 'dying');
                var wipeOut = fx.wipeOut(
                {
                    node: node,
                    duration: (
                        djConfig.graphicoreContentWipeOutTime || 1000
                    ),
                    onEnd: function(){dojo.destroy(node);}
                });
                wipeOut.play();
            });
        },
        loadLanguageSwitch: function(markup) {
            if(!markup)
                return;
            var langSwitch = dojo.query('.language-switch')[0];
            dojo.place(markup , langSwitch, 'replace');
            titleReplacement.perform(langSwitch.parentNode);
        },
        loadContent: function(hashVal, markup, type) {
            var klass = ({
                    page:'black',
                    error:'error',
                    list: 'white list'
                })[type] || 'white',
                container = this._getContentContainer();
            if(hashVal)
                dojo.attr(container, 'id', hashVal);

            this.removeContainer((djConfig.graphicoreMaxContainers || 10));
            dojo.addClass(container, klass);
            dojo.place(markup, container, 'only');
            // we use the id if we have one, because IE7 sucks on
            // DOMNodes as context (seccond argument for dojo.query)
            allYourBase.areBelongToUs(container);
            var wipeIn = fx.wipeIn({
                node: container,
                duration: (djConfig.graphicoreContentWipeInTime || 1000)
            });
            wipeIn.play();
        },
        updateMenu: function(href) {
            dojo.query('.menu a', this.header)
            .map(function(node) {
                dojo.removeClass(node.parentNode, 'active');
                return node;
            })
            //.removeClass('active')
            .filter('[href="' + href + '"]')
            .forEach(function(node) {
                dojo.addClass(node.parentNode, 'active');
            });
        },
        setTitle: function(title) {
            dojo.doc.title = title;
        }
    };

    allYourBase.add(ajaxLinks);
    allYourBase.add(makeMail);
    allYourBase.add(titleReplacement);

    dojo.addOnLoad(function() {
    /// bootstrappping /////////////////////////////////////////////////
        dojo.removeClass(dojo.body(), 'noscript');
    /// prepare the content apt ////////////////////////////////////////
        var stage, header, initialContent;
        stage = dojo.query('.stage')[0];
        if(!stage)
            return;
        header = dojo.query('.header', stage)[0];
        if(!header)
            return;

        content.stage = stage;
        content.header = header;

        initialContent = dojo.query('.content', stage)[0];
        if(initialContent) {
            pageData.titles[initialContent.id] = dojo.doc.title;
            pageData.languageSwitches[initialContent.id] =
                dojo.query('.language-switch')[0];
        }

    /// let the up arrow scroll ////////////////////////////////////////
        dojo.query('a[href="#"]').on('click', scrollToTopHandler);

    /// init ///////////////////////////////////////////////////////////
        allYourBase.areBelongToUs();
    /// fix stupid browser differences /////////////////////////////////
        // test if the page scroll with documentElement (should not)
        // there is some odd behavior with webkit, but this resets the fix
        // for firefox again
        document.documentElement.scrollTop = 1;
        if(document.documentElement.scrollTop === 1)
            dojo.style(document.documentElement, 'overflow', 'hidden');
    /// fix the back button ////////////////////////////////////////////
        // http://stackoverflow.com/questions/10742422/prevent-browser-scroll-on-html5-history-popstate
        // well, there is a scroll event, that we can't break :-(
        // so scrolling is now on the body element, which some people say
        // has a bad performance when scrolling.

        var haveSeenFirstPop = false,
            initialPathname = dojo.doc.location.pathname;
        on(window, 'popstate', function(evt) {
            // some browsers fire a popstate on load
            if(!haveSeenFirstPop) {
                haveSeenFirstPop = true;
                if(dojo.doc.location.pathname === initialPathname)
                    return;
            }
            contentLoader.get(dojo.doc.location.pathname, true);
        });
    });
});
