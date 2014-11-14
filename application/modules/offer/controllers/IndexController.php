<?php

/**
 * Offer_IndexController
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Offer_IndexController extends MF_Controller_Action {
 
    public static $articleItemCountPerPage = 10;
    
    public function listOfferAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $pageService = $this->_service->getService('Page_Service_Page');
        
	
	$page = $pageService->geti18nPage('oferta', 'slug');
        $offers = $offerService->getAllOffers(Doctrine_Core::HYDRATE_ARRAY);
        
	$metatagService->setViewMetatags($page['metatag_id'], $this->view);
       
        $this->view->assign('page', $page);
        $this->view->assign('offers', $offers);
        
        $this->_helper->layout->setLayout('page');
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function indexAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $offers = $offerService->getAllOffers(Doctrine_Core::HYDRATE_ARRAY);
        
        
        if(!$singleOffer = $offerService->getFullOffer($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Offer '.$this->getRequest()->getParam('slug').' not found', 404);
        }
        $metatagService->setViewMetatags($singleOffer->get('Metatags'), $this->view);
       
        $this->view->assign('offers', $offers);
        $this->view->assign('singleOffer', $singleOffer);
        
        $this->_helper->layout->setLayout('page');
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
     
    
    
}

