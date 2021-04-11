var djConfig = {
    isDebug: false,
    graphicoreMaxContainers : 30,
    graphicoreContentWipeInTime : 1000,
    graphicoreContentWipeOutTime : 1000

};

window._gaq = window._gaq || [];

if(dojo.isIE <= 7)
{
    dojo.config.dojoBlankHtmlUrl = '/javascript/blank.html'
    //on my local machine
    //dojo.config.dojoBlankHtmlUrl = '/graphicore2/public/javascript/blank.html'
}
dojo.require("dijit.Tooltip");
dojo.require("dojo.fx");
dojo.require("dojo.hash");

/*
 *
 * used like this:
 * dojo.query('a[href^="/"]')
 * .not('.language-switch a, .header .menu-bar .filters.menu a')
 */
dojo.extend(dojo.NodeList, {
    not: function(query, root)
    {
        var substract = dojo.query(query ,root);
        return this.filter(function(node)
        {
            return substract.indexOf(node) == -1;
        });
    }
});
dojo.addOnLoad(function(){

///bootstrappping///////////////////////////////////////////////////////
    dojo.removeClass(dojo.body(), 'noscript');
///all that functions that invoke the javascript stuff//////////////////
    var allYourBase = {
        areBelongToUs: function(context)
        {
            for (var i = 0; i < this.transformers.length; i++)
            {
                this.transformers[i].perform(context);
            }
        },
        transformers:[],//they all have a perform method that takes one argument: context. if context is undefined the whole document will be used
        add: function(obj)
        {
            this.transformers.push(obj);
        }
    }
///every title attribute becomes a tooltip//////////////////////////////
    var titleReplacement =
    {
        perform: function(context)
        {
            dojo.query('*[title]', context).forEach(function(elem)
            {
                var title = elem.getAttribute('title');
                elem.removeAttribute('title');
                if(title === ""){return;}
                new dijit.Tooltip({
                    connectId: elem,
                    label: title,
                    position: ['above', 'below'],
                    showDelay: 100,
                    duration: 200
                });
            });
        }
    }
///prepare the content container///////////////////////////////////////////
/*
 * the "var page" will have an interface
 *
 * DOMElement content.getContainer(void)
 * void content.load(string html)
 *
 *
 * var page.status = 'closed'|'opened';
 */
    var content = undefined;

    (function(stage)
    {
        if(!stage){return;}
        var header= dojo.query('.header', stage)[0];
        if(!header){return;}

        content = {
            _container: undefined,
            getContainer: function()
            {
                //this._container = dojo.query('.content', stage)[0];
                this._container = dojo.create('div')
                dojo.addClass(this._container, 'content');
                dojo.style(this._container, 'display', 'none');
                dojo.place(this._container, header, 'after');
                return this._container;
            },
            removeContainer: function(count)
            {
                dojo.query('.content', stage)
                .not('.content.dying', stage)
                .splice(count)
                .forEach(function(node)
                {
                    dojo.addClass(node, 'dying');
                    var wipeOut = dojo.fx.wipeOut(
                    {
                        node: node,
                        duration: (djConfig.graphicoreContentWipeOutTime || 1000),
                        onEnd: function(){dojo.destroy(node);}
                    });
                    wipeOut.play();

                });
            },
            existedOnLoad: (dojo.query('.content', stage)[0] !== undefined),
            load: function(data, anchorName, type)
            {
                var klass = ({page:'black',error:'error',list: 'white list'})[type] || 'white';
                this.getContainer();
                if(anchorName)
                {
                    dojo.attr(this._container, 'id', anchorName);
                }
                //should be after this.getContainer() or count is nine, then it may be before
                this.removeContainer((djConfig.graphicoreMaxContainers || 10));
                dojo.addClass(this._container, klass);
                dojo.place(data, this._container, "only");
                //we use the id if we have one, because IE7 sucks on DOMNodes as context (seccond argument for dojo.query)
                allYourBase.areBelongToUs(this._container);
                var wipeIn = dojo.fx.wipeIn({
                    node: this._container,
                    duration: (djConfig.graphicoreContentWipeInTime || 1000)
                });

                wipeIn.play();
            }
        }
    })(dojo.query('.stage')[0]);
///make real emails from the mailto links///////////////////////////////
    var makeMail = {
        perform: function(context)
        {
            var self = this;
            dojo.query('.vcard a.email[href^=mailto]', context)
            .forEach(function(node)
            {
                self.vcardEmail(node);
            });
            dojo.query('span.no-spam', context)
            .forEach(function(node)
            {
                self.text(node);
            });
        },
        vcardEmail: function(node)
        {
            var href = this.transform(node.textContent);
            dojo.attr(node, 'href', 'mailto:' + encodeURI(href));
            node.textContent = href;
        },
        text: function(node)
        {
            if(!node.textContent){return;}
            var href = this.transform(node.textContent);
            var a = dojo.create('a',
            {
                href: 'mailto:' + encodeURI(href),
                textContent: href,
                'class': dojo.attr(node, 'class')
            });
            dojo.place(a, node, 'replace');
        },
        transform: function(str)
        {
            if(!dojo.isString(str)){return '';}
            return str.toLowerCase()
                .replace(/\s+/g, '')
                .replace(/[\[({]at[})\]]/, '@')
                .replace(/[\[({]dot[})\]]/,'.');
        }
    }
///make the links performing ajax///////////////////////////////////////
    var ajaxLinks = {
        _loading : {},
        _get : function(evt)
        {
            evt.preventDefault();
            var href = dojo.attr(evt.currentTarget, 'href');
            var url = href;
            //this prevents the browsers from beeing confused by the
            //content negotiation the server does via the http header
            //and it solves a caching problem of the internet explorer
            //the parameter has no meaning on the server side
            if(url.match(/\?/))
            {
                url += '&ajax=true';
            }
            else
            {
                url += '?ajax=true';
            }

            if(dojo.isIE == 7)
            {
                var horst = dojo.doc.location.protocol +'//' + dojo.doc.location.host;
                href = href.substring(horst.length)
            }
            var hashVal = 'graphicore_url_' + href.replace(/[^A-Za-z0-9._:\-]/g, ':');


            if(dojo.byId(hashVal))
            {
                // track href
                window._gaq.push(['_trackPageview', href]);
                
                /*go to the element with id = hash*/
                if(dojo.isIE)
                {
                    dojo.hash(hashVal);
                }
                else
                {
                    dojo.hash(encodeURIComponent(hashVal));
                }
                return;
            }
            else
            {
                dojo.hash('#');/*go to the top of the page*/
            }
            //prevent multiple loading due tue multiple clicks on the links before the page is loaded and the id exists
            if(ajaxLinks._loading[url])
            {
                return;
            }
            ajaxLinks._loading[url] = true;
            var xhrArgs =
            {
                url: url,
                handleAs: 'json',
                failOk: true,
                timeout: 15000,
                load: function(data, ioargs)
                {
                    delete(ajaxLinks._loading[url])
                    dojo.query('.header .menu a')
                    .map(function(node)
                    {
                        dojo.removeClass(node.parentNode, 'active');
                        return node;
                    })
                    //.removeClass('active')
                    .filter('[href="' + href + '"]')
                    .map(function(node)
                    {
                        dojo.addClass(node.parentNode, 'active');
                    });

                    dojo.doc.title = data['title'];

                    /*could be intersting again later*/
                    //page.open(data['html']);

                    content.load(data['html'], hashVal, data['type']);
                    if(data['languageSwitch'])
                    {
                        dojo.place( data['languageSwitch'] , dojo.query('.language-switch')[0], 'replace');
                        titleReplacement.perform(dojo.query('.language-switch')[0].parentNode);
                    }
                    
                    // track href
                    window._gaq.push(['_trackPageview', href]);
                },
                error: function(error, ioargs)
                {
                    delete(ajaxLinks._loading[url])
                    try
                    {
                        var contentType = ioargs.xhr.getResponseHeader("Content-Type");
                        if(contentType === 'application/json')
                        {
                            var data = dojo.fromJson(error.responseText);
                        }
                        else if( parseInt(ioargs.xhr.status) === 200 )
                        {
                            var data =
                            {
                                title: 'Error | Wrong Contenttype',
                                html: '<h1>Ooops: Error | Wrong Content-Type</h1><p>The page was not prepared for this Ajax style.</p>',
                                type: 'error'
                            }
                            if(djConfig.isDebug)
                            {
                                data.html += '<p> Content-Type was ' + contentType + '</p>';
                            }
                        }
                        if(data)
                        {
                            xhrArgs.load(data);
                            return;
                        }
                    }
                    catch(e)
                    {}
                    var data =
                    {
                        title: error.name + ((error.status) ? ' ' + error.status : ''),
                        html: '<h1>Ooops: '+ error.name + ((error.status) ? ' ' + error.status : '') + '</h1>',
                        type: 'error'
                    }
                    if(djConfig.isDebug)
                    {
                        data.html += '<p>' + error.message + '</p>';
                    }
                    xhrArgs.load(data);
                }
            }
            //Call the asynchronous xhrGet
            var deferred = dojo.xhrGet(xhrArgs);
        },
        perform : function(context)
        {
            dojo.query('a[href^="/"]', context)
            .not('.language-switch a', context)
            .not('a.permalink', context)
            .forEach(function(node)
            {
                //var href = dojo.attr(node, 'href');
                //was used to optically indicate witch links are affected
                //dojo.style(node, {color: 'red'});
                dojo.connect(node, 'onclick', ajaxLinks._get);
            });
        }
    }
///the footer animation/////////////////////////////////////////////////
    dojo.query('.footer').at(0).forEach(function(node)
    {
        var nodeStatus = 'closed';
        var match = dojo.style(node, 'bottom').match(/^([^a-zA-Z]+)([a-zA-Z]+)$/);
        var closeVal = match[1];
        var animation = undefined;

        var open = function(evt)
        {
            if(nodeStatus == 'opened'){return; true;}
            nodeStatus = 'opened';
            if(animation){animation.stop()};
            animation = dojo.animateProperty(
            {
                node: node,
                properties:
                {
                    bottom: {end: 0, units: match[2]}
                },
                delay: 50,
                duration: 200,
                onBegin: function(){dojo.removeClass(node, 'closed');dojo.addClass(node, 'opened');},
                onEnd: function(){delete animation;}
            });
            animation.play();
        };
        var close = function(evt)
        {
            if(nodeStatus == 'closed'){return true;}
            nodeStatus = 'closed';
            if(animation){animation.stop()};
            animation = dojo.animateProperty(
            {
                node: node,
                properties:
                {
                    bottom: {end: closeVal, units: match[2]}
                },
                delay: 50,
                duration: 500,
                onBegin: function(){dojo.removeClass(node, 'opened');dojo.addClass(node, 'closed');},
                onEnd: function(){delete animation;}
            });
            animation.play();
        };
        var inner = dojo.query('.inner', node)[0];
        dojo.connect(inner, 'onmouseenter', open);
        dojo.connect(inner, 'onmouseleave', close);
        dojo.connect(inner, 'onclick', function(evt)
        {
            if( nodeStatus == 'closed' )
            {
                open(evt);
            }
            else if( nodeStatus == 'opened' )
            {
                close(evt);
            }
        });
    });
////////////////////////////////////////////////////////////
    allYourBase.add(ajaxLinks);
    allYourBase.add(makeMail);
    allYourBase.add(titleReplacement);
    allYourBase.areBelongToUs();
    return;
});
