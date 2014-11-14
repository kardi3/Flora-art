<?php

/**
 * Offer_AdminController
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Offer_AdminController extends MF_Controller_Action {
    
     public function init() {
        $this->_helper->ajaxContext()
                ->addActionContext('move-offer-photo', 'json')
                ->initContext();
        parent::init();
    }
    
    public function listOfferAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $i18nService = $this->_service->getService('Default_Service_I18n');
            
        $form = $offerService->getOfferForm();

        $languages = $i18nService->getLanguageList();

        $this->view->assign('form', $form);
        $this->view->assign('languages', $languages);
    }
    
    public function listOfferDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Offer_Model_Doctrine_Offer');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Offer_DataTables_Offer', 
            'columns' => array('xt.name', 'x.publish_date'),
            'searchFields' => array('x.id','xt.name','x.publish_date')
        ));
        
        $results = $dataTables->getResult();
        $language = $i18nService->getAdminLanguage();

        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result->id;
            $row[] = $result->Translation[$language->getId()]->name;
            $row[] = MF_Text::timeFormat($result->publish_date, 'H:i d/m/Y');
            
            $options = '<a href="' . $this->view->adminUrl('edit-offer', 'offer', array('id' => $result->id)) . '" name="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-offer', 'offer', array('id' => $result->id)) . '" class="remove" name="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addOfferAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $offerService->getOfferForm();
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        $form->translations->getSubForm($adminLanguage->getId())->getElement('name')->setRequired();
        
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
            
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('name' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    $offer = $offerService->saveOfferFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-offer', 'offer', array('id' => $offer->getId())));
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
    
    public function editOfferAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $attachmentService = $this->_service->getService('Media_Service_Attachment');
        
        $translator = $this->_service->get('translate');
        
        if(!$offer = $offerService->getOffer($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Offer not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $offerService->getOfferForm($offer);
        $form->translations->getSubForm($adminLanguage->getId())->getElement('name')->setRequired();
        
        $metatagsForm = $metatagService->getMetatagsSubForm($offer->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        if(!$offer->photo_root_id){
            $photoRoot = $photoService->createPhotoRoot();
            $offer->set('PhotoRoot',$photoRoot);
            $offer->save();
        }
        
        if(!$offer->attachment_id){
            $attachmentRoot = $attachmentService->createAttachmentRoot();
            $offer->set('AttachmentRoot',$attachmentRoot);
            $offer->save();
        }
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                 
                    if($metatags = $metatagService->saveMetatagsFromArray($offer->get('Metatags'), $values, array('name' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $offer = $offerService->saveOfferFromArray($values);
                   
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-offer', 'offer'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $offerPhotos = array();
        $root = $offer->get('PhotoRoot');
        if ( $root != NULL){
            if(!$root->isInProxyState())
                $offerPhotos = $photoService->getChildrenPhotos($root);
        }
        else{
            $offerPhotos = NULL;
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('offer', $offer);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
        $this->view->assign('offerPhotos', $offerPhotos);
    }
    
    public function removeOfferAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if($offer = $offerService->getOffer($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $metatagService->removeMetatag((int) $offer->getMetatagId());

                $photoRoot = $offer->get('PhotoRoot');
                $photoService->removePhoto($photoRoot);
                
                $offerService->removeOffer($offer);


                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-offer', 'offer'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-offer', 'offer'));
    }
    
    public function addOfferMainPhotoAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$offer = $offerService->getOffer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Offer not found');
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

                    $root = $offer->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], Offer_Model_Doctrine_Offer::getOfferPhotoDimensions(), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], Offer_Model_Doctrine_Offer::getOfferPhotoDimensions(), false);                    
                    }

                    $offer->set('PhotoRoot', $photo);
                    $offer->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $list = '';
        
        $offerPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $offer->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $offerPhotos->add($root);
            $list = $this->view->partial('admin/offer-main-photo.phtml', 'offer', array('photos' => $offerPhotos, 'offer' => $offer));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $offer->getId()
        ));      
    }
    
    public function removeOfferMainPhotoAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$offer = $offerService->getOffer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Offer not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $offer->get('PhotoRoot')) {
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
        
        $offerPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $offer->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $offerPhotos->add($root);
            $list = $this->view->partial('admin/offer-main-photo.phtml', 'offer', array('photos' => $offerPhotos, 'offer' => $offer));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $offer->getId()
        ));
        
    }
    
    public function addOfferPhotoAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        
        $photoDimension = $photoDimensionService->getDimension('offer');
        
        if(!$offer = $offerService->getOffer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Offer not found');
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

                        $root = $offer->get('PhotoRoot');
                        if($root->isInProxyState()) {
                            $root = $photoService->createPhotoRoot();
                            $offer->set('PhotoRoot', $root);
                            $offer->save();
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
        
        $root = $offer->get('PhotoRoot');
        $root->refresh();
        if(!$root->isInProxyState()) {
            $offerPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/offer-photos.phtml', 'offer', array('photos' => $offerPhotos, 'offer' => $offer));
        }
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $offer->getId()
        ));
    }
    
    public function removeOfferPhotoAction() {
        $offerService = $this->_service->getService('Offer_Service_Offer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('photo-id'))) {
            throw new Zend_Controller_Action_Exception('Photo not found');
        }
        
        if(!$offer = $offerService->getOffer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Offer not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            $photoService->removePhoto($photo);
        
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage(), 4);
        }
        
        $root = $offer->get('PhotoRoot');
        $photos = $photoService->getChildrenPhotos($root);
        $list = $this->view->partial('admin/offer-photos.phtml', 'offer', array('photos' => $photos , 'offer' => $offer));
        
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $offer->getId()
        ));
        
    }
    
    public function moveOfferPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        $offerService = $this->_service->getService('Offer_Service_Offer');
        
        if(!$offer = $offerService->getOffer($this->getRequest()->getParam('offer'))) {
            throw new Zend_Controller_Action_Exception('Offer not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Offer photo not found');
        }

        $photoService->movePhoto($photo, $this->getRequest()->getParam('move', 'down'));
        
        $list = '';
        
        $root = $offer->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $offerPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/offer-photos.phtml', 'offer', array('photos' => $offerPhotos, 'offer' => $offer));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $offer->getId()
        ));
    }
    
    
}

