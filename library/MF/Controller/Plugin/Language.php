<?php

/**
 * Language
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class MF_Controller_Plugin_Language extends Zend_Controller_Plugin_Abstract
{
    public $lang;
    
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $translator = Zend_Controller_Router_Route::getDefaultTranslator();
        
        $uriParts = explode('/', $_SERVER['REQUEST_URI']);
        $this->lang = (Zend_Locale::isLocale($uriParts[1])) ? $uriParts[1] : null;

      
        if($translator->isAvailable($this->lang)) {
            $translator->setLocale($this->lang);
        // set default site locale
        } elseif($locale = Zend_Registry::get('Zend_Locale')) {
            $translator->setLocale($locale);
        } else {
            throw new Zend_Controller_Action_Exception("Language $this->lang not available", 500);
        }
        
        Zend_Controller_Router_Route::setDefaultTranslator($translator);
	}
    
    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        $front = Zend_Controller_Front::getInstance();
        
          $regionSession = new Zend_Session_Namespace('regionSession');

          $params = $this->getRequest()->getParams();
          if(isset($params['region'])){
              $regionSession->region = $params['region'];
          }
          
          if(!strlen($regionSession->region)){
               $regionSession->region = "malopolska";
          }
          $view = $front->getParam('bootstrap')->getResource('view');
          $view->assign('region', $regionSession->region);
          
        $locale = Zend_Registry::get('Zend_Locale');
        $translate = Zend_Registry::get('Zend_Translate');
        
        if($request->getModuleName() == 'admin') {
            $language = $locale->getLanguage();
        } elseif($lang = $request->getParam('lang')) {
            $language = $lang;
            if($lang != $session->lang) {
                $session->lang = $language;
            }
        } elseif(isset($session->lang) && in_array($session->lang, $translate->getList())) {
            $language = $session->lang;
        } else {
            $language = $locale->getLanguage();
        }

        $translationNotAvailable = false; // translation of this language not available --> 404
        if(!in_array($language, $translate->getList())) {
            $language = $locale->getLanguage();
            $translationNotAvailable = true;
        }

        if(array_key_exists($language, Zend_Locale::getLocaleList())) {
            $view = $front->getParam('bootstrap')->getResource('view');
            $view->assign('language', $language);
            $view->assign('locale', $locale);


            $container = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getContainer();

            $container->set('Zend_Locale', $locale);

            $translate->setLocale($language);
            $container->set('Zend_Translate', $translate);

            $container->set('Zend_Currency', new Zend_Currency(array(), $locale));

            Zend_Form::setDefaultTranslator($translate);
            Zend_Controller_Front::getInstance()->getRouter()->setGlobalParam('lang', $language);

        }
       
        
        if($translationNotAvailable) {
            $this->_redirect404($request);
        }
    }
    
    protected function _redirect404($request) {
        $request->setModuleName('default');
        $request->setControllerName('error');
        $request->setActionName('error404');
    }

}


