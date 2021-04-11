<?php
class Backend_UserController extends Formation_Controller_Action_Simple
{
    protected $_modelClass = 'Backend_Model_User';
    protected $_urlArray = array(
        'module' => 'backend',
        'controller' => 'user',
        'action' => Null
    );
    protected $_form = array(
        'create' => 'Backend_Form_UserCreate',
        'update' => 'Backend_Form_UserUpdate',
        'delete' => 'Backend_Form_UserDelete',
    );
    protected $_indexListItem = array(
        'fields' => array('name'),
        'format' => '%1$s'
    );

    protected $_typeName = 'User';
    protected $_notHumanMsg = 'You didn\'t pass the <a href="http://en.wikipedia.org/wiki/CAPTCHA"><acronym xml:lang="en" lang="en" title="Completely Automated Public Turing test to tell Computers and Humans Apart">CAPTCHA</acronym></a>.';
    protected function _setUpForm(GC_DomForm_Subset $form)
    {
        $translate = GC_Translate::get();
        $groupModel = new Backend_Model_Group();
        $groups = $groupModel->findAll();
        $options = array(
            array('', $translate->_('none')),
        );
        foreach($groups as $group)
        {
            $options[] = array($group['id'], $group['name']);
        }
        $values['dcGroupId'] = $options;
        $form->setDefaults($values, $form->namespace);
    }
    protected function _setUpFormCreate(/* Backend_Form_UserCreate */$form)
    {
        if(!$form instanceof Backend_Form_UserCreate)
            throw new GC_Exception('Form must be a Backend_Form_UserCreate');
        $this->_setupForm($form);
    }
    protected function _setUpFormUpdate(/* Backend_Form_UserUpdate */$form)
    {
        if(!$form instanceof Backend_Form_UserUpdate)
            throw new GC_Exception('Form must be a Backend_Form_UserUpdate');
        $this->_setupForm($form);
    }

    protected function _postUpdate($dcModel)
    {
        $passwordUrl = array(
            'module' => 'backend',
            'controller' => 'user',
            'action' => 'password',
            'id' => $this->view->id
        );
        $urlHelper = new Zend_View_Helper_Url();
        $this->view->specials = sprintf(
            '<a href="%2$s">%1$s</a>',
            'Generate a Password for this user and send it via email.',
            $urlHelper->url($passwordUrl, 'modules_i18n' ,True)
        );
    }
    public function logoutAction()
    {
        Formation_Login::logout();
        $urlArray = $this->_urlArray;
        $urlArray['action'] = 'login';
        $this->_redirectTo($urlArray);
    }
    public function loginAction()
    {
        $dump = array();
        $this->_urlArray['action'] = 'login';
        $this->view->urlArray = $this->_urlArray;
        $this->view->verb = 'Login';
        $this->view->headlineFormat = '<h1>%2$s</h1>';

        $recoverUrl = $this->_urlArray;
        $recoverUrl['action'] = 'recover';
        $urlHelper = new Zend_View_Helper_Url();
        $forgotten = sprintf(
            'Forgot your Password? <a href="%1$s">Recover your Password!</a>',
            $urlHelper->url($recoverUrl, 'modules_i18n' ,True)
        );
        $this->view->message = $forgotten;

        $request = $this->getRequest();
        $post = ($request->isPost()) ? $request->getPost() : array();
        $form = new Backend_Form_UserLogin();
        $recaptcha = $form->recaptcha;
        $form = $form->build()->form;
        $this->view->form = $form;
        if(!$form->wasSent($post))
        {
            $user = Zend_Registry::getInstance()->user;
            if($user)
            {
                $this->view->message = sprintf('You are logged in as %1$s.', $user->name);
            }
            $this->render('blank', null, true);
            return;
        }
        //if the user sent the form he gets logged out regardlessly
        Formation_Login::logout();

        //WTF?!? there seems some magic happening
        //$form->setValue($post[$form->namespace]['name'], 'name');

        if(!$recaptcha->isHuman($post))
        {
            $this->view->message = $this->_notHumanMsg;
            $this->render('blank', null, true);
            return;
        }

        // Set up the authentication adapter
        $authAdapter = DcUser::getAuthAdapter()->
            setIdentity($post[$form->namespace]['name'])->
            setCredential($post[$form->namespace]['password']);
        //check login data...
        // Get a reference to the singleton instance of Zend_Auth
        $auth = Zend_Auth::getInstance();
        // Attempt authentication, saving the result
        $result = $auth->authenticate($authAdapter);
        if($result->isValid())
        {
            //throw new Exception('the next line fails when loggin in beeing logged in...');
            //success AKA logged in
            $dcUser = $authAdapter->getAuthData();

            //$user = $this->_getModel((array) $row);
            Formation_Login::login($dcUser);
            $urlArray = $this->_urlArray;
            $urlArray['controller'] = 'index';
            $urlArray['action'] = 'index';
            $this->_redirectTo($urlArray);
        }
        else
        {
            //fail
            $message = 'Sorry, login failed.';
        }
        $this->view->message = ($message) ? $message.'<br />'.$forgotten : $forgotten;
        $this->view->dump = $dump;
        $this->render('blank', null, true);
    }
    public function setupAction()
    {
        //ask for the Password helps to prevent session hijacking
        //ask for a captcha to make it harder to bruteforce the password here
        $this->view->verb = 'Setup';
        $this->_urlArray['action'] = 'setup';
        $this->view->urlArray = $this->_urlArray;
        $this->view->headlineFormat = '<h1>%2$s your Account.</h1>';

        $dump = array();
        $request = $this->getRequest();
        $post = ($request->isPost()) ? $request->getPost() : array();
        $form = new Backend_Form_UserSetup();
        $recaptcha = $form->recaptcha;
        $form = $form->build()->form;
        $this->view->form = $form;
        $model = $this->_getModel();
        //check if the OLD passsword is valid
        $user = Zend_Registry::getInstance()->user;

        if($form->wasSent($post))
        {
            //WTF?!? there seems some magic happening
            //$form->setValue($post[$form->namespace]['name'], 'name');
            if(!$recaptcha->isHuman($post))
            {
                $this->view->message = $this->_notHumanMsg;
            }
            else if($user->password !== $user->saltPassword($post[$form->namespace]['password_old'], $user->salt))
            {
                //OLD password was wrong
                $this->view->message = 'Your old password was wrong.';
            }
            else
            {
                if(!$model->update($user, $post, $form->namespace))
                {
                    $this->view->message = 'Something went wrong.';
                    $dump['messagesFormat'] = $model->getMessages();
                    $form->setMessages($model->getMessages());
                }
                else
                {
                    $this->view->message = 'Updated Successfully.';
                }
            }

        }

        $usr = $model->map4Form($user, $form->namespace);
        $form->setValues($usr[$form->namespace], $form->namespace);

        $this->render('update', null, true);
    }

    public function recoverAction()
    {
        //captcha
        //username
        //correct mail
        $dump = array();
        $this->_urlArray['action'] = 'recover';

        $this->view->urlArray = $this->_urlArray;
        $this->view->verb = 'request';
        $this->view->headlineFormat = '<h1>Request a new Password</h1>';
        $request = $this->getRequest();
        $post = ($request->isPost()) ? $request->getPost() : array();
        $form = new Backend_Form_UserForgotten();
        $recaptcha = $form->recaptcha;
        $form = $form->build()->form;
        $this->view->form = $form;
        if($form->wasSent($post))
        {
            if(!$recaptcha->isHuman($post))
            {
                $this->view->message = $this->_notHumanMsg;
            }
            else
            {
                $model = $this->_getModel();
                $dcModel = $model->findByNameEmail(
                    $post[$form->namespace]['name'],
                    mb_strtolower($post[$form->namespace]['email'])
                );
                if($dcModel)
                {
                    $this->_generateAndSendPwd($dcModel);
                }
                else
                {
                    $this->view->message = 'A user with the requested data was not found in the system.';
                }
            }
        }
        else
        {
            $this->view->message = 'You can request a new password for your account here. Just provide your username and e-mail address.';
        }
        $this->render('blank', null, true);
    }
    public function passwordAction()
    {
        //send a generated password to an user
        $dump = array();
        $this->view->headlineFormat = '';
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
        $this->view->message = sprintf('Generate a new password for %1$s and send it to %2$s', $dcModel->name, $dcModel->email);

        $post = ($request->isPost()) ? $request->getPost() : array();
        $dump['post'] = $post;
        $form = new Backend_Form_UserPassword();
        $form = $form->build()->form;
        if($form->wasSent($post))
        {
            if($post[$form->namespace]['confirm'] === 'True')
            {
                $this->_generateAndSendPwd($dcModel);
            }
        }
        $this->view->dump = $dump;
        $this->view->form = $form;
        // Renders delete.phtml

        $this->_urlArray['action'] = 'password';
        $this->_urlArray['id'] = $id;
        $this->view->urlArray = $this->_urlArray;
        $this->render('blank', null, true);
    }
    protected function _generateAndSendPwd($dcModel)
    {
        $newPwd = GC_PasswordGenerator::generatePwd();
        $data['namespace'] = array(
            'email' => $dcModel->email,
            'password' => $newPwd,
            'password_confirm' => $newPwd
        );
        $model = $this->_getModel();
        if(!$model->update($dcModel, $data, 'namespace'))
        {
            $this->view->message = 'Generating the new password did not succeed.';
            //this will get logged...
            throw new GC_Debug_Exception($model->getMessages());
        }
        $mail = new Zend_Mail(ENCODING);
        $system = Zend_Registry::getInstance()->config->system;
        $mail->setFrom($system->email);
        $mail->addTo($dcModel->email);
        $mail->setSubject(Zend_Registry::getInstance()->config->system->title.' You requested a new password.');
        $bodyText = sprintf('Your new password is: %1$s'."\n"
            .'Your username is: %2$s'."\n"
            .'Please feel free to login at %3$s.',
            $newPwd,
            $dcModel->name,
            $system->loginUrl
        );
        $mail->setBodyText($bodyText);
        $mail->send();
        $this->view->message = sprintf('A new password for %1$s was generated and sent to %2$s', $dcModel->name, $dcModel->email);
        return True;
    }
}

