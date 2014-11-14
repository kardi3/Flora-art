<?php

/**
 * News_AdminController
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class News_AdminController extends MF_Controller_Action {
    
     public function init() {
        $this->_helper->ajaxContext()
                ->addActionContext('move-news-photo', 'json')
                ->initContext();
        parent::init();
    }
    
    public function listNewsAction() {
        
    }
    
    public function listNewsDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('News_Model_Doctrine_News');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'News_DataTables_News', 
            'columns' => array('x.id','xt.name', 'x.publish_date'),
            'searchFields' => array('x.id','xt.name','x.publish_date')
        ));
        
        $results = $dataTables->getResult();
        $language = $i18nService->getAdminLanguage();

        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result->id;
            $row[] = $result->Translation[$language->getId()]->name;
            $row[] = MF_Text::timeFormat($result->publish_date, 'd/m/Y H:i');
            
            $options = '<a href="' . $this->view->adminUrl('edit-news', 'news', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-news', 'news', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addNewsAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $newsService->getNewsForm();
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
        $form->translations->getSubForm($adminLanguage->getId())->getElement('name')->setRequired();
        
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
            
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'name', 'description' => 'description', 'keywords' => 'description'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    $news = $newsService->saveNewsFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-news', 'news', array('id' => $news->getId())));
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
    
    public function editNewsAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $tagService = $this->_service->getService('News_Service_Tag');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $attachmentService = $this->_service->getService('Media_Service_Attachment');
        
        $translator = $this->_service->get('translate');
        
        if(!$news = $newsService->getNews($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News not found');
        }
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $newsService->getNewsForm($news);
        $form->translations->getSubForm($adminLanguage->getId())->getElement('name')->setRequired();
        $form->removeElement('category_id');
	$form->getElement('tag_id')->setMultiOptions($tagService->prependSelectTagsOptions());
	$form->getElement('tag_id')->setValue($news->get('Tags')->getPrimaryKeys());
//	Zend_Debug::dump($news->get('Tags')->toArray());exit;
	
        $metatagsForm = $metatagService->getMetatagsSubForm($news->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
      
        if(!$news->photo_root_id){
            $photoRoot = $photoService->createPhotoRoot();
            $news->set('PhotoRoot',$photoRoot);
            $news->save();
        }
        
        if(!$news->attachment_id){
            $attachmentRoot = $attachmentService->createAttachmentRoot();
            $news->set('AttachmentRoot',$attachmentRoot);
            $news->save();
        }
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                 
                    if($metatags = $metatagService->saveMetatagsFromArray($news->get('Metatags'), $values, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $news = $newsService->saveNewsFromArray($values);
                   
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-news', 'news'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $newsPhotos = array();
        $root = $news->get('PhotoRoot');
        if ( $root != NULL){
            if(!$root->isInProxyState())
                $newsPhotos = $photoService->getChildrenPhotos($root);
        }
        else{
            $newsPhotos = NULL;
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('news', $news);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
        $this->view->assign('newsPhotos', $newsPhotos);
	
    }
    
    public function removeNewsAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if($news = $newsService->getNews($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $metatagService->removeMetatag((int) $news->getMetatagId());

                $photoRoot = $news->get('PhotoRoot');
                $photoService->removePhoto($photoRoot);
                
                $newsService->removeNews($news);


                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-news', 'news'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-news', 'news'));
    }
    
    public function addNewsMainPhotoAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$news = $newsService->getNews((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News not found');
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

                    $root = $news->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], News_Model_Doctrine_News::getNewsPhotoDimensions(), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], News_Model_Doctrine_News::getNewsPhotoDimensions(), false);                    
                    }

                    $news->set('PhotoRoot', $photo);
                    $news->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $list = '';
        
        $newsPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $news->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $newsPhotos->add($root);
            $list = $this->view->partial('admin/news-main-photo.phtml', 'news', array('photos' => $newsPhotos, 'news' => $news));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $news->getId()
        ));      
    }
    
    public function removeNewsMainPhotoAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$news = $newsService->getNews((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $news->get('PhotoRoot')) {
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
        
        $newsPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $news->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $newsPhotos->add($root);
            $list = $this->view->partial('admin/news-main-photo.phtml', 'news', array('photos' => $newsPhotos, 'news' => $news));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $news->getId()
        ));
        
    }
    
    public function addNewsPhotoAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        
        $photoDimension = $photoDimensionService->getDimension('news');
        
        if(!$news = $newsService->getNews((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News not found');
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

                        $root = $news->get('PhotoRoot');
                        if($root->isInProxyState()) {
                            $root = $photoService->createPhotoRoot();
                            $news->set('PhotoRoot', $root);
                            $news->save();
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
        
        $root = $news->get('PhotoRoot');
        $root->refresh();
        if(!$root->isInProxyState()) {
            $newsPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/news-photos.phtml', 'news', array('photos' => $newsPhotos, 'news' => $news));
        }
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $news->getId()
        ));
    }
    
    public function removeNewsPhotoAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('photo-id'))) {
            throw new Zend_Controller_Action_Exception('Photo not found');
        }
        
        if(!$news = $newsService->getNews((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            $photoService->removePhoto($photo);
        
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage(), 4);
        }
        
        $root = $news->get('PhotoRoot');
        $photos = $photoService->getChildrenPhotos($root);
        $list = $this->view->partial('admin/news-photos.phtml', 'news', array('photos' => $photos , 'news' => $news));
        
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $news->getId()
        ));
        
    }
    
    public function moveNewsPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        $newsService = $this->_service->getService('News_Service_News');
        
        if(!$news = $newsService->getNews($this->getRequest()->getParam('news'))) {
            throw new Zend_Controller_Action_Exception('News not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('News photo not found');
        }

        $photoService->movePhoto($photo, $this->getRequest()->getParam('move', 'down'));
        
        $list = '';
        
        $root = $news->get('PhotoRoot');
        if(!$root->isInProxyState()) {
            $newsPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/news-photos.phtml', 'news', array('photos' => $newsPhotos, 'news' => $news));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $news->getId()
        ));
    }
    
    
    /* tags */
    
    public function listTagAction(){}
    
    public function listTagDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('News_Model_Doctrine_Tag');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'News_DataTables_Tag', 
            'columns' => array('x.id','x.name'),
            'searchFields' => array('x.id','x.name')
        ));
        
        $results = $dataTables->getResult();
        $language = $i18nService->getAdminLanguage();

        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result->id;
            $row[] = $result->name;
            
            $options = '<a href="' . $this->view->adminUrl('edit-news', 'news', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-news', 'news', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addTagAction() {
        $tagService = $this->_service->getService('News_Service_Tag');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $tagService->getTagForm();
        
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
                    $tag = $tagService->saveTagFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-tag', 'news'));
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
    
    public function editTagAction() {
        $tagService = $this->_service->getService('News_Service_Tag');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $translator = $this->_service->get('translate');
        
        if(!$tag = $tagService->getTag($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Tag not found');
        }
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $tagService->getTagForm($tag);
       
        $metatagsForm = $metatagService->getMetatagsSubForm($tag->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
      
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    if($metatags = $metatagService->saveMetatagsFromArray($tag->get('Metatags'), $values, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $tag = $tagService->saveTagFromArray($values);
                   
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-tag', 'news'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
       
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('tag', $tag);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
	
    }
    
    public function removeTagAction() {
        $tagService = $this->_service->getService('News_Service_Tag');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if($tag = $tagService->getTag($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $metatagService->removeMetatag((int) $tag->getMetatagId());

                $photoRoot = $tag->get('PhotoRoot');
                $photoService->removePhoto($photoRoot);
                
                $tagService->removeTag($tag);


                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-news', 'news'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-news', 'news'));
    }
    
}

