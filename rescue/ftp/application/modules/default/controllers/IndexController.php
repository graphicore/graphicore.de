<?php

class IndexController extends GC_Controller_Action
{
    public $renderScript = 'index';//false is the default script// the one Zend would use, named after the action
    protected $_cache = array();
    static public $urlArray = array(
        'controller' => 'index',
        'module'     => 'default'
    );
    public function init()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            $this->view->isJson = True;
        }
        $this->_helper->layout->setLayout('frontend');
    }
    public function postDispatch()
    {
        $this->view->uniqueHtmlID = $this->_getCached('renderer')
            ->uniqueHtmlID(self::$urlArray);
        if($this->view->isJson && array_key_exists('mainmenu', $_GET) && 'true' === $_GET['mainmenu'])
        {
            $this->view->ajaxMainMenu = true;
        }
        if(False === $this->renderScript){return;}
        $this->render($this->renderScript);
    }
    public function indexAction()
    {
        self::$urlArray['action'] =  'index';
        $this->pageAction();
    }

    public function servererrorAction()
    {
        throw new Zend_Controller_Action_Exception('Expected Exception', 500);
    }

    public function getLatestDiary()
    {
        $count = 5;
        $model = $this->_getCached('model');
        $renderer = $this->_getCached('renderer');
        $latest = $model->getDiary(False, $count);
        $latest = $renderer->diaryLatest($latest, array(), $this->_getCached('renderedFilters'));
        return $latest['content'];
    }

    public function pageAction()
    {
        $this->_setDiarySubmenu();
        $page = $this->getRequest()->getParam('key');
        $page = ($page == '') ? 'index' : $page;

        $model = $this->_getCached('model');
        $data = $model->findOnePageByUrlId($page, Doctrine::HYDRATE_ARRAY, true);
        if(False === $data)
        {
            throw new Zend_Controller_Action_Exception(sprintf('"%1$s" was not found.', htmlentities($page)), 404);
        }
        $dump = array();
        $renderer = $this->_getCached('renderer');
        if($page == 'index')
        {
            $renderer->addSpecialsCallback(
                array($this, 'getLatestDiary'),
                '#%latest%#iu'
            );
            $renderer = $this->_getCached('renderer');
        }
        $rendered = $renderer->staticPage($data);
        //for debugging
        //$dump[] = GC_Debug::dump($data, false);
        //$dump[] = $rendered['content'];
        $dump[] = $rendered['content'];
        $this->view->resourceType = 'content';#used to be 'page' and that is renderd with black background
        $this->view->titleData = array($rendered['title']);
        $this->view->dump = $dump;
        if(!array_key_exists('action', self::$urlArray))
        {
            //throw new GC_Debug_Exception();
            self::$urlArray['action'] =  'page';
            self::$urlArray['key'] =  $page;
        }
        $this->view->urlArray = self::$urlArray;
    }





    public function archiveAction()
    {
        $this->_setDiarySubmenu();
        $this->renderScript = 'diary';
        $article = $this->getRequest()->getParam('key');
        $model = $this->_getCached('model');
        $data = $model->findOneArticleByUrlId($article, Doctrine::HYDRATE_ARRAY, true);
        if(False === $data)
        {
            throw new Zend_Controller_Action_Exception(sprintf('"%1$s" was not found.', htmlentities($article)), 404);
        }
        $renderer = $this->_getCached('renderer');
        $renderedFilters = $this->_getCached('renderedFilters');

        $dump = array();
        $rendered = $renderer->diaryEntry($data, $renderedFilters);
        //for debugging
        //$dump[] = GC_Debug::dump($data, false);
        $dump[] = $rendered['content'];
        $this->view->resourceType = 'content';
        $translate = GC_Translate::get();
        $this->view->titleData = array(
            $translate->_('Diary entry'),
            $rendered['title']
        );
        $this->view->dump = $dump;
        if(! array_key_exists('action', self::$urlArray))
        {
            //throw new GC_Debug_Exception();
            self::$urlArray['action'] =  'archive';
            self::$urlArray['key'] =  $article;
        }
        $this->view->urlArray = self::$urlArray;
    }

    public function diaryAction()
    {

        //get all Filters and put em into the sidebar
        $this->renderScript = 'diary';
        self::$urlArray['action'] =  'diary';

        $model = $this->_getCached('model');
        $filters = $this->_getCached('filters');
        $sentFilter = $this->getRequest()->getParam('key');
        $sentFilter = ('' === $sentFilter) ? Null : $sentFilter;
        $this->_setDiarySubmenu('diary', $sentFilter);
        $filterData = $this->_extractFilter($filters, $sentFilter);
        $filter = $filterData['urlId'];
        if($sentFilter !== $filterData['hit'])
        {
            throw new Zend_Controller_Action_Exception(sprintf('The filter "%1$s" was not found.', htmlentities($sentFilter)), 404);
        }
        $renderer = $this->_getCached('renderer');
        $renderedFilters = $this->_getCached('renderedFilters');

        $this->view->titleData = array();
        $translate = GC_Translate::get();
        if($filter && $renderedFilters)
        {
            $title = $translate->_('Diary').', '.$translate->_('Filter').': '.$renderedFilters[$filterData['key']]['name'];
            self::$urlArray['key'] =  $filter;
        }
        else
        {
            $title = $translate->_('Diary');
        }
        $this->view->titleData[] = $title;

/*
        $pageNumber = $this->getRequest()->getParam('page');
        $pageNumber = is_numeric($pageNumber) ? (int)$pageNumber : 1;
        if(1 > $pageNumber)
        {
            throw new Zend_Controller_Action_Exception(sprintf('Page "%1$d" doesn\'t exist.', $pageNumber), 404);
        }

        $pageNumberPlaceholder = '{%page_number}';
        $pagerUrl = str_replace(
            urlencode($pageNumberPlaceholder),
            $pageNumberPlaceholder,
            $renderer->url(
                array_merge(
                    self::$urlArray,
                    array('filter' => $filter, 'page' => $pageNumberPlaceholder)
                )
            )
        );
        $config = Zend_Registry::get('config')->diary;
        $pagerLayout = new Doctrine_Pager_Layout(
            $model->getDiaryPager($filter, $pageNumber, $config->pager->results_per_page),
            $pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' =>  $config->pager->chunk)),
            $pagerUrl
        );
        // Retrieving Doctrine_Pager instance
        $pager = $pagerLayout->getPager();
        $diary = $pager->execute();
        if($pager->getLastPage() < $pageNumber)
        {
            throw new Zend_Controller_Action_Exception(sprintf('Page "%1$d" doesn\'t exist.', $pageNumber), 404);
        }
        $rendered = $renderer->diary($diary, array('title' => $title.', '.$translate->_('Page #').' '.$pageNumber), $renderedFilters, $pagerLayout);
*/


        $diary = $model->getDiary($filter);
        $rendered = $renderer->diary($diary, array('title' => $title), $renderedFilters);

        $dump = array();
        $dump[] = $rendered['content'];
        $this->view->resourceType = 'page';
        $this->view->dump = $dump;
        $this->view->urlArray = self::$urlArray;
    }
    /*
     * rss feed and atom feed
     */
    public function feedAction()
    {
        $this->renderScript = 'feed';
        $request = $this->getRequest();

        $type = $request->getParam('key');
        if(!in_array($type, array('rss', 'atom'),True))
        {
            throw new Zend_Controller_Action_Exception(sprintf('unkown Type requested'), 404);
        }

        $model = $this->_getCached('model');
        $filter = Null;

        $renderer = $this->_getCached('renderer');
        $config = Zend_Registry::getInstance()->config->diary;
        $this->view->diary = $renderer->feed($model->getDiary($filter, (int) $config->feed->max_entries));

        $this->view->feedType = $type;
        //if there is a filter, should that work
        //was not requestet, an enhancment for later propably
        self::$urlArray['action'] =  'diary';
        $this->view->htmlUrlArray = self::$urlArray;
        self::$urlArray['action'] =  'feed';
        $this->view->urlArray = self::$urlArray;
    }

    /*run where the menu will be used*/
    protected function _setDiarySubmenu($action = '', $active = Null)
    {
        if(!array_key_exists('setDiarySubmenu', $this->_cache))
        {
            $renderer = $this->_getCached('renderer');
            $renderedFilters = $this->_getCached('renderedFilters');
            /*this is not at all without side effects!*/
            if($renderedFilters)
            {
                $renderer->diarySubmenu($action, $renderedFilters, $active);
                $this->_cache['setDiarySubmenu'] = True;
            }
        }
    }
    protected function _cacheGetModel()
    {
        return new Default_Model_Controller();
    }
    protected function _cacheGetFilters()
    {
            $model = $this->_getCached('model');
            return $model->getAllFilters();
    }
    protected function _cacheGetRenderer()
    {
        return new Custom_Render();
    }
    protected function _cacheGetRenderedFilters()
    {
        $filters = $this->_getCached('filters');
        if(is_array($filters) && count($filters))
        {
            $renderer = $this->_getCached('renderer');
            return $renderer->filters($filters);
        }
        return Null;
    }
    protected function _getCached($key)
    {
        if(!array_key_exists($key, $this->_cache))
        {
            $func = '_cacheGet'.ucfirst($key);
            $this->_cache[$key] = $this->$func();
        }
        return $this->_cache[$key];
    }
    protected function _extractFilter(array $filters, $filter = Null)
    {
        $filterData = array(
            'urlId'=> Null,
            'id' => Null,
            'key' => Null,
            'hit' => Null
        );

        if(NULL === $filter){return $filterData;}
        foreach(array_keys($filters) as $key)
        {
            if($filters[$key]['urlId'] === $filter)
            {
                //try to get the filter in lang = GC_I18n::getLang() or the first lang
                $langs = array_keys($filters[$key]['Translation']);
                $lang = GC_I18n::getLang();
                if(!in_array($lang, $langs))
                {
                    $lang = $langs[0];
                }
                return array(
                    'urlId'=> $filters[$key]['urlId'],
                    'id' => $filters[$key]['id'],
                    'key' => $key,
                    'lang' => $lang,
                    'hit' => $filter
                );
            }
        }
        return $filterData;
    }
}