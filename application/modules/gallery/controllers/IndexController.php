<?php

/**
 * Gallery_IndexController
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Gallery_IndexController extends MF_Controller_Action {
    
    public function indexAction() {
       
        $this->_helper->layout->setLayout('page');
        
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $metatagService = $this->_service->getService('Default_Service_Metatag');

        if(!$gallery = $galleryService->getI18nGallery($this->getRequest()->getParam('slug'), 'slug', $this->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Gallery not found');
        }
        $photoDimension = $photoDimensionService->getElementDimension('gallery');
        
        $metatagService->setViewMetatags($gallery['metatag_id'], $this->view);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->view->assign('gallery', $gallery);
        $this->view->assign('photoDimension', $photoDimension);
    }
    
    public function galleryListAction() {
       
        $this->_helper->layout->setLayout('gallery');
        
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');

        $galleries = $galleryService->getAllActiveGalleries();
        
        $metatagService->setViewMetatags($galleries[0]['metatag_id'], $this->view);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->view->assign('galleries', $galleries);
    }
}

