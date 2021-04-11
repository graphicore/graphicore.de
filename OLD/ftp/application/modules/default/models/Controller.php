<?php

class Default_Model_Controller
{

    public function findOneStaticByUrlAlias($urlAlias, $hydration = Doctrine::HYDRATE_RECORD)
    {
        $q = Doctrine_Query::create()
        ->setHydrationMode($hydration)
        ->from('DcStatic ds')
        ->leftJoin('ds.Translation t')
        ->where('ds.urlAlias = ?', $urlAlias)
        ;
        if('guest' === Zend_Registry::getInstance()->userRole)
        {
            $q->andWhere('t.published = ?', true);
        }

        //fetchOne() returns Array or Doctrine_Collection, depending on hydration mode. False if no result.
        $dcModel = $q->fetchOne();
        return $dcModel;
    }
    public function findOnePageByUrlId($urlId, $hydration = Doctrine::HYDRATE_RECORD)
    {
        $q = Doctrine_Query::create()
        ->setHydrationMode($hydration)
        ->from('DcPage ds')
        ->leftJoin('ds.Translation t')
        ->where('ds.urlId = ?', $urlId)
        ;
        if('guest' === Zend_Registry::getInstance()->userRole)
        {
            $q->andWhere('t.published = ?', true);
        }

        //fetchOne() returns Array or Doctrine_Collection, depending on hydration mode. False if no result.
        $dcModel = $q->fetchOne();
        return $dcModel;
    }
    public function findOneArticleByUrlId ($urlId, $hydration = Doctrine::HYDRATE_RECORD)
    {
        $q = Doctrine_Query::create()
        ->setHydrationMode($hydration)
        ->select('d.*, t.*, f.id')
        ->from('DcDiary d')
        ->leftJoin('d.Translation t')
        ->leftJoin('d.Filters f')
        ->where('d.urlId = ?', $urlId);
        if('guest' === Zend_Registry::getInstance()->userRole)
        {
            $q->andWhere('t.published = ?', true);
            //$q->andWhere('f.published = ?', true);
        }
        return $q->fetchOne();
    }
    public function getAllFilters($hydration = Doctrine::HYDRATE_ARRAY)
    {
        //throw new GC_Debug_Exception('cache the result of this function, probably statically');
        $q = Doctrine_Query::create()
        ->setHydrationMode($hydration)
        ->from('DcFilter f INDEXBY f.id')
        ->leftJoin('f.Translation t')
        ->orderBy('f.weight');
        //if the user is a guest
        if('guest' === Zend_Registry::getInstance()->userRole)
        {
            $q->where('f.published = ?', true);
        };
        //fetchOne() returns Array or Doctrine_Collection, depending on hydration mode. False if no result.
        return $q->execute();
    }
    public function getDiaryQuery($filter = False, $hydration = Doctrine::HYDRATE_ARRAY)
    {

        $q = Doctrine_Query::create()
        ->setHydrationMode($hydration)
        ->from('DcDiary d')
        ->select('d.*, t.*, f.*, ft.*')
        ->orderBy('d.timestamp DESC, f.weight')
        ->leftJoin('d.Translation t')
        ->leftJoin('d.Filters f')
        ->leftJoin('f.Translation ft');
        if('guest' === Zend_Registry::getInstance()->userRole)
        {
            $q->where('t.published = ?', true);
            //ther shall be no! entry with t.published === false
            //throw new GC_Debug_Exception($q->execute());
            //$q->andWhere('f.published = ?', true);
        }
        if($filter)
        {
            $filter = is_array($filter) ? $filter : array($filter);
            $qq = Doctrine_Query::create()
            ->setHydrationMode($hydration)
            ->select('d2.id')
            ->from('DcDiary d2')
            ->innerJoin('d2.Filters f2')
            ->whereIn('f2.urlId', $filter);
            $q->andWhere('d.id IN ('.$qq->getDql().')', $filter);
        }
        //subquery instead of the andwherein join!
        //see a filtered diaries articles Filter: list, only the filterd filter will be there now
        //throw new Exception($qq->getSqlQuery());
        //throw new GC_Debug_Exception($qq->execute());
        //throw new GC_Debug_Exception($q->execute());
        return $q;
    }
    public function getDiary($filter = False, $limit = False, $hydration = Doctrine::HYDRATE_ARRAY)
    {
        $q = $this->getDiaryQuery($filter, $hydration);
        if($limit && is_int($limit))
        {
            $q->limit($limit);
        }
        return $q->execute();
    }

    public function getDiaryPager($filter = False, $currentPage = Null, $resultsPerPage = Null, $hydration = Doctrine::HYDRATE_ARRAY)
    {
        $q = $this->getDiaryQuery($filter, $hydration);
        $currentPage = (is_numeric($currentPage) && $currentPage > 1)
            ? (int) $currentPage : 1;// Current page of request
        $resultsPerPage = (is_numeric($resultsPerPage) && $resultsPerPage > 1)
            ? (int) $resultsPerPage : 25;// (Optional) Number of results per page. Default is 25

        $pager = new Doctrine_Pager($q, $currentPage, $resultsPerPage);
        //$items = $pager->execute();
        return $pager;
    }




    protected function OLD_getAllTypes()
    {

/*
    the template for the subquery to get only types that have a translation in the default language

    throw new Exception(
        Doctrine_Query::create()
        ->select('x.id')
        ->from('GcType x')
        ->leftJoin('x.Translation xt')
        ->where('xt.lang = ?', GC_I18n::getDefaultLang())
        ->andWhere('x.id = ?', 1)
        ->getDql()
    );
*/
        //gets all published types that have published stories
        $result = Doctrine_Query::create()
        //gets all published types that have published stories
        //with all translations of the types and all published translations of the  stories
        //->select('g.*, t.*, x.*, z.*')

        //get the teasing part of the stories in all published languages
        //->select('t.*, tt.*, s.id, s.gc_type_id, s.intern, s.updated_at, st.title, st.teaser, st.lang')

        //shows no story at all
        //but every type has at least one story(with a published translation)
        ->select(
        't.*,
         tt.*')
        ->from('GcTopic t')
        ->leftJoin('t.Translation tt')
        ->leftJoin('t.GcStory s')
        ->leftJoin('s.Translation st')
        ->where('t.published = ?', 1)
        ->andWhere('t.hidden < ?', 1)
        ->andWhere('st.published = ?', 1)
        ->andWhere('EXISTS (SELECT x.id FROM GcTopic x LEFT JOIN x.Translation xt WHERE xt.lang = ? AND x.id = t.id)', GC_I18n::getDefaultLang())
        ->orderBy('t.weight ASC, t.intern ASC')
        ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
        ->execute();
        return $result;
    }
    protected function OLD_getType($intern = '', $limit = false)
    {
        //a type with an intern name of $type
        //if it has stories with published translations
        //the teasing parts of the translations
        $intern = (string) $intern;
        $query = Doctrine_Query::create()
        ->select(
        't.*,
         tt.*,
         s.id, s.gc_topic_id, s.intern, s.weight, s.created_at,
         st.title, st.teaser, st.lang, st.created_at')
        ->from('GcTopic t')
        ->leftJoin('t.Translation tt')
        ->leftJoin('t.GcStory s')
        ->leftJoin('s.Translation st')
        ->where('t.intern = ?', $intern)
        ->andWhere('t.published = ?', 1)
        ->andWhere('t.hidden < ?', 1)
        ->andWhere('st.published = ?', 1)
        ->andWhere('EXISTS (SELECT x.id FROM GcTopic x LEFT JOIN x.Translation xt WHERE xt.lang = ? AND x.id = t.id)', GC_I18n::getDefaultLang())
        ->orderBy('s.weight ASC, s.created_at DESC, st.created_at ASC')
        ;
        $result = $query->setHydrationMode(Doctrine::HYDRATE_ARRAY)->execute();

        //stupid thing, I have to find a way to limit the GcStories
        //this is not cool
        $limit = (int) $limit;
        if($limit > 1 && $result && count($result))
        {
            $copy = array();
            while($limit > 0)
            {
                $story = array_shift($result[0]['GcStory']);
                if(!$story){break;}
                $copy[] = $story;
                $limit--;
            }
            $result[0]['GcStory'] = $copy;
        }
        return $result;
    }

    protected function OLD_getStory($intern = '')
    {
        $intern = (string) $intern;
        $result = Doctrine_Query::create()
        ->select('s.*, st.*, t.intern, t.hidden')
        ->from('GcStory s')
        ->leftJoin('s.Translation st')
        ->leftJoin('s.GcTopic t')
        ->where('s.intern = ?', $intern)//intern name of the story
        ->andWhere('t.published = ?', 1)//the type must be published
        ->andWhere('st.published = ?', 1)//a Translation must be published
        ->andWhere('EXISTS (SELECT x.id FROM GcTopic x LEFT JOIN x.Translation xt WHERE xt.lang = ? AND x.id = t.id)', GC_I18n::getDefaultLang())
        ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
        ->execute();
        return $result;
    }

}

