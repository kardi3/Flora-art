<?php

class User_AuthController extends MF_Controller_Action
{
    public function loginAction() {
        
        $this->_helper->actionStack('layout','index','default');
        
         $this->_helper->layout->setLayout('login');
        $auth = $this->_service->getService('User_Service_Auth');
        $userService = $this->_service->getService('User_Service_User');
        
       $metatagService = $this->_service->getService('Default_Service_Metatag');
      
        $this->view->messages()->clean();
        
	$translator = $this->getFrontController()->getParam('bootstrap')->getContainer()->get('translate');
	$form = new User_Form_Login();
        $form->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $form->getElement('submit')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);
        $form->getElement('remember')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);
      //  $form->setAction($this->_helper->url->url(array(), 'domain-i18n:login'));
	$form->setTranslator($translator);
        $form->getElement('username')->setAttrib('class', 'span10');
        $form->getElement('password')->setAttrib('class', 'span10');
                    
        if($this->getRequest()->getCookie('remember_me') == 'true') {
            $form->getElement('remember')->setChecked(true);
        }
       
	if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                
                if(!$user = $userService->getUser($form->getValue('username'), 'email')):
                    $this->view->messages()->add($this->view->translate('Niepoprawne dane'), 'error');
                elseif ($user && !$user->isActive()):
                    $this->view->messages()->add($this->view->translate('User is not active'), 'error');
                else:
                    
                    $result = $auth->authenticate($form->getValue('username'), $form->getValue('password'));
                    if($form->getElement('remember') && $form->getElement('remember')->isChecked()) {
                        $auth->setRememberMeCookie(true);
                    } else {
                        $auth->setRememberMeCookie(false);
                    }
                    if($result->isValid()) {
                        if (!$auth->getRequestedUrl()):
                            $this->_helper->redirector->gotoRoute(array('action' => 'my-gyms'),'domain-gym-account');
                        endif;
                        if($user->role == "admin"):
                           $this->_helper->redirector->gotoRoute(array(), 'admin');
                        else:
                            $this->_helper->redirector->gotoRoute(array('action' => 'my-gyms'),'domain-gym-account');
                        endif;
                    } 

                    // error handling
                        switch($result->getCode()) {
                            case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                                $this->view->messages()->add($this->view->translate('User not found!!!'), 'error');
				break;
                            case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                                $this->view->messages()->add($this->view->translate('Credential invalid!!!'), 'error');
				break;
				}
                    
                endif;  
            }
	}
		
        $this->view->assign('loginForm', $form);
             
        if($this->getFrontController()->getRouter()->getCurrentRouteName() == 'admin') {
            $this->_helper->layout->setLayout('admin.login');
            $this->_helper->viewRenderer('admin.login');
        } 
    }
    
    public function logoutAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
        
//        $facebook = $this->getFrontController()->getPlugin('User_Plugin_Facebook');
//        $facebook->destroyAuthentication();
        
        $serviceBroker = MF_Service_ServiceBroker::getInstance();
        $authService = $serviceBroker->getService('User_Service_Auth');
        $authService->destroyAuthentication();
        
    	// redirect
    	$this->_helper->redirector->gotoRouteAndExit(array(), 'domain-homepage');
    }
    
    
}

