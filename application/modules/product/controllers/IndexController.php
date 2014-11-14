<?php

/**
 * Product_IndexController 
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Product_IndexController extends MF_Controller_Action {
    
    public static $productCountPerPage = 10;
    public static $producerProductCountPerPage = 10;
    public static $producerCountPerPage = 10;
    
    public function listBestsellerAction(){
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function listCategoryAction(){
//        $categoryService = $tchis->_service->getService('Product_Service_Category');
        
//        $categories = $categoryService->getAllCategories();
        
         $productService = $this->_service->getService('Product_Service_Product');
        $products = $productService->getAllProducts();

        $this->view->assign('products', $products);
        $this->_helper->actionStack('category-header');
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function categoryAction() {
      
        $this->_helper->actionStack('layout', 'index', 'default');
        
	$this->_helper->layout->setLayout('page');
	
        $categoryService = $this->_service->getService('Product_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $serviceService = $this->_service->getService('Default_Service_Service');
        $photoService = $this->_service->getService('Media_Service_Photo');
	
	
        $form = new Default_Form_Contact();
        $form->removeElement('firstName');
        $form->removeElement('csrf');
	$contactEmail = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('contact_email');
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
                    
		    
                    $values = $_POST;
		    
		    if(!$photo = $photoService->getPhoto($values['photo_id'])) {
			throw new Zend_Controller_Action_Exception('Photo not found');
		    }
		    
		    $values['photo_url'] = "http://$_SERVER[HTTP_HOST]/media/photos/".$photo['offset']."/270x150/".$photo['filename'];
		    
                    if(!strlen($contactEmail)){
                        $this->_helper->redirector->gotoUrl($this->view->url(array('success' => 'fail'), 'domain-contact'));
                    }
		    
                    $serviceService->sendMail($values,$contactEmail);
                    $form->reset();
                    $this->view->messages()->add($this->view->translate('Message sent'));
                    
                    $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
                  
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
	
	
        if(!$category = $categoryService->getFullCategory($this->getRequest()->getParam('category'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Category '.$this->getRequest()->getParam('slug').' not found', 404);
        }
         
        $metatagService->setViewMetatags($category->get('Metatags'), $this->view);
                
        $this->view->assign('form', $form);
        $this->view->assign('category', $category);

    }
    
    public function categoryMenuTreeAction()
    {
	$this->_helper->layout->disableLayout();
	
        $categoryService = $this->_service->getService('Product_Service_Category');
        
        if(!$category = $categoryService->getFullCategory($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Category '.$this->getRequest()->getParam('slug').' not found', 404);
        }
            
        if(!$category->getNode()->hasChildren()){
            $this->_helper->viewRenderer->setNoRender();
        }
        $this->view->assign('category',$category);
        $this->view->assign('tree',$category->getNode()->getChildren());
    }
    
    public function productAction() {
        
         if($this->view->messages()->count()>1): 
             $this->view->messages()->clear();
         endif; 
        
        $productService = $this->_service->getService('Product_Service_Product');
        $metatagService = $this->_service->getService('Default_Service_Metatag');

        if(!$product = $productService->getFullProduct($this->getRequest()->getParam('product'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Product not found', 404);
        }
        $metatagService->setViewMetatags($product->get('metatag_id'), $this->view);

       
        $form = new Product_Form_Contact();        
        
        $session = new Zend_Session_Namespace('CONTACT_CSRF');
        $form->getElement('csrf')->setSession($session)->initCsrfValidator();
        
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
        
        $contactEmail = $this->getFrontController()->getParam('bootstrap')->getOption('contact_email');

        if(!$contactEmail) {
            throw new Zend_Controller_Action_Exception('Contact email address not set');
        }
            
        $mail = new Zend_Mail('UTF-8');
        $mail->addTo($contactEmail);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $bodyText = "Mail kontaktowy od : ".$form->getValue('name')." ".$form->getValue('lastName')."\nDane kontaktowe: telefon - ".$form->getValue('phone')." email ".$form->getValue('email')." \n";
                    
                    $mail->setReplyTo($form->getValue('email'));
                    $mail->setSubject("Zapytanie o przedmiot ".$product['Translation'][$this->language]['name']." Identyfikator: ".$product['id']);
                    $mail->setBodyText($bodyText.$form->getValue('message'));
                    $mail->setFrom($contactEmail,$form->getValue('name'));
                    $mail->addTo('info@dodusmaszyny.pl');
                    $mail->addTo($contactEmail);
                    $mail->send();
                    $form->reset();
                    $this->view->messages()->add($this->view->translate('Message sent'));
                    
                } catch(Exception $e) {
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
            
        }
        $this->view->assign('form',$form);
        $this->view->assign('product', $product);
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function searchAction() {
      
        $this->_helper->actionStack('layout', 'index', 'default');
        
	$this->_helper->layout->setLayout('page');
	
        $categoryService = $this->_service->getService('Product_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $serviceService = $this->_service->getService('Default_Service_Service');
        $photoService = $this->_service->getService('Media_Service_Photo');
	$pageService = $this->_service->getService('Page_Service_Page');
        
	
        $form = new Default_Form_Contact();
        $form->removeElement('firstName');
        $form->removeElement('csrf');
	$contactEmail = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('contact_email');
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
                    
		    
                    $values = $_POST;
		    
		    if(!$photo = $photoService->getPhoto($values['photo_id'])) {
			throw new Zend_Controller_Action_Exception('Photo not found');
		    }
		    
		    $values['photo_url'] = "http://$_SERVER[HTTP_HOST]/media/photos/".$photo['offset']."/270x150/".$photo['filename'];
		    
                    if(!strlen($contactEmail)){
                        $this->_helper->redirector->gotoUrl($this->view->url(array('success' => 'fail'), 'domain-contact'));
                    }
		    
                    $serviceService->sendMail($values,$contactEmail);
                    $form->reset();
                    $this->view->messages()->add($this->view->translate('Message sent'));
                    
                    $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
                  
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
	
	
        $elements = $photoService->findPhotos($this->getRequest()->getParam('search'));
           
        if(!$page = $pageService->getPage('search','type')) {
            throw new Zend_Controller_Action_Exception('Page not found');
        } 
	
	
        $metatagService->setViewMetatags($page->get('Metatag'), $this->view);
                
        $this->view->assign('form', $form);
        $this->view->assign('elements', $elements);

    }
   
}

