<?php

/**
 * Gallery_AdminController
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Gallery_AdminController extends MF_Controller_Action {
    
    public function listGalleryAction() {
        
    }
    
    public function listGalleryDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
                
        $table = Doctrine_Core::getTable('Gallery_Model_Doctrine_Gallery');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table,
            'class' => 'Gallery_DataTables_Gallery', 
            'columns' => array('x.id','t.name'),
            'searchFields' => array('x.id','t.name')
        ));
        
       
        $results = $dataTables->getResult();
        
        $language = $i18nService->getAdminLanguage();
        
        $rows = array();
    
        
        foreach($results as $result) {
            $row = array();
            $row[] = $result->id;
            $row[] = $result->Translation[$language->getId()]->name;
            
            $options = '<a href="' . $this->view->adminUrl('edit-gallery', 'gallery', array('id' => $result->id)) . '" class="edit-item"><span class="icon24 entypo-icon-settings"></span></a>';
            $options .= '<a href="' . $this->view->adminUrl('delete-gallery', 'gallery', array('id' => $result->id)) . '" class="delete-item"><span class="icon24 icon-remove"></span></a>';
            $row[] = $options;
        
            $rows[] = $row;
        }
        
        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );

        $this->_helper->json($response);
    }
    
    public function addGalleryAction() {
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $form = $galleryService->getGalleryForm();
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        $languages = $i18nService->getLanguageList();
        
        $user = $this->_helper->user();

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $values['user_id'] = $user->getId();
                    $gallery = $galleryService->saveGalleryFromArray($values);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                   // if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-gallery', 'gallery', array('id' => $gallery->getId())));
                    
                    //$this->_helper->redirector->gotoUrl($this->view->adminUrl('list-gallery', 'gallery'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
    }
    
    public function editGalleryAction() {
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$gallery = $galleryService->getGallery((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Gallery not found');
        }
        
        $form = $galleryService->getGalleryForm($gallery);
        $metatagsForm = $metatagService->getMetatagsSubForm($gallery->get('Metatag'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        $languages = $i18nService->getLanguageList();
        $adminLanguage = $i18nService->getAdminLanguage();
        
	if(!strlen($gallery->photo_root_id)) {
	    $root = $photoService->createPhotoRoot();
	    $gallery->set('PhotoRoot', $root);
	    $gallery->save();
	}
	
        $user = $this->_helper->user();

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    if($metatags = $metatagService->saveMetatagsFromArray($gallery->get('Metatag'), $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $values['user_id'] = $user->getId();
                    
                    $gallery = $galleryService->saveGalleryFromArray($values);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-gallery', 'gallery', array('id' => $gallery->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-gallery', 'gallery'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('gallery', $gallery);
        $this->view->assign('form', $form);
    }
    
    public function deleteGalleryAction() {
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        
        if(!$gallery = $galleryService->getGallery($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Gallery not found', 404);
        }

        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
            
            $gallery->get('Metatag')->delete();
            $galleryService->removeGallery($gallery);
           
            
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            echo $this->_service->get('log')->log($e->getMessage(), 4);
        }      
       // $this->_helper->viewRenderer->setNoRender();
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-gallery', 'gallery'));
    }
    
    public function addGalleryPhotoAction() {
         $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        
  
        $photoDimension = $photoDimensionService->getDimension('gallery');
	
        $photoDimension2 = $photoDimensionService->getDimension('galleryslider');
        
	$photoDimension = array_merge($photoDimension,$photoDimension2);
        if(!$gallery = $galleryService->getGallery((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Gallery not found');
        }
        
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        if(!array_key_exists('domain', $options)) {
            throw new Zend_Controller_Action_Exception('Domain string not set');
        }
        
        $hrefs = $this->getRequest()->getParam('hrefs');

        if(is_array($hrefs) && count($hrefs)) {
            foreach($hrefs as $href) {
                $path = str_replace("http://" . $options['domain'], "", urldecode($href));
                $filePath = $options['publicDir'] . $path;
                if(file_exists($filePath)) {
                    $pathinfo = pathinfo($filePath);
                    $slug = MF_Text::createSlug($pathinfo['basename']);
                    $name = MF_Text::createUniqueFilename($slug, $photoService->photosDir);
                    try {
                        $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                        $root = $gallery->get('PhotoRoot');
                        if($root->isInProxyState()) {
                            $root = $photoService->createPhotoRoot();
                            $gallery->set('PhotoRoot', $root);
                            $gallery->save();
                        }

                       $photoService->createPhoto($filePath, $name, $pathinfo['filename'], $photoDimension, $root, true);

                       $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    } catch(Exception $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->_service->get('Logger')->log($e->getMessage(), 4);
                    }
                }
            }
        }
        $list = '';
        
        $root = $gallery->get('PhotoRoot');
        $root->refresh();
        if(!$root->isInProxyState()) {
            $galleryPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/gallery-photos.phtml', 'product', array('photos' => $galleryPhotos, 'gallery' => $gallery));
        }
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $gallery->getId()
        ));
    }
    
    public function moveGalleryPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        
        if(!$gallery = $galleryService->getGallery($this->getRequest()->getParam('gallery'))) {
            throw new Zend_Controller_Action_Exception('Gallery not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Gallery photo not found');
        }

        $photoService->movePhoto($photo, $this->getRequest()->getParam('move', 'down'));
        
        $list = '';
        
        $root = $gallery->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $galleryPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/gallery-photos.phtml', 'gallery', array('photos' => $galleryPhotos, 'gallery' => $gallery));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $gallery->getId()
        ));
    }
    
    public function changePhotoNameAction() {   
        $photoService = $this->_service->getService('Media_Service_Photo');
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        
        if(!$gallery = $galleryService->getGallery((int) $this->getRequest()->getParam('gallery-id'))) {
            throw new Zend_Controller_Action_Exception('Gallery not found');
        }
        
        
        if(!$photo = $photoService->getPhoto($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo not found');
        }
        
        $photo->setTitle($this->getRequest()->getParam('name'));
        $photo->save();
        
        
        $list = '';
        
        $root = $gallery->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $galleryPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/gallery-photos.phtml', 'gallery', array('photos' => $galleryPhotos, 'gallery' => $gallery));
        }   
        
         $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $photo->getId()
        ));
    }
    
    public function removeGalleryPhotoAction() {   
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$gallery = $galleryService->getGallery((int) $this->getRequest()->getParam('gallery-id'))) {
            throw new Zend_Controller_Action_Exception('Gallery not found');
        }
        
        if(!$photo = $photoService->getPhoto($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }
        
        try {
            $photoService->removePhoto($photo);
            
        } catch(Exception $e) {
            $this->_service->get('Logger')->log($e->getMessage(), 4);
        }
              
        $list = '';
        
        $root = $gallery->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $galleryPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/gallery-photos.phtml', 'gallery', array('photos' => $galleryPhotos, 'gallery' => $gallery));
        }   
        
         $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $photo->getId()
        ));
    }
    
}

