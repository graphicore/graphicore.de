<?php
/*
 * changelog
 * 2009/11/23
 *      created while making a controller for usergroups recognizing that 95% was identical with a controller for users
 */
abstract class Formation_Controller_Action_Simple extends GC_Controller_Action
{
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
        // Renders index.phtml
        $this->render('index', null, true);
    }
    protected function _setUpFormCreate($form)
    {}
    public function createAction()
    {
        $dump = array();
        $this->view->verb = 'create';
        $this->view->headlineFormat = '<h1>%2$s a new %1$s</h1>';

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
            $dcmodel = $model->create($post, $form->namespace);//$post gets changed in here
            if (False !== $dcmodel)
            {
                //success
                $this->view->message = 'model says it was created';
                //go to the update page
                $this->_redirectToUpdate($dcmodel->id);
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
        // Renders create.phtml
        $this->render('create', null, true);
    }
    protected function _setUpFormUpdate($form)
    {}
    protected function _postUpdate($dcModel){}
    public function updateAction()
    {
        $dump = array();
        $this->view->verb = 'update';

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
            if(!$model->update($dcModel, $post, $form->namespace))
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
        $form->setValues($model->map4Form($dcModel, $form->namespace));
        $this->view->dump = $dump;
        $this->view->form = $form;
        // Renders update.phtml

        $this->_postUpdate($dcModel);

        $this->render('update', null, true);
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
        $post = ($request->isPost()) ? $request->getPost() : array();
        $dump['ignoredKeys'] = array_keys($post);
        $post = $this->_cleanPost($post, __FUNCTION__);
        $dump['ignoredKeys'] = array_diff($dump['ignoredKeys'], array_keys($post));
        $dump['post'] = $post;

        $form = new $this->_form['delete']();
        //radiobox do you really want to delete this Usaer
        //some text and links to other places
        //giving some hints and links like
            //how to unpublish this type (at update)
            //where to delete only translations //FIXME: implement that!
            //warn about how much data will get lost
        $form = $form->build()->form;
        if($form->wasSent($post))
        {
            if($post[$form->namespace]['confirm'] === 'True')
            {
                //FIXME: check if it was successful
                $dcModel->delete();
                $this->_redirectToIndex();
            }

        }
        $this->view->dump = $dump;
        $this->view->form = $form;
        // Renders delete.phtml
        $this->render('delete', null, true);
    }
    protected function _redirectToUpdate($toId)
    {
        $urlArray = $this->_urlArray;
        $urlArray['action'] = 'update';
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
    /*
     * This is a blacklist implementation
     * its deletes blacklisted that we know from the post array
     * its not for security, the stricter whitelist after this filter will
     * GC_DOMForm::wasSent() fail if we don't remove these keys from its input
     *
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