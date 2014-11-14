<?php

/**
 * Banner_AdminController
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Banner_AdminController extends MF_Controller_Action {
   
  
     public function listBannerAction() {

   }
    
   public function listBannerDataAction() {    
        $i18nService = $this->_service->getService('Default_Service_I18n');
       
        $table = Doctrine_Core::getTable('Banner_Model_Doctrine_Banner');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Banner_DataTables_Banner', 
            'columns' => array('pt.name', 'p.created_at'),
            'searchFields' => array('pt.name', 'p.created_at')
        ));
        
        $language = $i18nService->getAdminLanguage();
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result->Translation[$language->getId()]->name;
            $row[] = $result['created_at'];
            if($result['status'] == 1){ 
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-banner', 'banner', array('id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-checkbox-2"></span></a>';
           
                   }else{
                        $row[] = '<a href="' . $this->view->adminUrl('refresh-status-banner', 'banner', array('id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-checkbox-unchecked-2"></span></a>';
         
                 }
           $options = '<a href="' . $this->view->adminUrl('edit-banner', 'banner', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-banner', 'banner', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addBannerAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $bannerService->getBannerForm();
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                       
                    $banner = $bannerService->saveBannerFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-banner', 'banner', array('id' => $banner->getId())));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
    }
    
    public function editBannerAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $translator = $this->_service->get('translate');
        
        if(!$banner = $bannerService->getBanner($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $bannerService->getBannerForm($banner);
        
        $metatagsForm = $metatagService->getMetatagsSubForm($banner->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray($banner->get('Metatags'), $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $banner = $bannerService->saveBannerFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-banner', 'banner'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('banner', $banner);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
    }
    
    public function removeBannerAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if($banner = $bannerService->getBanner($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $metatag = $metatagService->getMetatag((int) $banner->getMetatagId());
                $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $banner->getMetatagId());

                $photoRoot = $banner->get('PhotoRoot');
                $photoService->removePhoto($photoRoot);
                
                $bannerService->removeBanner($banner);

                $metatagService->removeMetatag($metatag);
                $metatagTranslationService->removeMetatagTranslation($metatagTranslation);

                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-banner', 'banner'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-banner', 'banner'));
    }
    
    public function addBannerPhotoAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        
  
        $photoDimension = $photoDimensionService->getDimension('banner');
        
        if(!$banner = $bannerService->getBanner((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        if(!array_key_exists('domain', $options)) {
            throw new Zend_Controller_Action_Exception('Domain string not set');
        }
        
        $href = $this->getRequest()->getParam('hrefs');

        if(is_string($href) && strlen($href)) {
            $path = str_replace("http://" . $options['domain'], "", urldecode($href));
            $filePath = urldecode($options['publicDir'] . $path);
            if(file_exists($filePath)) {
                $pathinfo = pathinfo($filePath);
                $slug = MF_Text::createSlug($pathinfo['basename']);
                $name = MF_Text::createUniqueFilename($slug, $photoService->photosDir);
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $root = $banner->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], $photoDimension, false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], $photoDimension, false);
                    }

                    $banner->set('PhotoRoot', $photo);
                    $banner->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $list = '';
        
        $bannerPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $banner->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $bannerPhotos->add($root);
            $list = $this->view->partial('admin/banner-main-photo.phtml', 'banner', array('photos' => $bannerPhotos, 'banner' => $banner));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $banner->getId()
        ));
        
    }
    
    public function editBannerPhotoAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$banner = $bannerService->getBanner((int) $this->getRequest()->getParam('banner-id'))) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            $this->view->messages()->add($translator->translate('First you have to choose picture'), 'error');
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-banner', 'banner', array('id' => $banner->getId())));
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-banner-photo', 'banner', array('banner-id' => $banner->getId(), 'id' => $photo->getId())));
        
        $photosDir = $photoService->photosDir;
        $offsetDir = realpath($photosDir . DIRECTORY_SEPARATOR . $photo->getOffset());
        if(strlen($photo->getFilename()) > 0 && file_exists($offsetDir . DIRECTORY_SEPARATOR . $photo->getFilename())) {
            list($width, $height) = getimagesize($offsetDir . DIRECTORY_SEPARATOR . $photo->getFilename());
            $this->view->assign('imgDimensions', array('width' => $width, 'height' => $height));
        }

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $photo = $photoService->saveFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-banner-photo', 'banner', array('id' => $banner->getId(), 'photo' => $photo->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-banner', 'banner', array('id' => $banner->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
        
      
        $this->view->admincontainer->findOneBy('id', 'edit-banner')->setLabel($banner->Translation[$adminLanguage->getId()]->name);
        $this->view->admincontainer->findOneBy('id', 'edit-banner')->setParam('id', $banner->getId());
        $this->view->adminTitle = $this->view->translate($this->view->admincontainer->findOneBy('id', 'cropbannerphoto')->getLabel());

        $this->view->assign('banner', $banner);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Banner_Model_Doctrine_Banner::getBannerPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeBannerPhotoAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$banner = $bannerService->getBanner((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $banner->get('PhotoRoot')) {
                if($root && !$root->isInProxyState()) {
                    $photo = $photoService->updatePhoto($root);
                    $photo->setOffset(null);
                    $photo->setFilename(null);
                    $photo->setTitle(null);
                    $photo->save();
                }
            }
        
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage(), 4);
        }
        
        $list = '';
        
        $bannerPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $banner->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $bannerPhotos->add($root);
            $list = $this->view->partial('admin/banner-main-photo.phtml', 'banner', array('photos' => $bannerPhotos, 'banner' => $banner));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $banner->getId()
        ));
        
    }
    
    public function refreshStatusBannerAction() {
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        if(!$banner = $bannerService->getBanner((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Banner not found');
        }
        
        $bannerService->refreshStatusBanner($banner);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-banner', 'banner'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
}

