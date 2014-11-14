<?php

/**
 * ProductController
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Product_AdminController extends MF_Controller_Action {
    
    public function init() {
        $this->_helper->ajaxContext()
                ->addActionContext('move-category', 'json')
                ->addActionContext('move-product-photo', 'json')
                ->addActionContext('remove-category', 'json')
                ->addActionContext('remove-attachment', 'json')
                ->initContext();
        parent::init();
    }
    
    /* category start */
    
    public function listCategoryAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $categoryService = $this->_service->getService('Product_Service_Category');
        
        $adminLanguage = $i18nService->getAdminLanguage();
           
        if(!$categoryRoot = $categoryService->getCategoryRoot()) {
            $languages = array('en' => 'Categories', 'pl' => 'Kategorie', 'de' => 'Kategorien');
            $categoryService->createCategoryRoot($languages);
        }

        if(!$parent = $categoryService->getCategory($this->getRequest()->getParam('id', 0))) {
            $parent = $categoryService->getCategoryRoot();
        }

        $categoryTree = $categoryService->getCategoryTree();
           
        if($current = $this->view->admincontainer->findOneBy('id', 'category')) {
            $current->setActive(true);
        }
        
        $this->view->assign('adminLanguage', $adminLanguage->getId());
        $this->view->assign('parent', $parent);
        $this->view->assign('categoryTree', $categoryTree);
    }
    
    public function listCategoryDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Product_Model_Doctrine_Category');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Product_DataTables_Category', 
            'columns' => array('t.name'),
            'searchFields' => array('t.name')
        ));
        $results = $dataTables->getResult();
        
        $language = $i18nService->getAdminLanguage();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result->id;
            $row[] = $result->Translation[$language->getId()]->name;
          
            $options = '<a href="' . $this->view->adminUrl('edit-category', 'product', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-category', 'product', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getTotal(),
            "iTotalDisplayRecords" => $dataTables->getDisplayTotal(),
            "aaData" => $rows
        );

        $this->_helper->json($response);
    }
    
    public function addCategoryAction() {
        $this->view->messages()->clean();
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $categoryService = $this->_service->getService('Product_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $translator = $this->_service->get('translate');
        
        $languages = $i18nService->getLanguageList();
	
        $adminLanguage = $i18nService->getAdminLanguage();
        $form = $categoryService->getCategoryForm();
        
        if(!$parent = $categoryService->getCategory($this->getRequest()->getParam('id', 0))) {
            $parent = $categoryService->getCategoryRoot();
        }
	
        $form->getElement('parent_id')->setValue($parent->getId());
        
        $form->translations->getSubForm($adminLanguage->getId())->getElement('name')->setRequired();
        
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        $form->setAction($this->view->adminUrl('add-category', 'product'));
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }

                    $category = $categoryService->saveCategoryFromArray($values);
                                        
                    $root = $photoService->createPhotoRoot();
                    $category->set('PhotoRoot', $root);
                    $category->save();

                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-category', 'product', array('id' => $category->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
        $this->view->assign('parentId', $parent->getId());
    }
    
    public function listSubCategoryAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $categoryService = $this->_service->getService('Product_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Category not found');
        }
        
        $form = $categoryService->getCategoryForm($category);

        $metatagsForm = $metatagService->getMetatagsSubForm($category->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray($category->get('Metatags'), $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $category = $categoryService->saveCategoryFromArray($values);

                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-category', 'product', array('id' => $category->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        if(!$categoryRoot = $categoryService->getCategoryRoot()) {
            $languages = array('en' => 'Categories', 'pl' => 'Kategorie');
            $categoryService->createCategoryRoot($languages);
        }
     
        $categoryTree = $categoryService->getCategoryTree();
        
        if($current = $this->view->admincontainer->findOneBy('id', 'list-sub-category')) {
            $current->setLabel($translator->translate($current->getLabel()) . ' ' . $category->Translation[$adminLanguage]->name);
            $current->setActive(true);
            $this->view->assign('adminTitle', $current->getLabel());
        }
        
        $this->view->assign('adminLanguage', $adminLanguage->getId());
        $this->view->assign('parent', $category);
        $this->view->assign('categoryTree', $categoryTree);
        $this->view->assign('form', $form);
    }
    
    public function editCategoryAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $categoryService = $this->_service->getService('Product_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Category not found');
        }
        
        $form = $categoryService->getCategoryForm($category);
        $form->translations->getSubForm($adminLanguage->getId())->getElement('name')->setRequired();
        
        $metatagsForm = $metatagService->getMetatagsSubForm($category->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray($category->get('Metatags'), $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $category = $categoryService->saveCategoryFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $parentId = $category->getNode()->getParent() ? $category->getNode()->getParent()->getId() : null;

                    if ($parentId == 1):
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'product', array('id' => $parentId)));
                    else:
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-sub-category', 'product', array('id' => $parentId)));
                    endif;
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        $categoryPhotos = array();
        $root = $category->get('PhotoRoot');
        if ($root != NULL){
            if(!$root->isInProxyState())
                $categoryPhotos = $photoService->getChildrenPhotos($root);
        }
        else{
            $categoryPhotos = NULL;
        }
        
        $parentId = $category->getNode()->getParent() ? $category->getNode()->getParent()->getId() : null;
        
        if($current = $this->view->admincontainer->findOneBy('id', 'edit-category')) {
            $current->setLabel($translator->translate($current->getLabel()) . ' ' . $category->Translation[$adminLanguage]->name);
            $current->setActive(true);
            $this->view->assign('adminTitle', $current->getLabel());
        }

        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('category', $category);
        $this->view->assign('form', $form);
        $this->view->assign('parentId', $parentId);
        $this->view->assign('categoryPhotos', $categoryPhotos);
    }
  
    public function moveCategoryAction() {

        $categoryService = new Product_Service_Category();
        
        $this->view->clearVars();

        $category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'));
        $status = 'success';
        
        $dest = $categoryService->getCategory((int) $this->getRequest()->getParam('dest_id'));
  
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

            $categoryService->moveCategory($category, $dest, $this->getRequest()->getParam('mode', 'after'));

            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage());
            $status= 'error';
        }
        
        $this->_helper->viewRenderer->setNoRender();
        
        $this->view->assign('status', $status);
    }
    
    public function removeCategoryAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $photoService = $this->_service->getService('Media_Service_Photo');
     
        $this->view->clearVars();
        
        $status = 'success';
        
        if($category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $category['Translation']->delete();
                $category->delete();
                
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
               
		$this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'product'));
                    
            } catch(Exception $e) {
		var_dump($e->getMessage());exit;
                $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                $this->_service->get('log')->log($e->getMessage());
                $status = 'error';
            }
        }
        
        $this->_helper->viewRenderer->setNoRender();
        
        $this->view->assign('status', $status);
    }
        
    public function removeCategoryPhotoAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Category not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $category->get('PhotoRoot')) {
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
        
        $list = $this->view->partial('admin/category-main-logo.phtml', 'product', array('photos' => null, 'category' => $category));
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $category->getId()
        ));
    }
    
    
    public function addCategoryPhotoAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        
        if(!$category = $categoryService->getCategory((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Category not found');
        }
  
        $photoDimension = $photoDimensionService->getDimension('category');
        
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

                    $root = $category->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], $photoDimension, false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], $photoDimension, false);                    
                    }

                    $category->set('PhotoRoot', $photo);
                    $category->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $list = '';
        
        $categoryPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $category->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $categoryPhotos->add($root);
            $list = $this->view->partial('admin/category-main-logo.phtml', 'product', array('photos' => $categoryPhotos, 'category' => $category));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $category->getId()
        ));      
    }
    
    
    /* category end */
    
    /* producer start */
    
    public function listProducerAction() {
        
    }
    
    public function listProducerDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Product_Model_Doctrine_Producer');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Product_DataTables_Producer', 
            'columns' => array('t.name', 'x.email', 'x.phone','x.website','x.address'),
            'searchFields' => array('t.name', 'x.email', 'x.phone','x.website','x.address')
        ));
        
        $language = $i18nService->getAdminLanguage();
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result->Translation[$language->getId()]->name;
            $row[] = $result['email'];
            $row[] = $result['phone'];
            $row[] = $result['website'];
            $row[] = $result['address'];
            
            $options = '<a href="' . $this->view->adminUrl('edit-producer', 'product', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
            $options .= '<a href="' . $this->view->adminUrl('remove-producer', 'product', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addProducerAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        $this->view->messages()->clean();
        $form = $producerService->getProducerForm();
        
        $form->translations->getSubForm($adminLanguage->getId())->name->setRequired();
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
     
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try{
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $producer = $producerService->saveProducerFromArray($values); 
                    
                    $root = $producer->get('PhotoRoot');
                    if($root->isInProxyState()) {
                        $root = $photoService->createPhotoRoot();
                        $producer->set('PhotoRoot', $root);
                    }
                    
                    $producer->save();
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-producer', 'product', array('id' => $producer->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }              
            }
        }       
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);   
    }
    
    public function editProducerAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');

        if(!$producer = $producerService->getFullProducer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
        }
        $form = $producerService->getProducerForm($producer);
        
        $form->translations->getSubForm($adminLanguage->getId())->name->setRequired();
        
        $metatagsForm = $metatagService->getMetatagsSubForm($producer->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
                
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    if($metatags = $metatagService->saveMetatagsFromArray($producer->get('Metatags'), $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $producerService->saveProducerFromArray($values);
                   
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-producer', 'product'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
        $producerPhotos = array();
        $root = $producer->get('PhotoRoot');
        if ( $root != NULL){
            if(!$root->isInProxyState())
                $producerPhotos = $photoService->getChildrenPhotos($root);
        }
        else{
            $producerPhotos = NULL;
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
        $this->view->assign('producer', $producer);
        $this->view->assign('producerPhotos', $producerPhotos);
    } 
    
    public function removeProducerAction() {
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');

        if($producer = $producerService->getProducer($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $metatag = $metatagService->getMetatag((int) $producer->getMetatagId());
                $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $producer->getMetatagId());
                           
                $photoRoot = $producer->get('PhotoRoot');
                $photoService->removeGallery($photoRoot);
     
                $producerService->removeProducer($producer);
                
                $metatagService->removeMetatag($metatag);
                $metatagTranslationService->removeMetatagTranslation($metatagTranslation);
                
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-producer', 'product'));
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-producer', 'product'));
    }
      
     
    public function addProducerMainPhotoAction() {
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        
        $photoDimension = $photoDimensionService->getDimension('producer');
        
        if(!$producer = $producerService->getProducer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
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

                    $root = $producer->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], $photoDimension, false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], $photoDimension, false);                    
                    }

                    $producer->set('PhotoRoot', $photo);
                    $producer->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $list = '';
        
        $producerPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $producer->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $producerPhotos->add($root);
            $list = $this->view->partial('admin/producer-main-photo.phtml', 'product', array('photos' => $producerPhotos, 'producer' => $producer));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $producer->getId()
        ));      
    }

   
    public function removeProducerMainPhotoAction() {
        $producerService = $this->_service->getService('Product_Service_Producer');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$producer = $producerService->getProducer((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Producer not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $producer->get('PhotoRoot')) {
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
        
        $producerPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $producer->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $producerPhotos->add($root);
            $list = $this->view->partial('admin/producer-main-photo.phtml', 'product', array('photos' => $producerPhotos, 'product' => $producer));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $producer->getId()
        ));
        
    }
    
    /* producer end */
    
    /* product start */
    
    public function listProductAction() {
        
    }
    
    public function listProductDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Product_Model_Doctrine_Product');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Product_DataTables_Product', 
            'columns' => array('x.id','t.name', 'ct.name'),
            'searchFields' => array('x.id','t.name', 'ct.name')
        ));
        
        $results = $dataTables->getResult();

        $language = $i18nService->getAdminLanguage();

        $rows = array();
        foreach($results as $result) {
                    $row = array();
		    $row[] = $result->id;
                    $row[] = $result->Translation[$language->getId()]->name;
                    $row[] = $result['Category']->Translation[$language->getId()]->name;
                    
                    
                    $options = '<a href="' . $this->view->adminUrl('edit-product', 'product', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
                    $options .= '<a href="' . $this->view->adminUrl('remove-product', 'product', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
   
    public function addProductAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $productService = $this->_service->getService('Product_Service_Product');
        $categoryService = $this->_service->getService('Product_Service_Category');
        $subscriberService = $this->_service->getService('Newsletter_Service_Subscriber');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $adminLanguage = $i18nService->getAdminLanguage();
          
        $translator = $this->_service->get('translate');
        
        $form = $productService->getProductForm();
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
       // $form->getElement('producer_id')->setMultiOptions($producerService->getTargetProducerSelectOptions(true, $adminLanguage->getId()));
        $form->getElement('category_id')->setMultiOptions($categoryService->getTargetCategorySelectOptions(null, $adminLanguage->getId()));
        
        $form->translations->getSubForm($adminLanguage->getId())->name->setRequired();
        
        $form->removeElement('price');
        $form->removeElement('code');
        $form->removeElement('dimensions');
        $form->removeElement('availability');
        $form->removeElement('youtube');
        $form->removeElement('promotion_price');
        $form->removeElement('discount_id');
        $form->removeElement('most_frequently_purchased');
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try{
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                 
                    $product = $productService->saveProductFromArray($values);
                                        
                    $subscriberService->saveProductSubscribers($product);
                    
                    $root = $product->get('PhotoRoot');
                    if($root->isInProxyState()) {
                      
                        $root = $photoService->createPhotoRoot();
                        $product->set('PhotoRoot', $root);
                        $product->save();
                    }
                    
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product', 'product', array('id' => $product->getId())));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }              
            }
            else{
                $form->populate($_POST);
            }
        }     
        
        
        if(!$categoryRoot = $categoryService->getCategoryRoot()) {
            $languages = array('en' => 'Categories', 'pl' => 'Kategorie');
            $categoryService->createCategoryRoot($languages);
        }

        if(!$parent = $categoryService->getCategory($this->getRequest()->getParam('id', 0))) {
            $parent = $categoryService->getCategoryRoot();
        }

        $categoryTree = $categoryService->getCategoryTree();
           
        $languages = $i18nService->getLanguageList();
        
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('parent', $parent);
        $this->view->assign('categoryTree', $categoryTree);
        $this->view->assign('form', $form);       
    }
  
    public function editProductAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $producerService = $this->_service->getService('Product_Service_Producer');
        $categoryService = $this->_service->getService('Product_Service_Category');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $translator = $this->_service->get('translate');
        
        if(!$product = $productService->getFullProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        $form = $productService->getProductForm($product);
        $metatagsForm = $metatagService->getMetatagsSubForm($product->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        $form->translations->getSubForm($adminLanguage->getId())->name->setRequired();
        
       // $form->getElement('producer_id')->setMultiOptions($producerService->getTargetProducerSelectOptions(true, $adminLanguage->getId()));
        $form->getElement('category_id')->setMultiOptions($categoryService->getTargetCategorySelectOptions(false, $adminLanguage->getId()));
        $form->removeElement('price');
        $form->removeElement('code');
        $form->removeElement('dimensions');
        $form->removeElement('availability');
        $form->removeElement('youtube');
        $form->removeElement('promotion_price');
        $form->removeElement('discount_id');
        $form->removeElement('most_frequently_purchased');
         if(!$product->photo_root_id){
                $root = $photoService->createPhotoRoot();
                $product->set('PhotoRoot',$root);
                $product->save();
            }
       
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                  
                    $values = $form->getValues();  
                    
                    if($metatags = $metatagService->saveMetatagsFromArray($product->get('Metatags'), $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    $product = $productService->saveProductFromArray($values); 
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
            else{
                $form->populate($form->getValues());
            }
        }    
        $productPhotos = array();
        $root = $product->get('PhotoRoot');
        if ( $root != NULL){
            if(!$root->isInProxyState())
                $productPhotos = $photoService->getChildrenPhotos($root);
        }
        else{
            $productPhotos = NULL;
        }
        
        if(!$categoryRoot = $categoryService->getCategoryRoot()) {
            $languages = array('en' => 'Categories', 'pl' => 'Kategorie');
            $categoryService->createCategoryRoot($languages);
        }

        if(!$parent = $categoryService->getCategory($this->getRequest()->getParam('id', 0))) {
            $parent = $categoryService->getCategoryRoot();
        }

        $categoryTree = $categoryService->getCategoryTree();
           
        $languages = $i18nService->getLanguageList();
        
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('parent', $parent);
        $this->view->assign('categoryTree', $categoryTree);
        $this->view->assign('form', $form);
        $this->view->assign('product', $product);
        $this->view->assign('productPhotos', $productPhotos);
    } 
    
    public function removeProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
            
        if($product = $productService->getProduct($this->getRequest()->getParam('id'))) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $metatagService->removeMetatag($product['metatag_id']);
                    $photoRoot = $product->get('PhotoRoot');
                    if(!empty($photoRoot))
                        $photoService->removeGallery($photoRoot);
                    
                    $productService->removeProduct($product);
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
    }
    
    public function addProductPhotoAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        
        $productDimension = $photoDimensionService->getDimension('product');
        $sliderDimension = $photoDimensionService->getDimension('slider');
        $photoDimension = array_unique(array_merge($productDimension,$sliderDimension));
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
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

                        $root = $product->get('PhotoRoot');
                        if($root->isInProxyState()) {
                            $root = $photoService->createPhotoRoot();
                            $product->set('PhotoRoot', $root);
                            $product->save();
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
        
        $root = $product->get('PhotoRoot');
        $root->refresh();
        if(!$root->isInProxyState()) {
            $productPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/product-photos.phtml', 'product', array('photos' => $productPhotos, 'product' => $product));
        }
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $product->getId()
        ));
    }
    
    public function editProductPhotoAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('product-id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-product-photo', 'product', array('product-id' => $product->getId(), 'id' => $photo->getId())));
        
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product-photo', 'product', array('id' => $photo->getId(), 'product-id' => $product->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product', 'product', array('id' => $product->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
          
        $this->view->admincontainer->findOneBy('id', 'cropproductphoto')->setActive();
        $this->view->admincontainer->findOneBy('id', 'editphotoproduct')->setLabel($product->Translation[$adminLanguage->getId()]->name);
        $this->view->admincontainer->findOneBy('id', 'editphotoproduct')->setParam('id', $product->getId());
        $this->view->adminTitle = $this->view->translate($this->view->admincontainer->findOneBy('id', 'cropproductphoto')->getLabel());
        
        $this->view->assign('product', $product);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Product_Model_Doctrine_Product::getProductPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeProductPhotoAction() {   
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('product-id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        if(!$photo = $photoService->getPhoto($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }
        
        $photoService->removePhoto($photo);
            
          
        $list = '';
        
        $root = $product->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $productPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/product-photos.phtml', 'product', array('photos' => $productPhotos, 'product' => $product));
        }   
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $product->getId()
        ));   
        
    }
    
    
    
    public function addProductMainPhotoAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        
        $productDimension = $photoDimensionService->getDimension('productmain');
        $sliderDimension = $photoDimensionService->getDimension('slider');
        $photoDimension = array_unique(array_merge($productDimension,$sliderDimension));
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
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

                    $root = $product->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], $photoDimension, false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], $photoDimension, false);                    
                    }

                    $product->set('PhotoRoot', $photo);
                    $product->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $list = '';
        
        $productPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $product->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $productPhotos->add($root);
            $list = $this->view->partial('admin/product-main-photo.phtml', 'product', array('photos' => $productPhotos, 'product' => $product));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $product->getId()
        ));      
    }

    public function removeProductMainPhotoAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $product->get('PhotoRoot')) {
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
        
        $productPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $product->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $productPhotos->add($root);
            $list = $this->view->partial('admin/product-main-photo.phtml', 'product', array('photos' => $productPhotos, 'product' => $product));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $product->getId()
        ));
        
    }
    
    public function moveProductPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        $productService = $this->_service->getService('Product_Service_Product');
        
        if(!$product = $productService->getProduct($this->getRequest()->getParam('product'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product photo not found');
        }

        $photoService->movePhoto($photo, $this->getRequest()->getParam('move', 'down'));
        
        $list = '';
        
        $root = $product->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $productPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/product-photos.phtml', 'product', array('photos' => $productPhotos, 'product' => $product));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $product->getId()
        ));
    }
    
      public function setNewProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        if($product->getNew()){
            $product->setNew(0);
        }
        else{
            $product->setNew(1);
        }
        $product->save();
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function setFacebookProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $facebookService = $this->_service->getService('User_Service_Facebook');
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        $category = $product['Categories'][0];
        $categoryName = $category->Translation[$this->language]->name;
        
        if($category->getNode()->hasParent()&&$category->getNode()->getParent()->level!=0)
        {
            $categoryName = $category->getNode()->getParent()->Translation[$this->language]->name." ".strtolower($categoryName);
        }
        
        $description = $product->Translation['pl']->name." \n ";
        $description .= "Kategoria - ".$categoryName." \n \n";
        if(strlen($product->Translation['pl']->description)<=200):
            $description .= strip_tags($product->Translation['pl']->description);
        else:
            $description .= mb_substr(strip_tags($product->Translation['pl']->description),0,200,'UTF-8')."...";
        endif;
        
        $photoRoot = $product['PhotoRoot'];
        $photoUrl = "http://dodusmaszyny.pl/media/photos/".$photoRoot['offset']."/".$photoRoot['filename'];
        
        $facebookService->publishNews($product->Translation['pl']->name,$description ,$this->view->url(array('product' => $product['Translation'][$this->language]['slug']), 'domain-i18n:product'),$photoUrl);

        if($product->getFacebook()){
            $product->setFacebook(0);
        }
        else{
            $product->setFacebook(1);
        }
        $product->save();
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function setGooglePlusProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $facebookService = $this->_service->getService('User_Service_GooglePlus');
        echo "ok";exit;
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        $category = $product['Categories'][0];
        $categoryName = $category->Translation[$this->language]->name;
        
        if($category->getNode()->hasParent()&&$category->getNode()->getParent()->level!=0)
        {
            $categoryName = $category->getNode()->getParent()->Translation[$this->language]->name." ".strtolower($categoryName);
        }
        
        $description = $product->Translation['pl']->name." \n ";
        $description .= "Kategoria - ".$categoryName." \n \n";
        if(strlen($product->Translation['pl']->description)<=200):
            $description .= strip_tags($product->Translation['pl']->description);
        else:
            $description .= mb_substr(strip_tags($product->Translation['pl']->description),0,200,'UTF-8')."...";
        endif;
        
        $photoRoot = $product['PhotoRoot'];
        $photoUrl = "http://dodusmaszyny.pl/media/photos/".$photoRoot['offset']."/".$photoRoot['filename'];
        
        $facebookService->publishNews($product->Translation['pl']->name,$description ,$this->view->url(array('product' => $product['Translation'][$this->language]['slug']), 'domain-i18n:product'),$photoUrl);

        if($product->getFacebook()){
            $product->setFacebook(0);
        }
        else{
            $product->setFacebook(1);
        }
        $product->save();
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function setPromotedProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        if($product->getPromoted()){
            $product->setPromoted(0);
        }
        else{
            $product->setPromoted(1);
        }
        $product->save();
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function setPromotionProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        if($product->getPromotion()){
            $product->setPromotion(0);
        }
        else{
            $product->setPromotion(1);
        }
        $product->save();
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
     public function setSliderProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        if($product->getSlider()){
            $product->setSlider(0);
        }
        else{
            $product->setSlider(1);
        }
        $product->save();
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function setSoldProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        if($product->getSold()){
            $product->setSold(0);
        }
        else{
            $product->setSold(1);
        }
        $product->save();
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function setActiveProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        if($product->getActive()){
            $product->setActive(0);
        }
        else{
            $product->setActive(1);
        }
        $product->save();
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-product', 'product'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function editProductMainPhotoAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        
        $productDimension = $photoDimensionService->getDimensionArray('productmain');
        $sliderDimension = $photoDimensionService->getDimensionArray('slider');
        $photoDimension = array_unique(array_merge($productDimension,$sliderDimension));
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$product = $productService->getProduct((int) $this->getRequest()->getParam('product-id'))) {
            throw new Zend_Controller_Action_Exception('Product not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo photo not found');
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-product-photo', 'product', array('product-id' => $product->getId(), 'id' => $photo->getId())));
        
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product-photo-serwis4', 'product', array('id' => $photo->getId(), 'product-id' => $product->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-product-serwis4', 'product', array('id' => $product->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }

        $this->view->admincontainer->findOneBy('id', 'cropproductphoto')->setActive();
        $this->view->admincontainer->findOneBy('id', 'editphotoproduct')->setLabel($product->Translation[$adminLanguage->getId()]->name);
        $this->view->admincontainer->findOneBy('id', 'editphotoproduct')->setParam('id', $product->getId());
        $this->view->adminTitle = $this->view->translate($this->view->admincontainer->findOneBy('id', 'cropproductphoto')->getLabel());  
        $this->view->assign('product', $product);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', $photoDimension);
        $this->view->assign('form', $form);
    }
    
  /* product end */ 
    
}
?>