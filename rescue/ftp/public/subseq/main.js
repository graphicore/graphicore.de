(function(document, $, treeData, contentData) {
    
    function Leaf(parent, depth, data) {
        this.parent = parent || null
        this.depth = depth
        data = data || {}
        this.title = data.title || undefined
        this.teaser = data.teaser || undefined
        this.id = data.id || undefined
        this.content_id = data.content_id || undefined
        
        this.children = []
    }
    var _Lp = Leaf.prototype;
    
    _Lp.render = function(containerElement, renderTitle, renderTeaser) {
        renderTitle = renderTitle == undefined ? true : !!renderTitle;
        renderTeaser = renderTeaser == undefined ? true : !!renderTeaser;
        
        var container = document.createElement(containerElement)
          , title
          , teaser
          , item = document.createElement('div')
          ;
        
        item.className = 'item';
        
        if(renderTitle && this.title) {
            title = document.createElement('h' + (this.depth+1))
            title.innerHTML = this.title;
            item.appendChild(title)
        }
        
        if(renderTeaser && this.teaser) {
            teaser = document.createElement('p');
            teaser.innerHTML = this.teaser;
            item.appendChild(teaser)
        }
        
        if(item.children.length)
            container.appendChild(item)
        
        
        if(this.children.length)
            container.appendChild(this.renderChildren())
        
        $(container).on('mouseover', this._hover.bind(this, container))
        return container
    }
    
    _Lp.getContent = function() {
        /* Caution: uses 'global' contents */
        if(this.content_id)
            return contents[this.content_id]
    }
    
    _Lp._hover = function(container, e) {
        e.stopPropagation()
        var content = this.getContent() || {};
        $(document.body).triggerHandler('hovering.groupBranch', [content.groupBranchId])
    }
    
    _Lp.renderChildren = function() {
        var children = document.createElement('ol'),
            i=0;
        for(;i<this.children.length;i++)
            children.appendChild(this.children[i].render('li'))
        return children;
    }
    
    _Lp.walk = function(job) {
        var i=0;
        for(;i<this.children.length;i++) {
            job(this.children[i])
            this.children[i].walk(job)
        }
    }
    
    
    function Content(id, innerHTML, crossreferences) {
        this.id = id;
        this.dom = document.createElement('article');
        this.dom.innerHTML = innerHTML;
        
        this.crossreferences = crossreferences || []
        this.active = false
        this.groupBranchId = undefined
    }
    
    var _Cp = Content.prototype
    
    
    
    
    var contents = (function(data){
        var result = {}
          , id
          ;
        for(id in data)
            result[id] = new Content(id, data[id].html, data[id].xrefs)
        return result;
    })(contentData)
    
    
    function Structure(treeData){
        this._index = {}
        this.trees = this.buildTrees(treeData)
    }
    var _Sp = Structure.prototype
    
    _Sp.idExists = function(id) {
        return id in this._index;
    }
    _Sp._addToIndex = function(leaf) {
        if(this.idExists(leaf.id))
            throw new Error('ID already exists: ' + leaf.id)
        this._index[leaf.id] = leaf
    }
    
    _Sp.getById = function(id) {
        return this._index[id]
    }
    
    _Sp.buildTrees = function (treeData, parent, depth) {
        var result = []
          , depth = depth || 0
          , i=0
          , leaf
          , data
          ;
        for(;i<treeData.length;i++) {
            data = treeData[i]
            leaf = new Leaf(parent, depth, data)
            if(leaf.id)
                this._addToIndex(leaf)
            if(data.children)
                leaf.children = this.buildTrees(data.children, leaf, depth+1)
            result.push(leaf)
            
        }
        return result;
    }
    
    function getChildById(children, id) {
        var i=0;
        for(;i<children.length; i++) {
            if(children[i].id == id)
                return children[i];
        }
        return undefined;
    }
    
    var structure = new Structure(treeData);
      
    function markGroupTreeBranchID(structure, contents) {
        var groupTree = structure.getById('group')
          , i = 0
          // Mark the content of leaf with groupBranchId at groupBranchId
          // this method is bound as method of the content dict
          , marker = function(groupBranchId, leaf) {
                if(leaf.content_id && this[leaf.content_id]
                            // the first set groupBranchId wins
                            // this might be an Error as well, depending
                            // on how we model the data (may a content be
                            // multiple times in this tree)
                            && !this[leaf.content_id].groupBranchId)
                    this[leaf.content_id].groupBranchId = groupBranchId;
            }
          , job
          ;
        for(;i<groupTree.children.length;i++) {
            // bind groupBranchId to job
            job = marker.bind(contents, groupTree.children[i].id)
            job(groupTree.children[i])// mark the leafs content itself
            groupTree.children[i].walk(job)// mark the children
        }
    }
    
    function fixHeight(toc) {
        // height fix: needs to run on display of a toc
        var $toplevel = $('>ol>li', toc)
          , rowSize = 4
          , i=0
          , row = [];
        for(;i<$toplevel.length;i++) {
            row.push($toplevel.eq(i))
            if (row.length == rowSize) {
                //this was the last element of this row
                maxHeight = Math.max.apply(
                    null,
                    row.map(function($item) {
                        $item.css('height', '');
                        return $item.innerHeight()
                    })
                )
                row.forEach(function($item){
                    $item.css('height', maxHeight + 'px')
                })
                
                // clear the row
                row = [];
            }
        }
    }
    
    markGroupTreeBranchID(structure, contents)
    function main() {
        var i = 0
          , $nav = $('nav')
          , nav = $nav[0]
          , toc // table of contents
          , tocsFragment = document.createDocumentFragment()
          , tocs = $('.tocs', nav).get(0)
          , menu = $('.switch-toc menu', nav)
          , $button
          , tocIndex = {}
          , buttonIndex = {}
          ;
        
        for(;i<structure.trees.length;i++) {
            toc = structure.trees[i].render('div', true, false);
            toc.className = ['toc', 'toc-'+structure.trees[i].id].join(' ')
            tocIndex[structure.trees[i].id] = $(toc)
            tocsFragment.appendChild(toc)
        }
        
        //build the switch:
        function activateToc(id) {
            var k;
            
            for(k in buttonIndex) {
                if(k == id)
                    buttonIndex[k].addClass('active')
                else
                    buttonIndex[k].removeClass('active')
            }
            
            for(k in tocIndex) {
                if(k == id)
                    tocIndex[k].addClass('active')
                else
                    tocIndex[k].removeClass('active')
                fixHeight(tocIndex[k][0])
            }
        }
        for(i=0;i<structure.trees.length;i++) {
            $button = $(document.createElement('button'))
            $button[0].innerHTML = structure.trees[i].title
            buttonIndex[structure.trees[i].id] = $button
            menu[0].insertBefore($button[0], menu[0].lastElementChild)
            
            $button.on('click', activateToc.bind($button[0], structure.trees[i].id))
        }
        
        var $branchDisplay = $('header .branch')
          , siteBranchTitle = $branchDisplay.text()
          ;
        
        $(document.body).on('hovering.groupBranch', function(e, branchId) {
            var branchLeaf = this.getById(branchId) || {};
            $branchDisplay.text(branchLeaf.title || siteBranchTitle);
        }.bind(structure))
        
        
        tocs.appendChild(tocsFragment)
        // now the tocs are in the DOM
        buttonIndex[structure.trees[0].id].triggerHandler('click')
        
        $('nav>.switch-toc .call-to-action button').on('click',function(){
            menu.slideToggle(400)
        })
        
        var menuToggle = $('.toggle-menu');
        $('.toggle-menu').on('click', function() {
            
            $(this).toggleClass('active')
            
            $nav.slideToggle(400, function() {
                if($nav.is(':hidden'))
                    menu.stop().hide()
            })
        })
        
        $nav.hide();/* wasn't good via css, need to calc height …*/
        
        if($nav.is(':visible'))
            menuToggle.addClass('active')
        else
            menuToggle.removeClass('active')
        
        
    }
    $(main)
})(document, $
  , [
        {
            title: 'für Kunden'
          , id: 'clients' 
          , children: [
                {
                    title: 'IT'
                  , teaser: 'Nahtlose Beratungs&shy;kompetenz in Prozess und&nbsp;IT.'
                  , children: [
                        {
                          title: 'Consulting'
                        , teaser: 'Kompetenzen in allen logistischen und finanz&shy;wirt&shy;schaft&shy;lichen Modulen'
                        , content_id: 'consulting'
                        , children: [
                                {
                                    title: 'JAVA'
                                  , teaser: 'platt&shy;form&shy;unabhängig, verlässlich und etabliert JAVA als Fun&shy;dament für Ihre unter&shy;neh&shy;mens&shy;kri&shy;ti&shy;schen An&shy;wen&shy;dung&shy;en'
                                  , content_id: 'java_consulting'
                                }
                              , {
                                    title: 'SAP'
                                  , teaser: 'MM, SRM, PP, WM, QM, PM, SD, CRM, CS, FI, CO, HR'
                                  , content_id: 'sap_consulting'
                                }
                              , {
                                    title: 'Mobile Solutions'
                                  , teaser: 'In&shy;te&shy;grie&shy;ren Sie Kun&shy;den, Lie&shy;fer&shy;an&shy;ten, Part&shy;ner und Mit&shy;ar&shy;bei&shy;ter in Ihre Ge&shy;schäfts&shy;pro&shy;zesse, je&shy;der&shy;zeit und überall.'
                                  , content_id: 'mobile_consulting'
                                }
                            ]
                        }
                      , {
                          title: 'Produktentwicklung'
                        , teaser: 'mit »mehr Wert« für unsere Kunden!'
                        , content_id: 'dotnet'
                        , children: [
                            
                            ]
                        }
                    ]
                }
              , {
                    title: 'Human Resources'
                  , teaser: 'Wir bringen Kunden und Experten zusammen – schnell, flexibel und zuverlässig'
                  , children: [
                        {
                            title:'Recruiting'
                          , teaser: 'Eine erfolgreiche Partnerschaft entwickelt sich nicht von alleine.'
                          , content_id: 'recruiting_for_clients'
                          , children: []
                        }
                      , {
                            title:'Contracting'
                          , teaser: 'Wir kümmern uns zuverlässig um die besten Zulieferer für Sie.'
                          , content_id: 'contracting_for_clients'
                          , children: []
                        }
                  
                  
                    ]
                }
              , {
                    title: 'Beschaffung'
                  , children: [
                        {
                            title: 'Erstmusterprüfung'
                          , teaser: 'Vermeiden Sie Produktfehler durch einen Erst&shy;be&shy;mus&shy;ter&shy;ungs&shy;&shy;pro&shy;zess mit der SUBSEQ-Con&shy;sulting-Lösung'
                          , content_id: 'consulting_erstmuster'
                        }
                        , {
                            title: 'Lieferantenbeurteilung'
                          , teaser: 'Steigern Sie die Aussagekraft Ihrer Lieferantenbeurteilung mit der SUBSEQ-Consulting-Lösung'
                          , content_id: 'consulting_lieferantenbeurteilung'
                        }
                      , {
                            title: 'Katalogbeschaffung'
                          , children: [
                                {
                                    title: 'mit SRM'
                                  , teaser: 'Optimieren Sie die Einkaufsprozesse Ihrer Anwender signifikant und bieten Sie ihnen nutzerfreundliche Beschaffung per webbasierten Katalogstrukturen an!'
                                }
                              , {
                                    title: 'ohne SRM'
                                  , teaser: 'Nutzen Sie die Vorteile einer katalogbasierten Beschaffung auch OHNE den Einsatz von SRM!'
                                }
                            ]
                             
                        }
                    ]
                }
              , {
                    title: 'Produktion'
                  , teaser: 'Der Kernbereich Ihres Unternehemens ist uns das Wichtigste.'
                  , children: [
                        {
                            title: 'Qualitätsmanagement'
                          , teaser: 'Verbessern Sie Ihr Qua&shy;li&shy;täts&shy;man&shy;age&shy;ment und steigern Sie Ihre Pro&shy;dukt&shy;qua&shy;li&shy;tät.'
                          , children: [
                                {
                                    title: 'Erstmuster'
                                  , teaser: 'Fehlervermeidung und kontinuierlichen Verbesserung'
                                  , content_id: 'consulting_erstmuster'
                                }
                              , {
                                    title: 'Lieferantenbeurteilung'
                                  , teaser: 'Steigern Sie die Aussagekraft Ihrer Lieferantenbeurteilung mit der SUBSEQ-Consulting-Lösung'
                                  , content_id: 'consulting_lieferantenbeurteilung'
                                }
                          ]
                        }
                  
                      , {
                            title:'SAP'
                          , content_id: 'sap_consulting'
                        }
                      , {
                            title:'JAVA'
                          , content_id: 'java_consulting'
                        }
                      , {
                            title:'Mobile Solutions'
                          , content_id: 'mobile_consulting'
                        }
                    ]
                }
              , {
                    title: 'Logistik'
                  , children: [
                        {
                            title: 'Erstmusterprüfung'
                          , teaser: 'Vermeiden Sie Produktfehler durch einen Erst&shy;be&shy;mus&shy;ter&shy;ungs&shy;&shy;pro&shy;zess mit der SUBSEQ-Con&shy;sulting-Lösung'
                          , content_id: 'consulting_erstmuster'
                        }
                        , {
                            title: 'Lieferantenbeurteilung'
                          , teaser: 'Steigern Sie die Aussagekraft Ihrer Lieferantenbeurteilung mit der SUBSEQ-Consulting-Lösung'
                          , content_id: 'consulting_lieferantenbeurteilung'
                        }
                      , {
                            title: 'Katalogbeschaffung'
                          , children: [
                                {
                                    title: 'mit SRM'
                                  , teaser: 'Optimieren Sie die Einkaufsprozesse Ihrer Anwender signifikant und bieten Sie ihnen nutzerfreundliche Beschaffung per webbasierten Katalogstrukturen an!'
                                }
                              , {
                                    title: 'ohne SRM'
                                  , teaser: 'Nutzen Sie die Vorteile einer katalogbasierten Beschaffung auch OHNE den Einsatz von SRM!'
                                }
                            ]
                             
                        }
                    ]
                }
              , {
                    title: 'Kundenservice'
                  , teaser: 'Ihr Ziel: Zufriedene Kunden statt langer Gesichter!'
                  , children: [
                        {
                            title: 'CRM – Customer Relationship Management'
                          , teaser: 'Verbessern Sie Ihr Qua&shy;li&shy;täts&shy;man&shy;age&shy;ment und steigern Sie Ihre Pro&shy;dukt&shy;qua&shy;li&shy;tät.'
                          , children: [
                                {
                                    title: 'Analytisches CRM'
                                  , teaser: 'Fehlervermeidung und kontinuierlichen Verbesserung'
                                  , content_id: 'consulting_erstmuster'
                                }
                              , {
                                    title: 'Optimiertes Kam&shy;pag&shy;nen&shy;man&shy;age&shy;ment'
                                  , teaser: 'Steigern Sie die Aussagekraft Ihrer Lie&shy;fer&shy;an&shy;ten&shy;be&shy;ur&shy;tei&shy;lung mit der SUBSEQ-Consulting-Lösung'
                                  , content_id: 'consulting_lieferantenbeurteilung'
                                }
                          ]
                        }
                  
                      , {
                            title:'SAP'
                          , content_id: 'sap_consulting'
                        }
                      , {
                            title:'JAVA'
                          , content_id: 'java_consulting'
                        }
                      , {
                            title:'Mobile Solutions'
                          , content_id: 'mobile_consulting'
                        }
                    ]
                }
              , {
                    title: 'Marketing & Vertrieb'
                  , children: [
                        {
                          title: 'Consulting'
                        , teaser: 'Kompetenzen in allen logistischen und finanz&shy;wirt&shy;schaft&shy;lichen Modulen'
                        , content_id: 'consulting'
                        , children: [
                                {
                                    title: 'JAVA'
                                  , teaser: 'platt&shy;form&shy;unabhängig, verlässlich und etabliert JAVA als Fun&shy;dament für Ihre unter&shy;neh&shy;mens&shy;kri&shy;ti&shy;schen An&shy;wen&shy;dung&shy;en'
                                  , content_id: 'java_consulting'
                                }
                              , {
                                    title: 'SAP'
                                  , teaser: 'MM, SRM, PP, WM, QM, PM, SD, CRM, CS, FI, CO, HR'
                                  , content_id: 'sap_consulting'
                                }
                              , {
                                    title: 'Mobile Solutions'
                                  , teaser: 'In&shy;te&shy;grie&shy;ren Sie Kun&shy;den, Lie&shy;fer&shy;an&shy;ten, Part&shy;ner und Mit&shy;ar&shy;bei&shy;ter in Ihre Ge&shy;schäfts&shy;pro&shy;zesse, je&shy;der&shy;zeit und überall.'
                                  , content_id: 'mobile_consulting'
                                }
                            ]
                        }
                      , {
                          title: 'Produktentwicklung'
                        , teaser: 'mit »mehr Wert« für unsere Kunden!'
                        , content_id: 'dotnet'
                        , children: [
                            
                            ]
                        }
                    ]
                }
            ]
        }
      , {
            title: 'für Experten'
          , id: 'experts' 
          , children: [
                {
                    title: 'Festanstellung'
                  , content_id: 'recruiting'
                  , children: [
                        {
                            title:'für Experten'
                          , teaser: 'Als Expert-Partner bestimmen Sie maßgeblich die Qualität unserer Dienstleistung und stehen für unseren Geschäftserfolg. '
                          , content_id: 'recruiting_experts'
                          , children: []
                        }
                      , {
                            title:'für Unternehmen'
                          , teaser: 'Eine erfolgreiche Partnerschaft entwickelt sich nicht von alleine. Wir investieren daher viel Zeit in den Auf- und Ausbau unseres Partnernetzwerkes.'
                          , content_id: 'recruiting_for_clients'
                          , children: []
                        }
                      , {
                            title:'Stellenangebote'
                          , teaser: 'Folgende Stellen haben wir permanent zu vergeben:'
                          , content_id: 'recruiting_for_clients'
                          , children: [
                                {title: 'Account-Manager'}
                              , {title: 'Senior / Management-Consultant SAP-QM/MM'}
                              , {title: 'Junior Consultant SAP-QM/MM'}
                              , {title: 'Studentische Aushilfe für MS Access'}
                          
                          ]
                        }
                    ]
                }
              , {
                    title: 'als Zulieferer'
                  , content_id: 'contracting'
                  , children: [
                        {
                            title:'für Unternehmen'
                          , teaser: 'Eine erfolgreiche Partnerschaft entwickelt sich nicht von alleine. Wir investieren daher viel Zeit in den Auf- und Ausbau unseres Partnernetzwerkes.'
                          , content_id: 'contracting_for_clients'
                          , children: []
                        }
                      ,                         {
                        title:'für Experten'
                      , teaser: 'Als Expert-Partner bestimmen Sie maßgeblich die Qualität unserer Dienstleistung und stehen für unseren Geschäftserfolg. '
                          , content_id: 'contracting_experts'
                          , children: []
                      }
                    
                    ]
                }
              , {
                    title: 'CONSULTING'
                  , content_id: 'consulting'
                  , children: [
                       {
                            title: 'Qualitätsmanagement'
                          , teaser: 'Verbessern Sie Ihr Qua&shy;li&shy;täts&shy;man&shy;age&shy;ment und steigern Sie Ihre Pro&shy;dukt&shy;qua&shy;li&shy;tät.'
                          , children: [
                                {
                                    title: 'Erstmuster'
                                  , teaser: 'Fehlervermeidung und kontinuierlichen Verbesserung'
                                  , content_id: 'consulting_erstmuster'
                                }
                              , {
                                    title: 'Lieferantenbeurteilung'
                                  , teaser: 'Steigern Sie die Aussagekraft Ihrer Lieferantenbeurteilung mit der SUBSEQ-Consulting-Lösung'
                                  , content_id: 'consulting_lieferantenbeurteilung'
                                }
                                
                                
                          ]
                            
                        }
                      , {
                            title: 'SRM'
                          , teaser: 'Supplier Relationship Management'
                          , children:[
                                {
                                    title: 'Lieferantenbeurteilung'
                                }
                              , {
                                    title: 'Katalogbeschaffung mit SRM'
                                }
                              , {
                                    title: 'Katalogbeschaffung ohne SRM'
                                }
                            ]
                        }
                      , {
                            title: 'CRM'
                          , teaser: 'Customer Relationship Managements'
                          , children: [
                                {title: 'Analytisches CRM'}
                              , {title: 'Optimiertes Kampagnenmanagement'}
                              , {title: 'Vertriebssteuerung'}
                          ]
                        }
                      , {
                            title:'SAP'
                          , content_id: 'sap_consulting'
                        }
                      , {
                            title:'JAVA'
                          , content_id: 'java_consulting'
                        }
                      , {
                            title:'Mobile Solutions'
                          , content_id: 'mobile_consulting'
                        }
                    ]
                }
              , {
                    title: 'Training'
                  , content_id: 'dotnet'
                  , children: [
                        {
                            title: 'Softwareentwicklung'
                            , teaser: 'mit »mehr Wert« für unsere Kunden!'
                            , content_id: 'dotnet'
                            , children: [
                            
                            ]
                        }
                      , {
                          title: 'Consulting'
                        , teaser: 'Kompetenzen in allen logistischen und finanz&shy;wirt&shy;schaft&shy;lichen Modulen'
                        , content_id: 'consulting'
                        , children: [
                                {
                                    title: 'JAVA'
                                  , teaser: 'platt&shy;form&shy;unabhängig, verlässlich und etabliert JAVA als Fun&shy;dament für Ihre unter&shy;neh&shy;mens&shy;kri&shy;ti&shy;schen An&shy;wen&shy;dung&shy;en'
                                  , content_id: 'java_consulting'
                                }
                              , {
                                    title: 'SAP'
                                  , teaser: 'MM, SRM, PP, WM, QM, PM, SD, CRM, CS, FI, CO, HR'
                                  , content_id: 'sap_consulting'
                                }
                              , {
                                    title: 'Mobile Solutions'
                                  , teaser: 'In&shy;te&shy;grie&shy;ren Sie Kun&shy;den, Lie&shy;fer&shy;an&shy;ten, Part&shy;ner und Mit&shy;ar&shy;bei&shy;ter in Ihre Ge&shy;schäfts&shy;pro&shy;zesse, je&shy;der&shy;zeit und überall.'
                                  , content_id: 'mobile_consulting'
                                }
                            ]
                        }
                    ]
                }
            ]
        }
      , {
            title: 'SUBSEQ Struktur'
          , id: 'group' 
          , children: [
                {
                    title: 'CONSULTING'
                  , id: 'consulting'
                  , content_id: 'consulting'
                  , children: [
                        {
                            title: 'Qualitätsmanagement'
                          , teaser: 'Verbessern Sie Ihr Qua&shy;li&shy;täts&shy;man&shy;age&shy;ment und steigern Sie Ihre Pro&shy;dukt&shy;qua&shy;li&shy;tät.'
                          , children: [
                                {
                                    title: 'Erstmuster'
                                  , teaser: 'Fehlervermeidung und kontinuierlichen Verbesserung'
                                  , content_id: 'consulting_erstmuster'
                                }
                              , {
                                    title: 'Lieferantenbeurteilung'
                                  , teaser: 'Steigern Sie die Aussagekraft Ihrer Lieferantenbeurteilung mit der SUBSEQ-Consulting-Lösung'
                                  , content_id: 'consulting_lieferantenbeurteilung'
                                }
                                
                                
                          ]
                            
                        }
                      , {
                            title: 'SRM'
                          , teaser: 'Supplier Relationship Management'
                          , children:[
                                {
                                    title: 'Lieferantenbeurteilung'
                                }
                              , {
                                    title: 'Katalogbeschaffung mit SRM'
                                }
                              , {
                                    title: 'Katalogbeschaffung ohne SRM'
                                }
                            ]
                        }
                      , {
                            title: 'CRM'
                          , teaser: 'Customer Relationship Managements'
                          , children: [
                                {title: 'Analytisches CRM'}
                              , {title: 'Optimiertes Kampagnenmanagement'}
                              , {title: 'Vertriebssteuerung'}
                          ]
                        }
                      , {
                            title:'SAP'
                          , content_id: 'sap_consulting'
                        }
                      , {
                            title:'JAVA'
                          , content_id: 'java_consulting'
                        }
                      , {
                            title:'Mobile Solutions'
                          , content_id: 'mobile_consulting'
                        }
                    ]
                }
              , {
                    title: '.NET'
                  , id: 'dotnet'
                  , content_id: 'dotnet'
                  , children: [
                        {
                            title: 'Softwareentwicklung'
                            , teaser: 'mit »mehr Wert« für unsere Kunden!'
                            , content_id: 'dotnet'
                            , children: [
                            
                            ]
                        }
                      , {
                          title: 'Consulting'
                        , teaser: 'Kompetenzen in allen logistischen und finanz&shy;wirt&shy;schaft&shy;lichen Modulen'
                        , content_id: 'consulting'
                        , children: [
                                {
                                    title: 'JAVA'
                                  , teaser: 'platt&shy;form&shy;unabhängig, verlässlich und etabliert JAVA als Fun&shy;dament für Ihre unter&shy;neh&shy;mens&shy;kri&shy;ti&shy;schen An&shy;wen&shy;dung&shy;en'
                                  , content_id: 'java_consulting'
                                }
                              , {
                                    title: 'SAP'
                                  , teaser: 'MM, SRM, PP, WM, QM, PM, SD, CRM, CS, FI, CO, HR'
                                  , content_id: 'sap_consulting'
                                }
                              , {
                                    title: 'Mobile Solutions'
                                  , teaser: 'In&shy;te&shy;grie&shy;ren Sie Kun&shy;den, Lie&shy;fer&shy;an&shy;ten, Part&shy;ner und Mit&shy;ar&shy;bei&shy;ter in Ihre Ge&shy;schäfts&shy;pro&shy;zesse, je&shy;der&shy;zeit und überall.'
                                  , content_id: 'mobile_consulting'
                                }
                            ]
                        }
                    ]
                }
              , {
                    title: 'RECRUITING'
                  , id: 'recruiting'
                  , content_id: 'recruiting'
                  , children: [
                        {
                            title:'für Experten'
                          , teaser: 'Als Expert-Partner bestimmen Sie maßgeblich die Qualität unserer Dienstleistung und stehen für unseren Geschäftserfolg. '
                          , content_id: 'recruiting_experts'
                          , children: []
                        }
                      , {
                            title:'für Unternehmen'
                          , teaser: 'Eine erfolgreiche Partnerschaft entwickelt sich nicht von alleine. Wir investieren daher viel Zeit in den Auf- und Ausbau unseres Partnernetzwerkes.'
                          , content_id: 'recruiting_for_clients'
                          , children: []
                        }
                      , {
                            title:'Stellenangebote'
                          , teaser: 'Folgende Stellen haben wir permanent zu vergeben:'
                          , content_id: 'recruiting_for_clients'
                          , children: [
                                {title: 'Account-Manager'}
                              , {title: 'Senior / Management-Consultant SAP-QM/MM'}
                              , {title: 'Junior Consultant SAP-QM/MM'}
                              , {title: 'Studentische Aushilfe für MS Access'}
                          
                          ]
                        }
                    ]
                }
              , {
                    title: 'CONTRACTING'
                  , id: 'contracting'
                  , content_id: 'contracting'
                  , children: [
                        {
                            title:'für Unternehmen'
                          , teaser: 'Eine erfolgreiche Partnerschaft entwickelt sich nicht von alleine. Wir investieren daher viel Zeit in den Auf- und Ausbau unseres Partnernetzwerkes.'
                          , content_id: 'contracting_for_clients'
                          , children: []
                        }
                      ,                         {
                        title:'für Experten'
                      , teaser: 'Als Expert-Partner bestimmen Sie maßgeblich die Qualität unserer Dienstleistung und stehen für unseren Geschäftserfolg. '
                          , content_id: 'contracting_experts'
                          , children: []
                      }
                    
                    ]
                }
            ]
        }
      , {
            title: 'Themenregister'
          , id: 'topics' 
          , children: [
                {
                    title: 'Produktion'
                  , teaser: 'Der Kernbereich Ihres Unternehemens ist uns das Wichtigste.'
                  , children: [
                        {
                            title: 'Qualitätsmanagement'
                          , teaser: 'Verbessern Sie Ihr Qua&shy;li&shy;täts&shy;man&shy;age&shy;ment und steigern Sie Ihre Pro&shy;dukt&shy;qua&shy;li&shy;tät.'
                          , children: [
                                {
                                    title: 'Erstmuster'
                                  , teaser: 'Fehlervermeidung und kontinuierlichen Verbesserung'
                                  , content_id: 'consulting_erstmuster'
                                }
                              , {
                                    title: 'Lieferantenbeurteilung'
                                  , teaser: 'Steigern Sie die Aussagekraft Ihrer Lieferantenbeurteilung mit der SUBSEQ-Consulting-Lösung'
                                  , content_id: 'consulting_lieferantenbeurteilung'
                                }
                          ]
                        }
                  
                      , {
                            title:'SAP'
                          , content_id: 'sap_consulting'
                        }
                      , {
                            title:'JAVA'
                          , content_id: 'java_consulting'
                        }
                      , {
                            title:'Mobile Solutions'
                          , content_id: 'mobile_consulting'
                        }
                    ]
                }
              , {
                    title: 'Logistik'
                  , children: [
                        {
                            title: 'Erstmusterprüfung'
                          , teaser: 'Vermeiden Sie Produktfehler durch einen Erst&shy;be&shy;mus&shy;ter&shy;ungs&shy;&shy;pro&shy;zess mit der SUBSEQ-Con&shy;sulting-Lösung'
                          , content_id: 'consulting_erstmuster'
                        }
                        , {
                            title: 'Lieferantenbeurteilung'
                          , teaser: 'Steigern Sie die Aussagekraft Ihrer Lieferantenbeurteilung mit der SUBSEQ-Consulting-Lösung'
                          , content_id: 'consulting_lieferantenbeurteilung'
                        }
                      , {
                            title: 'Katalogbeschaffung'
                          , children: [
                                {
                                    title: 'mit SRM'
                                  , teaser: 'Optimieren Sie die Einkaufsprozesse Ihrer Anwender signifikant und bieten Sie ihnen nutzerfreundliche Beschaffung per webbasierten Katalogstrukturen an!'
                                }
                              , {
                                    title: 'ohne SRM'
                                  , teaser: 'Nutzen Sie die Vorteile einer katalogbasierten Beschaffung auch OHNE den Einsatz von SRM!'
                                }
                            ]
                             
                        }
                    ]
                }
              , {
                    title: 'Kundenservice'
                  , teaser: 'Ihr Ziel: Zufriedene Kunden statt langer Gesichter!'
                  , children: [
                        {
                            title: 'CRM – Customer Relationship Management'
                          , teaser: 'Verbessern Sie Ihr Qua&shy;li&shy;täts&shy;man&shy;age&shy;ment und steigern Sie Ihre Pro&shy;dukt&shy;qua&shy;li&shy;tät.'
                          , children: [
                                {
                                    title: 'Analytisches CRM'
                                  , teaser: 'Fehlervermeidung und kontinuierlichen Verbesserung'
                                  , content_id: 'consulting_erstmuster'
                                }
                              , {
                                    title: 'Optimiertes Kam&shy;pag&shy;nen&shy;man&shy;age&shy;ment'
                                  , teaser: 'Steigern Sie die Aussagekraft Ihrer Lie&shy;fer&shy;an&shy;ten&shy;be&shy;ur&shy;tei&shy;lung mit der SUBSEQ-Consulting-Lösung'
                                  , content_id: 'consulting_lieferantenbeurteilung'
                                }
                          ]
                        }
                  
                      , {
                            title:'SAP'
                          , content_id: 'sap_consulting'
                        }
                      , {
                            title:'JAVA'
                          , content_id: 'java_consulting'
                        }
                      , {
                            title:'Mobile Solutions'
                          , content_id: 'mobile_consulting'
                        }
                    ]
                }
              , {
                    title: 'Marketing & Vertrieb'
                  , children: [
                        {
                          title: 'Consulting'
                        , teaser: 'Kompetenzen in allen logistischen und finanz&shy;wirt&shy;schaft&shy;lichen Modulen'
                        , content_id: 'consulting'
                        , children: [
                                {
                                    title: 'JAVA'
                                  , teaser: 'platt&shy;form&shy;unabhängig, verlässlich und etabliert JAVA als Fun&shy;dament für Ihre unter&shy;neh&shy;mens&shy;kri&shy;ti&shy;schen An&shy;wen&shy;dung&shy;en'
                                  , content_id: 'java_consulting'
                                }
                              , {
                                    title: 'SAP'
                                  , teaser: 'MM, SRM, PP, WM, QM, PM, SD, CRM, CS, FI, CO, HR'
                                  , content_id: 'sap_consulting'
                                }
                              , {
                                    title: 'Mobile Solutions'
                                  , teaser: 'In&shy;te&shy;grie&shy;ren Sie Kun&shy;den, Lie&shy;fer&shy;an&shy;ten, Part&shy;ner und Mit&shy;ar&shy;bei&shy;ter in Ihre Ge&shy;schäfts&shy;pro&shy;zesse, je&shy;der&shy;zeit und überall.'
                                  , content_id: 'mobile_consulting'
                                }
                            ]
                        }
                      , {
                          title: 'Produktentwicklung'
                        , teaser: 'mit »mehr Wert« für unsere Kunden!'
                        , content_id: 'dotnet'
                        , children: [
                            
                            ]
                        }
                    ]
                }
              , {
                    title: 'IT'
                  , teaser: 'Nahtlose Beratungs&shy;kompetenz in Prozess und&nbsp;IT.'
                  , children: [
                        {
                          title: 'Consulting'
                        , teaser: 'Kompetenzen in allen logistischen und finanz&shy;wirt&shy;schaft&shy;lichen Modulen'
                        , content_id: 'consulting'
                        , children: [
                                {
                                    title: 'JAVA'
                                  , teaser: 'platt&shy;form&shy;unabhängig, verlässlich und etabliert JAVA als Fun&shy;dament für Ihre unter&shy;neh&shy;mens&shy;kri&shy;ti&shy;schen An&shy;wen&shy;dung&shy;en'
                                  , content_id: 'java_consulting'
                                }
                              , {
                                    title: 'SAP'
                                  , teaser: 'MM, SRM, PP, WM, QM, PM, SD, CRM, CS, FI, CO, HR'
                                  , content_id: 'sap_consulting'
                                }
                              , {
                                    title: 'Mobile Solutions'
                                  , teaser: 'In&shy;te&shy;grie&shy;ren Sie Kun&shy;den, Lie&shy;fer&shy;an&shy;ten, Part&shy;ner und Mit&shy;ar&shy;bei&shy;ter in Ihre Ge&shy;schäfts&shy;pro&shy;zesse, je&shy;der&shy;zeit und überall.'
                                  , content_id: 'mobile_consulting'
                                }
                            ]
                        }
                      , {
                          title: 'Produktentwicklung'
                        , teaser: 'mit »mehr Wert« für unsere Kunden!'
                        , content_id: 'dotnet'
                        , children: [
                            
                            ]
                        }
                    ]
                }
              , {
                    title: 'Human Resources'
                  , teaser: 'Wir bringen Kunden und Experten zusammen – schnell, flexibel und zuverlässig'
                  , children: [
                        {
                            title:'Recruiting'
                          , teaser: 'Eine erfolgreiche Partnerschaft entwickelt sich nicht von alleine.'
                          , content_id: 'recruiting_for_clients'
                          , children: []
                        }
                      , {
                            title:'Contracting'
                          , teaser: 'Wir kümmern uns zuverlässig um die besten Zulieferer für Sie.'
                          , content_id: 'contracting_for_clients'
                          , children: []
                        }
                  
                  
                    ]
                }
              , {
                    title: 'Beschaffung'
                  , children: [
                        {
                            title: 'Erstmusterprüfung'
                          , teaser: 'Vermeiden Sie Produktfehler durch einen Erst&shy;be&shy;mus&shy;ter&shy;ungs&shy;&shy;pro&shy;zess mit der SUBSEQ-Con&shy;sulting-Lösung'
                          , content_id: 'consulting_erstmuster'
                        }
                        , {
                            title: 'Lieferantenbeurteilung'
                          , teaser: 'Steigern Sie die Aussagekraft Ihrer Lieferantenbeurteilung mit der SUBSEQ-Consulting-Lösung'
                          , content_id: 'consulting_lieferantenbeurteilung'
                        }
                      , {
                            title: 'Katalogbeschaffung'
                          , children: [
                                {
                                    title: 'mit SRM'
                                  , teaser: 'Optimieren Sie die Einkaufsprozesse Ihrer Anwender signifikant und bieten Sie ihnen nutzerfreundliche Beschaffung per webbasierten Katalogstrukturen an!'
                                }
                              , {
                                    title: 'ohne SRM'
                                  , teaser: 'Nutzen Sie die Vorteile einer katalogbasierten Beschaffung auch OHNE den Einsatz von SRM!'
                                }
                            ]
                             
                        }
                    ]
                }
            ]
        }
    ]
  , {
        recruiting: {
            html: '<h1>Wettbewerbsfaktor Humankapital</h1><p>Die Komplexität im IT- und Prozessumfeld steigt kontinuierlich und damit auch die Anforderungen an Experten. Dieser Herausforderung haben wir uns für Sie angenommen.</p><p>Wir bringen Unternehmen mit den richtigen Spezialisten zusammen &#8211; schnell, flexibel und zuverlässig.</p><p>Dabei wird nicht von uns nur reines IT- oder Modulwissen im Auswahlprozess berücksichtigt, sondern ebenso das geforderte Prozess- und Branchenwissen geprüft. Personalverantwortliche bekommen daher nur ausgewählte und qualitativ hochwertige Beraterprofile vorgelegt.</p><h1>Matching-Prozess</h1><p>Innovative Ansätze bei Bewertung und Umsetzung der kundenspezifischen Personalan-<br />forderungen stehen bei SUBSEQ im Vordergrund. Um den optimalen Deckungsgrad zwischen Kundenanforderungen und Beratungs- profilen zu erreichen, wird das von SUBSEQ entwickelte SAP-Expert-Prinzip® erfolgreich eingesetzt.</p><h1>Projekterfahrung</h1><p>Die SUBSEQ GmbH wird von Partnern geführt, die seit vielen Jahren in der internationalen Beratung aktiv sind und damit die spezifischen Herausforderungen in Ihrem Umfeld kennen. Dieses Wissen und der profunde ERP/SAP®-Hintergrund ermöglicht einen effizienten Vermittlungsprozess zwischen Klienten und Beratern.</p>'
        }
      , recruiting_experts: {
            html: '<h1>Unsere Leistung für SAP Experten</h1><p>Mit unserem SAP-Expert-Prinzip® sind wir in der Lage schnell und kompetent auf die Anfragen unserer Kunden zu reagieren. Als Expert-Partner bestimmen Sie maßgeblich die Qualität unserer Dienstleistung und stehen für unseren Geschäftserfolg. Deshalb ist die SAP-Expert-Partnerschaft® für uns mehr nur als nur ein Wort.</p><p>Unser Partnernetzwerk umschließt sowohl Unternehmensberatungen, freie Mitarbeiter und permanent beschäftigte Consultants. Gemeinsam stehen wir für den Geschäftserfolg beim Kunden. Wir leben langfristige Partnerschaften, die sich durch erfolgreiche Projektarbeiten, Vertrauen und Respekt auszeichnen.</p><p>Das SAP-Expert-Prinzip® von SUBSEQ setzt auf den Spezialisten mit Erfahrung. Sie arbeiten nur an Projekten, die Ihren Qualifikationen entsprechen. Gleichzeitig minimieren sich Ihr Akquisitionsaufwand und Ihre Leerlaufzeiten.</p><p>Profitieren Sie aus der Zufriedenheit unserer Kunden!</p><p>Bitte registrieren Sie sich bei Interesse <a href="http://subseq.com/recruiting-2/fur-sap-experten/registrierung/">hier,</a>  oder kontaktieren Sie uns per <a style="color: #252a6c;" href="mailto:info@subseq.com"><span style="font-style: normal;">E-Mail.</span></a> Wir werden uns schnellstmöglich bei Ihnen melden.</p>'
        }
      , recruiting_for_clients:{
            html: '<h1>Recruiting: Unsere Leistung für Unternehmen</h1><p>Eine erfolgreiche Partnerschaft entwickelt sich nicht von alleine. Wir investieren daher viel Zeit in den Auf- und Ausbau unseres Partnernetzwerkes. So können wir unseren Kunden einen optimalen Service bei der Durchführung ihrer Projekte anbieten.</p><p>Mit dem SAP-Expert-Prinzip® von SUBSEQ setzen Sie auf ein dynamisches Team aus Spezialisten mit Erfahrung. Neben den neuesten IT-Technologien und Branchenlösungen aus dem Hause SAP®, stehen auch Beratungskompetenz und Lösungsmethodik des SAP-Expert-Partners® im Vordergrund.</p><p>Vertrauen Sie einem Team, welches Ihre fachlichen Anforderungen in Ihrer bestehenden IT Landschaft umsetzt.</p><p>Das spart Geld und Ihre Nerven.</p><p>Nutzen Sie mit dem SAP-Expert-Prinzip® die Synergien, die sich aus der Kompetenz von Strategie, Prozessen und SAP® aus einer Hand ergeben.</p><p><a href="http://subseq.com/kontakt/">Zum Kontaktformular</a><br />oder<br />laden Sie <a href="http://subseq.com/recruiting-2/fur-unternehmen/profilanfrage/">hier Ihre aktuelle Projektbeschreibung</a> hoch.</p>'
        }
      , contracting:{
            html: '<h1>Wer hat die besten Supplier</h1><p>Die Komplexität im IT- und Prozessumfeld steigt kontinuierlich und damit auch die Anforderungen an Experten. Dieser Herausforderung haben wir uns für Sie angenommen.</p><p>Wir bringen Unternehmen mit den richtigen Spezialisten zusammen &#8211; schnell, flexibel und zuverlässig.</p><p>Dabei wird nicht von uns nur reines IT- oder Modulwissen im Auswahlprozess berücksichtigt, sondern ebenso das geforderte Prozess- und Branchenwissen geprüft. Personalverantwortliche bekommen daher nur ausgewählte und qualitativ hochwertige Beraterprofile vorgelegt.</p><h1>Matching-Prozess</h1><p>Innovative Ansätze bei Bewertung und Umsetzung der kundenspezifischen Personalan-<br />forderungen stehen bei SUBSEQ im Vordergrund. Um den optimalen Deckungsgrad zwischen Kundenanforderungen und Beratungs- profilen zu erreichen, wird das von SUBSEQ entwickelte SAP-Expert-Prinzip® erfolgreich eingesetzt.</p><h1>Projekterfahrung</h1><p>Die SUBSEQ GmbH wird von Partnern geführt, die seit vielen Jahren in der internationalen Beratung aktiv sind und damit die spezifischen Herausforderungen in Ihrem Umfeld kennen. Dieses Wissen und der profunde ERP/SAP®-Hintergrund ermöglicht einen effizienten Vermittlungsprozess zwischen Klienten und Beratern.</p>'
        }  
      , contracting_for_clients:{
            html: '<h1>Contracting: Was wir für Ihr Unternehmen tun können</h1><p>Eine erfolgreiche Partnerschaft entwickelt sich nicht von allein, deshalb investieren wir viel Zeit in den Auf- und Ausbau unseres Partnernetzwerks. So können wir Ihnen den optimalen Service bieten, wenn es um die adäquate Besetzung vakanter Stellen in Ihrem Unternehmen geht.</p><p>Über das erfolgreiche Expert-Prinzip® von<strong> SUBSEQ &#8211; Consulting </strong>können wir Ihnen passende Experten für Ihr Projekt zur Verfügung stellen. Unsere Experten sind mit den neuesten IT-Technologien vertraut, verfügen über eine hohe Beratungskompetenz und haben eine Vielzahl von Projekteinsätzen.</p><p>Vertrauen Sie unserem Team, das Ihre fachlichen Stellenanforderungen in Ihrem spezifischen Umfeld kundenorientiert umsetzt. Nutzen Sie mit dem Expert-Prinzip® die Vorteile, die sich aus der Synergie von Strategie, Prozess und SAP® aus einer Hand ergeben. Das spart Geld und schont Ihre Nerven.</p><p><a title="Kontakt" href="http://meq-consulting.de/kontakt/">Zum Kontaktformular</a></p><p>oder</p><p>laden Sie <a title="Stellenbeschreibung" href="http://meq-consulting.de/recruiting-2/fur-unternehmen/stellenbeschreibung/">hier Ihre aktuelle Stellenbeschreibung</a> hoch.</p><p>Unseren Geschäftsbereich Subseq, über den wir freiberufliche Ressourcen für Projekteinsätze koordinieren und organisieren, finden Sie <a title="Recruiting" href="http://www.subseq.com/" target="_blank">hier</a>.</p>'
        }
      , contracting_experts:{}
      , consulting: {
            html: '<h1>Beratung  mit  „mehr Wert“ für unsere Kunden!</h1><div class="col2Left"><p>Effiziente Beratung auf hohem Niveau erfordert spezifisches Branchen Know-how. <strong>SUBSEQ Consulting</strong> verfügt über fundiertes Wissen in verschiedenen Geschäftsfeldern. Bei der Optimierung von Geschäftsprozessen spielt die Informationstechnologie eine zunehmend wichtige Rolle. Daher bringen wir in unsere Projekte die notwendige, erstklassige IT-Expertise ein.</p></div><div class="col2Right"><a class="wpGallery" href="http://subseq.com/wp-content/uploads/2009/10/consulting_de.jpg"><img style="border: 0px initial initial;" title="Wertschöpfungskette" src="http://subseq.com/wp-content/uploads/2009/10/consulting_de.jpg" alt="" width="256" height="156" /></a></div><div class="fullcol"><p style="text-align: left;"></div><div class="col2Left"><p style="text-align: left;"><strong>Consulting</strong></p><ul><li style="margin: 0.0px 0.0px 7.2px 0.0px; font: 12.0px Arial;">Branchenübergreifende Logistikberatung im Supply Chain Management (SCM) und Product Lifecycle Management (PLM)</li><li style="margin: 0.0px 0.0px 7.2px 0.0px; font: 12.0px Arial;"><span style="letter-spacing: 0.0px;">Nahtlose Beratungskompetenz in Prozess und IT entlang der Wertschöpfungskette durch eingespielte und seniore Beratungsteams </span></li><li style="margin: 0.0px 0.0px 0.0px 0.0px; font: 12.0px Arial;"><span style="letter-spacing: 0.0px;">SAP-Kompetenzen in allen logistischen und finanzwirtschaftlichen Modulen wie MM, SRM, PP, WM, QM, PM, SD, CRM, CS, FI, CO, HR  inkl. der Abbildung des entsprechenden Berichtswesens im Business Warehouse (BW) </span></li></ul></div><div class="col2Right"><p style="text-align: left;"><strong>Produktentwicklung </strong></p><ul><li style="margin: 0.0px 0.0px 7.2px 0.0px; font: 12.0px Arial;"><span style="letter-spacing: 0.0px;">Praxiserprobte Consulting-Lösungen für fehlende SAP-Standardfunktionalitäten </span></li><li style="margin: 0.0px 0.0px 7.2px 0.0px; font: 12.0px Arial;"><span style="letter-spacing: 0.0px;">Fachgerechte Bewertung und Vorabselektion einzelner Anpassungen hinsichtlich kunden- und systemspezifischer Anforderungen </span></li><li style="margin: 0.0px 0.0px 0.0px 0.0px; font: 12.0px Arial;"><span style="letter-spacing: 0.0px;">Abdeckung aller Anforderungen im Implementierungsprozess vom Projektleiter bis zum SAP-Entwickler </span></li></ul></div><div class="fullcol"><p><strong>SUBSEQ</strong> zeichnet sich insbesondere durch profunde Beratungs- und Projektkompetenz in logistischen Fragestellungen entlang der Wertschöpfungskette aus. Dabei können alle gängigen SAP-Logistik- und Finanzmodule aus einer Hand bedient werden.</p></div>'
        }
      , mobile_consulting: {}
      , sap_consulting: {}
      , java_consulting: {}
      
      , consulting_erstmuster: {}
      , consulting_lieferantenbeurteilung: {}
      , dotnet: {
            html: '<h1>Produktentwicklung  mit  „mehr Wert“ für unsere Kunden!</h1><div class="col2Left"><p>Effiziente Beratung auf hohem Niveau erfordert spezifisches Branchen Know-how. <strong>SUBSEQ Consulting</strong> verfügt über fundiertes Wissen in verschiedenen Geschäftsfeldern. Bei der Optimierung von Geschäftsprozessen spielt die Informationstechnologie eine zunehmend wichtige Rolle. Daher bringen wir in unsere Projekte die notwendige, erstklassige IT-Expertise ein.</p></div><div class="col2Right"><a class="wpGallery" href="http://subseq.com/wp-content/uploads/2009/10/consulting_de.jpg"><img style="border: 0px initial initial;" title="Wertschöpfungskette" src="http://subseq.com/wp-content/uploads/2009/10/consulting_de.jpg" alt="" width="256" height="156" /></a></div><div class="fullcol"><p style="text-align: left;"></div><div class="col2Left"><p style="text-align: left;"><strong>Consulting</strong></p><ul><li style="margin: 0.0px 0.0px 7.2px 0.0px; font: 12.0px Arial;">Branchenübergreifende Logistikberatung im Supply Chain Management (SCM) und Product Lifecycle Management (PLM)</li><li style="margin: 0.0px 0.0px 7.2px 0.0px; font: 12.0px Arial;"><span style="letter-spacing: 0.0px;">Nahtlose Beratungskompetenz in Prozess und IT entlang der Wertschöpfungskette durch eingespielte und seniore Beratungsteams </span></li><li style="margin: 0.0px 0.0px 0.0px 0.0px; font: 12.0px Arial;"><span style="letter-spacing: 0.0px;">SAP-Kompetenzen in allen logistischen und finanzwirtschaftlichen Modulen wie MM, SRM, PP, WM, QM, PM, SD, CRM, CS, FI, CO, HR  inkl. der Abbildung des entsprechenden Berichtswesens im Business Warehouse (BW) </span></li></ul></div><div class="col2Right"><p style="text-align: left;"><strong>Produktentwicklung </strong></p><ul><li style="margin: 0.0px 0.0px 7.2px 0.0px; font: 12.0px Arial;"><span style="letter-spacing: 0.0px;">Praxiserprobte Consulting-Lösungen für fehlende SAP-Standardfunktionalitäten </span></li><li style="margin: 0.0px 0.0px 7.2px 0.0px; font: 12.0px Arial;"><span style="letter-spacing: 0.0px;">Fachgerechte Bewertung und Vorabselektion einzelner Anpassungen hinsichtlich kunden- und systemspezifischer Anforderungen </span></li><li style="margin: 0.0px 0.0px 0.0px 0.0px; font: 12.0px Arial;"><span style="letter-spacing: 0.0px;">Abdeckung aller Anforderungen im Implementierungsprozess vom Projektleiter bis zum SAP-Entwickler </span></li></ul></div><div class="fullcol"><p><strong>SUBSEQ</strong> zeichnet sich insbesondere durch profunde Beratungs- und Projektkompetenz in logistischen Fragestellungen entlang der Wertschöpfungskette aus. Dabei können alle gängigen SAP-Logistik- und Finanzmodule aus einer Hand bedient werden.</p></div>'
        }
    }
);
