<?php

class Default_IndexController extends MF_Controller_Action
{
    /**
     * Homepage action controller
     */
    public static $producerCountPerPage = 12;
    public static $resultsCountPerPage = 10;
    
    public function layoutAction() { 
        $settingService = $this->_service->getService('Default_Service_Setting');
        $pageService = $this->_service->getService('Page_Service_Page');

        
        if(!$homepage = $pageService->getI18nPage('homepage', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Homepage not found');
        }
        $settings = $settingService->getAllSettingsArray();
        
        $this->view->assign('company_address', $settings['company_address']);
        $this->view->assign('company_name', $settings['company_name']);
        $this->view->assign('company_city', $settings['company_city']);
        $this->view->assign('company_postal_code', $settings['company_postal_code']);
        $this->view->assign('company_phone', $settings['company_phone']);
        $this->view->assign('google_analytics', $settings['google_analytics']);
        $this->view->assign('contact_email', $settings['contact_email']);
        $this->view->assign('email', $settings['contact_email']);
        $this->view->assign('facebook_url', $settings['facebook_url']);
        $this->view->assign('googleplus_url', $settings['googleplus_url']);
        $this->view->assign('pin_url', $settings['pin_url']);
        $this->view->assign('twitter_url', $settings['twitter_url']);
        $this->view->assign('youtube_url', $settings['youtube_url']);
        $this->view->assign('homepage', $homepage);
        
        $this->_helper->actionStack('slider','index','slider');
        
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function indexAction() {
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $newsService = $this->_service->getService('News_Service_News');
	
        if(!$homepage = $pageService->getI18nPage('homepage', 'type', $this->view->language, Doctrine_Core::HYDRATE_ARRAY)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        
        $lastNews = $newsService->getLastNews(3,Doctrine_Core::HYDRATE_ARRAY);

        
        
        if($homepage != NULL):
            $metatagService->setViewMetatags($homepage['metatag_id'], $this->view);
        endif;
        
	
        $this->view->assign('lastNews', $lastNews);
        $this->view->assign('homepage', $homepage);

        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
     
    
    
    public function sidebarAction(){
        $partnerService = $this->_service->getService('Partner_Service_Partner');
        
        $partners = $partnerService->getAllActivePartners();
        $this->view->assign('partners', $partners);
        
        $this->_helper->viewRenderer->setResponseSegment('sidebar');
    }
    
    
    public function contactAction() {
        
        $this->_helper->layout->setLayout('contact');
        
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $serviceService = $this->_service->getService('Default_Service_Service');
        $settingService = $this->_service->getService('Default_Service_Setting');
        
        $settings = $settingService->getAllSettingsArray();
        
        $company_address = $settings['company_address'];
        $company_name = $settings['company_name'];
        $company_city = $settings['company_city'];
        $company_postal_code = $settings['company_postal_code'];
        
        $this->view->assign('company_address', $company_address);
        $this->view->assign('company_name', $company_name);
        $this->view->assign('company_city', $company_city);
        $this->view->assign('company_postal_code', $company_postal_code);
        if(!$page = $pageService->getI18nPage('contact', 'type', $this->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
 
	$contactEmail = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('contact_email');
        
        if ($page != NULL):
            $metatagService->setViewMetatags($page->get('Metatag'), $this->view);
        endif;
        $form = new Default_Form_Contact();
        $form->removeElement('firstName');
        $form->removeElement('lastName');
        $form->removeElement('email');
        $form->removeElement('csrf');
        $captchaDir = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('captchaDir');
        $form->addElement('captcha', 'captcha',
            array(
            'label' => 'Rewrite the chars', 
            'captcha' => array(
                'captcha' => 'Image',  
                'wordLen' => 5,  
                'timeout' => 300,  
                'font' => APPLICATION_PATH . '/../data/arial.ttf',  
                'imgDir' => $captchaDir,  
                'imgUrl' => $this->view->serverUrl() . '/captcha/',  
            )
        ));                    
          if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    if(!strlen($contactEmail)){
                        $this->_helper->redirector->gotoUrl($this->view->url(array('success' => 'fail'), 'domain-contact'));
                    }
                    $values = $_POST;
                    $serviceService->sendMail($values,$contactEmail);
		    
                    $form->reset();
                    $this->view->messages()->add($this->view->translate('Message sent'));
                        $this->_helper->redirector->gotoUrl($this->view->url(array('success' => 'success'), 'domain-contact'));
                  
                } catch(Exception $e) {
		    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
         }
         else{
                    $this->view->messages()->add('Podano błędny kod','error');
         }
                }

        $this->view->assign('form', $form);
        $this->view->assign('page', $page);
        $this->view->assign('success',$this->getRequest()->getParam('success'));
        $this->_helper->actionStack('layout', 'index', 'default');
    }
  
    
    public function aboutUsAction(){
        
        $this->_helper->layout->setLayout('about-us');
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');

       
        if(!$oUniqueGarage = $pageService->getI18nPage('o-unique-garage', 'type', $this->view->language, Doctrine_Core::HYDRATE_ARRAY)) {
            throw new Zend_Controller_Action_Exception('Page o-unique-garage not found');
        }
        
        if(!$celeNaszejFirmy = $pageService->getI18nPage('cele-naszej-firmy', 'type', $this->view->language, Doctrine_Core::HYDRATE_ARRAY)) {
            throw new Zend_Controller_Action_Exception('Page cele-naszej-firmy not found');
        }
        
        if(!$naszZespol = $pageService->getI18nPage('nasz-zespol', 'type', $this->view->language, Doctrine_Core::HYDRATE_ARRAY)) {
            throw new Zend_Controller_Action_Exception('Page nasz-zespol not found');
        }
        
        
        $metatagService->setViewMetatags($oUniqueGarage['metatag_id'], $this->view);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->view->assign('oUniqueGarage', $oUniqueGarage);
        $this->view->assign('celeNaszejFirmy', $celeNaszejFirmy);
        $this->view->assign('naszZespol', $naszZespol);
    }
}
