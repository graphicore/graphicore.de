<?php
/*
 * changelog
 * 2009/11/23
 *      created as a copy of Formation_Controller_Action_simple to implement the handling of translations
 */
abstract class Formation_Controller_Action_I18n extends GC_Controller_Action
{
    protected $_editLangKey = 'editlang';
    protected $_modelClass = '';
    protected $_model;
    protected $_form = array(
        'create' => Null,
        'update' => Null,
        'delete' => Null,
    );
    protected $_urlArray = array(
        'module' => Null,
        'controller' => Null,
        'action' => Null,
    );
    protected $_typeName = Null;
    protected $_indexListItem = array(
        'fields' => array('id'),
        'format' => '%1$s'
    );
    /*
     * see protected function _cleanPost for more
     */
    protected $_cleanPostKeys = array(
        '__ALL__' => array()
    );
    public function init()
    {
        $this->view->editLangKey = $this->_editLangKey;
        $this->view->urlArray = $this->_urlArray;
        $this->view->typeName = $this->_typeName;
        $this->view->message = '';
    }
    protected function _getModel($values = NULL)
    {
        if (NULL === $this->_model || NULL !== $values) {
            $this->_model = new $this->_modelClass();
        }
        return $this->_model;
    }
    public function indexAction()
    {
        $dump = array();
        $this->view->verb = 'choose';
        $this->view->headlineFormat = '<h1>%1$s index</h1>';
        //show a list of all $things...
        $model = $this->_getModel();
        $list = $model->findAll();
        $this->view->list = $list;

        $this->view->indexListItemFields = $this->_indexListItem['fields'];
        $this->view->indexListItemFormat = $this->_indexListItem['format'];

        $dump['findAll'] = $list;
        $this->view->dump = $dump;
        // Renders indexi18n.phtml
        $this->render('indexi18n', null, true);
    }
    //we use these methods to set up stuff like select fields
    protected function _setUpFormCreate($form)
    {}
    protected function _setUpFormUpdate($form)
    {}
    protected function _setFrontUrlArr($action, $dcModel)
    {}
    public function createAction()
    {
        $dump = array();
        $this->view->verb = 'create';
        $this->view->headlineFormat = '<h1>%2$s a new %1$s</h1>';
        $editingLanguage = $this->_getEditingLanguage();
        $this->view->editLang = $editingLanguage;

        $request = $this->getRequest();
        $post = ($request->isPost()) ? $request->getPost() : array();
        $dump['ignoredKeys'] = array_keys($post);
        $post = $this->_cleanPost($post, __FUNCTION__);
        $dump['ignoredKeys'] = array_diff($dump['ignoredKeys'], array_keys($post));
        $form = new $this->_form['create']();
        $this->_setUpFormCreate($form);
        $form = $form->build()->form;
        if($form->wasSent($post))
        {
            $model = $this->_getModel();
            $dcmodel = $model->create($post, $editingLanguage, $form->namespace, $form->namespaceI18n);//$post gets changed in here
            if (False !== $dcmodel)
            {
                //success
                $this->view->message = 'model says it was created';
                //go to the update page
                $this->_redirectToUpdate($dcmodel->id, $editingLanguage);
            }
            else
            {
                //something went wrong
                $this->view->message = 'Something went wrong.';
                //set the messages to the form
                $dump['messagesFormat'] = $model->getMessages();
                $form->setValues($post);
                $form->setMessages($model->getMessages());
            }
        }
        $this->view->dump = $dump;
        $this->view->form = $form;

        // Renders createi18n.phtml
        $this->render('createi18n', null, true);
    }
    public function updateAction()
    {
        $dump = array();
        $this->view->verb = 'update';
        $editingLanguage = $this->_getEditingLanguage();
        $this->view->editLang = $editingLanguage;

        $request = $this->getRequest();
        $id = $request->getParam('id');
        $model = $this->_getModel();
        if( !is_numeric($id) || !($dcModel = $model->find($id)) )
        {
            //the id is wrong
            throw new Zend_Controller_Action_Exception(
                sprintf(
                    'id "%1$s" does not exist in %2$s',
                    htmlspecialchars($id),
                    get_class($model)
                ),
                 404
            );
        }
        $this->view->id = $dcModel->id;
        $id = $dcModel->id;
        $post = ($request->isPost()) ? $request->getPost() : array();
        $dump['ignoredKeys'] = array_keys($post);
        $post = $this->_cleanPost($post, __FUNCTION__);
        $dump['ignoredKeys'] = array_diff($dump['ignoredKeys'], array_keys($post));
        $dump['post'] = $post;

        $form = new $this->_form['update']();
        $this->_setUpFormUpdate($form);
        $form = $form->build()->form;
        if($form->wasSent($post))
        {
            if(!$model->update($dcModel, $post, $editingLanguage, $form->namespace, $form->namespaceI18n))
            {
                $this->view->message = 'Something went wrong.';
                $dump['messagesFormat'] = $model->getMessages();
                $form->setMessages($model->getMessages());
            }
            else
            {
                $this->view->message = 'Updated successfully.';
            }
        }
        $form->setValues($model->map4Form($dcModel, $editingLanguage ,$form->namespace, $form->namespaceI18n));

        $dump['map4Form'] = $model->map4Form($dcModel, $editingLanguage ,$form->namespace, $form->namespaceI18n);
        $this->view->dump = $dump;
        $this->view->form = $form;

        $this->_setFrontUrlArr('update', $dcModel);

        // Renders updatei18n.phtml
        $this->render('updatei18n', null, true);
    }
    //public function readAction()
    //{}
    public function deleteAction()
    {
        $dump = array();
        $this->view->verb = 'delete';

        $request = $this->getRequest();
        $id = $request->getParam('id');
        $model = $this->_getModel();
        if( !is_numeric($id) || !($dcModel = $model->find($id)) )
        {
            //the id is wrong
            throw new Zend_Controller_Action_Exception(
                sprintf(
                    'id "%1$s" does not exist in %2$s',
                    htmlspecialchars($id),
                    get_class($model)
                ),
                 404
            );
        }
        $this->view->id = $dcModel->id;
        $id = $dcModel->id;
        $editingLanguage = $this->getRequest()->getParam($this->_editLangKey);
        if(empty($editingLanguage))
        {
            //we will delete the whole $dcModel
            //we want to display options for language choosing
            $editingLanguage = Null;
            $this->view->deletingLanguages = False;
            $this->view->editingLanguage = $editingLanguage;
        }
        //else if(GC_I18n::isLocale($editingLanguage) && isset($dcModel->Translation[$editingLanguage]))
        //deleteable even if these are not allowed locales
        //could be if a locale used to be allowed but isn't anymore
        else if(isset($dcModel->Translation[$editingLanguage]))
        {
            //we will delete the $dcModel->Translation
            //we want to display the link to delete the whole model
            $this->view->deletingLanguages = True;
            $this->view->editingLanguage = $editingLanguage;
        }
        else
        {
            throw new Zend_Controller_Action_Exception(
                sprintf(
                    'there is no language "%1$s"',
                    htmlspecialchars($editingLanguage)
                ),
                 404
            );
        }
        $post = ($request->isPost()) ? $request->getPost() : array();
        $dump['ignoredKeys'] = array_keys($post);
        $post = $this->_cleanPost($post, __FUNCTION__);
        $dump['ignoredKeys'] = array_diff($dump['ignoredKeys'], array_keys($post));
        $dump['post'] = $post;
        $form = new $this->_form['delete']();
        //radiobox do you really want to delete this "$model"
        //some text and links to other places would be nice
        //giving some hints and links like
            //how to unpublish this type (at update)
            //where to delete only translations //FIXME: implement that!
            //warn about how much data will get lost
        $form = $form->build()->form;
        if($form->wasSent($post))
        {
            if($post[$form->namespace]['confirm'] === 'True')
            {
                if(!isset($editingLanguage) || count($dcModel->Translation) === 1)
                {
                    $dcModel->delete();
                }
                else
                {
                    //delete the one language
                    $dcModel->Translation[$editingLanguage]->delete();
                    unset($dcModel->Translation[$editingLanguage]);
                }
                //FIXME: check if it was successful
                $this->_redirectToIndex();
            }
        }
        $this->view->deleteableLangs = array_keys($dcModel->Translation->toArray());
        $dump['deleteableLangs'] = $this->view->deleteableLangs;
        $this->view->dump = $dump;
        $this->view->form = $form;
        // Renders deletei18n.phtml
        $this->render('deletei18n', null, true);
    }
    protected function _redirectToUpdate($toId, $editingLanguage = '')
    {
        $urlArray = $this->_urlArray;
        $urlArray['action'] = 'update';
        if($editingLanguage)
        {
            $urlArray[$this->_editLangKey] = $editingLanguage;
        }
        $urlArray['id'] = $toId;
        $this->_redirectTo($urlArray);
    }
    protected function _redirectToIndex()
    {
        $urlArray = $this->_urlArray;
        $urlArray['action'] = 'index';
        $this->_redirectTo($urlArray);
    }
    protected function _redirectTo(array $array)
    {
        $this
            ->_helper
            ->redirector
            ->gotoRouteAndExit(
                $array,
                'modules_i18n', True
        );
    }
    protected function _getEditingLanguage()
    {
        $editingLanguage = $this->getRequest()->getParam($this->_editLangKey);
        if(!empty($editingLanguage) && !GC_I18n::isLocale($editingLanguage))
        {
            throw new Zend_Controller_Action_Exception(sprintf('Can\'t edit "%1$s" it\'s not an allowed Locale.', htmlspecialchars($editingLanguage)), 404);
        }
        //set the default language if it was not sent
        return (empty($editingLanguage))? $this->getLang() : $editingLanguage;
    }
    /*
     * This is a blacklist implementation
     * its deletes keys that we know from the post array
     * its not for security, the stricter whitelist after this filter
     * "GC_DOMForm::wasSent()" will fail if we don't remove these keys from its input
     * we know these keys because something we did set them! (ie. a wysiwyg editor)
     */
    protected function _cleanPost(array $post, $aditionalRules)
    {
        if(!is_array($aditionalRules))
        {
            $aditionalRules = array($aditionalRules);
        }

        $keys = array_merge (array('__ALL__'), $aditionalRules);
        foreach($keys as $key)
        {
            if(array_key_exists($key, $this->_cleanPostKeys)
            && is_array($this->_cleanPostKeys[$key])
            && count($this->_cleanPostKeys[$key]))
            {
                foreach($this->_cleanPostKeys[$key] as $delete)
                {
                    if(array_key_exists($delete, $post))
                    {
                        unset($post[$delete]);
                    }
                }
            }
        }
        return $post;
    }
}
