<?php

/**
 * Page_IndexController
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Page_IndexController extends MF_Controller_Action {
    
    public function indexAction() {
        $this->_helper->layout->setLayout('page');
        
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');

        if(!$page = $pageService->getI18nPage($this->getRequest()->getParam('slug'), 'slug', $this->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        $photoDimension = $photoDimensionService->getElementDimension('page');
        
        $metatagService->setViewMetatags($page['metatag_id'], $this->view);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->view->assign('page', $page);
        $this->view->assign('photoDimension', $photoDimension);
    }
}

