<?php

/**
 * Banner_IndexController
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Banner_IndexController extends MF_Controller_Action {
 
   public function listBannerAction(){
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        if(!$banners = $bannerService->getAllBanners()) {
            throw new Zend_Controller_Action_Exception('Banners not found ');
        }
        
        
        $this->view->assign('banners', $banners);
        $this->_helper->actionStack('layout-serwis10', 'index', 'default');
        
    }
}

