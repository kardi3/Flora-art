<?php

class Media_AdminController extends MF_Controller_Action
{
    public function init() {
        $this->_helper->ajaxContext()
                ->addActionContext('save-attachment', 'json')
                ->addActionContext('delete-session-photo', 'json')
                ->initContext();
        parent::init();
    }
    
    /* attachment - start */
    
    public function listAttachmentDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Media_Model_Doctrine_Attachment');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Media_DataTables_Attachment', 
            'columns' => array('xt.title', 'x.publish_date'),
            'searchFields' => array('x.id','xt.title','x.publish_date')
        ));
        
        $results = $dataTables->getResult();
        $language = $i18nService->getAdminLanguage();
        $languages = $i18nService->getAllLanguages();
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result->id;
            
            $name = "";
            foreach($languages as $lang):
                $name .= "<input lang='".$lang->getId()."' rel='".$result->id."' disabled value='".$result['Translation'][$lang->getId()]['title']."' type='text' /><br />";
            endforeach;
            
            $row[] = $name;
            $row[] = $result->extension;
            
            $options = '<a class="editAttachment" rel="'.$result->id.'" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-attachment', 'media', array('id' => $result->id)) . '"rel="'.$result->id.'" href="' . $this->view->adminUrl('remove-news', 'news', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a disabled rel="'.$result->id.'" class="save" title="' . $this->view->translate('Save') . '"><span class="icon16 wpzoom-floppy"></span></a>';
            
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
    
    public function addAttachmentAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $attachmentService = $this->_service->getService('Media_Service_Attachment'); 
        
        if(!$parent = $attachmentService->getAttachment((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Parent attachment not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $fileForm = new Product_Form_UploadAttachment();
        $fileForm->setDecorators(array('FormElements'));
        $fileForm->removeElement('submit');
        $fileForm->getElement('file')->setValueDisabled(true);
        if($this->getRequest()->isPost()) {
            if($fileForm->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $attachment = $attachmentService->createAttachmentFromUpload($fileForm->getElement('file')->getName(), $fileForm->getValue('file'),$parent,null, $adminLanguage->getId());

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();             
                } catch(Exception $e) {
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
     
            }
        }
        $this->_helper->viewRenderer->setNoRender();  
    }
    
    public function saveAttachmentAction() {
        
        // akcja służy do zapisywania tytułów JSONem (przykład znajduje się w edit-news)
        // tytuły są przesyłane w zmiennych nazwanych językiem tytułu (przykładowo tytuł polski jest w zmiennej pl, angielski w zmiennej en)
        $params = $this->getRequest()->getParams();
        
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $attachmentService = $this->_service->getService('Media_Service_Attachment'); 
        
        $languages = $i18nService->getLanguageList();
        
        $values['id'] = (int) $this->getRequest()->getParam('id');
        foreach($languages as $lang):
            $values['translations'][$lang]['title'] = $params[$lang];
        endforeach;
        
        $attachmentService->saveAttachmentFromArray($values);
        $this->_helper->viewRenderer->setNoRender();  
        
        $this->_helper->json(array(
            'status' => 'success'
        ));      
    }
    
     public function removeAttachmentAction() {
        $attachmentService = $this->_service->getService('Media_Service_Attachment');
        
        if($attachment = $attachmentService->getAttachment($this->getRequest()->getParam('id'))){
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $attachmentService->removeAttachment($attachment);

                $this->_service->get('doctrine')->getCurrentConnection()->commit();
            } catch(Exception $e) {
               $this->_service->get('doctrine')->getCurrentConnection()->rollback();
               $this->_service->get('log')->log($e->getMessage(), 4);
            }
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'id' => $attachment->getId()
        ));
        
    }
    
    /* attachment - end */
    
    public function cropPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        $photosDir = $photoService->photosDir;
        
        $data = Zend_Json::decode($this->getRequest()->getParam('data'));
        $id = $data['id'];
        $filename = $data['filename'];
        $offset = $data['offset'];
        $watermark = isset($data['watermark']) ? 1 : 0;
        $data = $data['data'];
        
        $response = array('status' => 'error');
        
        $offsetDir = realpath($photosDir . DIRECTORY_SEPARATOR . $offset);
        
        $cropData = array();
        foreach($data as $item) {
            $cat = $item['cat'];
            $filePath = $offsetDir . DIRECTORY_SEPARATOR . $filename;
            $x = $item['x'];
            $y = $item['y'];
            $x2 = $item['x2'];
            $y2 = $item['y2'];
            $w = $item['w'];
            $h = $item['h'];
            $cropData[$cat] = array('x' => $x, 'y' => $y, 'x2' => $x2, 'y2' => $y2, 'w' => $w, 'h' => $h);
            
            $dimensions = preg_match('/(\d*)x(\d*)/', $cat, $match);
            $width = (0 == (int) $match[1]) ? null : (int) $match[1];
            $height = (0 == (int) $match[2]) ? null : (int) $match[2];

            if(!file_exists($filePath)) {
                $this->_helper->json($response);
            }

            if(!is_dir($offsetDir . DIRECTORY_SEPARATOR . $cat)) {
                @mkdir($offsetDir . DIRECTORY_SEPARATOR . $cat);
            }
            
            $photoService->crop($filePath, $offsetDir . DIRECTORY_SEPARATOR . $cat . DIRECTORY_SEPARATOR . $filename, $x, $y, $x2, $y2, $width, $height);
            if($watermark)
                $photoService->addWatermark($offsetDir . DIRECTORY_SEPARATOR . $cat . DIRECTORY_SEPARATOR . $filename, APPLICATION_PATH . '/../data/watermark.png');
            $response['status'] = 'success';
        }
        
        if(!empty($cropData)) {
            if($photo = $photoService->getPhoto($id)) {
                $currentCropData = ($photo->getCropData()) ? $photo->getCropData() : array();
                $photo->setCropData(array_merge($currentCropData, $cropData));
                $photo->save();
            }
        }
        
        $this->_helper->json($response);
    }
    
    /**
     * elfinder 2 connect action 
     */
    public function elfinderAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        include_once APPLICATION_PATH .'/../library/elFinder/php/elFinderConnector.class.php';
        include_once APPLICATION_PATH .'/../library/elFinder/php/elFinder.class.php';
        include_once APPLICATION_PATH .'/../library/elFinder/php/elFinderVolumeDriver.class.php';
        include_once APPLICATION_PATH .'/../library/elFinder/php/elFinderVolumeLocalFileSystem.class.php';
        
        $mediaDir = $this->getFrontController()->getParam('bootstrap')->getOption('mediaDir');
        $elfinderUrl = $this->getFrontController()->getParam('bootstrap')->getOption('elfinderUrl');

        $opts = array(
            // 'debug' => true,
            'roots' => array(
                array(
                    'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
                    'path'          => $mediaDir . '/elfinder/',
                    'URL'           => $elfinderUrl, // URL to files (REQUIRED)
//                    'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
                )
            )
        );

        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
        $connector->output();
    }
    
    /**
     * elfinder connect action for review edition
     */
    public function connectAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $options = $this->getInvokeArg('bootstrap')->getContainer()->get('elfinder');
        
        $elFinder = new elFinder($options);
        $elFinder->run();
    }
    
    /**
     * TinyMCE elfinder client
     */
    public function tinymceAction() {
        
    }   
    
    /**
     * elfinder client
     */
    public function clientAction() {
        $this->_helper->layout()->disableLayout();
    }    
    
    /**
     * elfinder client
     */
    public function client2Action() {
        $this->_helper->layout()->disableLayout();
    }    
    
    
    /* uploader by Tomasz Kardas */ 
    
     public function saveSessionPhotoAction(){
        $photoSession = new Zend_Session_Namespace('photo');
        foreach($_FILES['uploader']['tmp_name'] as $key => $fileName):
            $name = $_FILES['uploader']['name'][$key];
            $nameFinish = "";
            if(isset($photoSession->files[$name.$nameFinish])){
                $key = 0;
                while(isset($photoSession->files[$name.$nameFinish])){
                    $key++;
                    $nameFinish = "-".$key;
                }
            }
             $photoSession->files[$name.$nameFinish] = file_get_contents($fileName);
        endforeach;
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    }
    
    public function saveRootPhotosAction(){
        $photoService = $this->_service->getService('Media_Service_Photo');
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        
        $photoDimension = $photoDimensionService->getDimension($this->getRequest()->getParam('photo-dimension'));
        
        $root = $photoService->getPhoto($this->getRequest()->getParam('root-id'));
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        if(!array_key_exists('domain', $options)) {
            throw new Zend_Controller_Action_Exception('Domain string not set');
        }
        
        
        foreach($_FILES['uploader']['tmp_name'] as $key => $href) {
                $name = $_FILES['uploader']['name'][$key];
                $file = file_get_contents($href);
                    try {
                        $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                        $photoService->savePhotoFileFromUpload($file,$name,$root,$photoDimension);

                       $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    } catch(Exception $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->_service->get('Logger')->log($e->getMessage(), 4);
                    }
                
            }
        
        $list = '';
        $root->refresh();
        if(!$root->isInProxyState()) {
            $photos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/photos.phtml', 'media', array('photos' => $photos));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list
        ));
        
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    }
    
    
    
    public function deleteSessionPhotoAction(){
        $photoSession = new Zend_Session_Namespace('photo');
        
        $photoPosition = $this->getRequest()->getParam('position');
        
        
        $keys = array_keys($photoSession->files);
        unset($photoSession->files[$keys[$photoPosition]]);
        
       return $this->_helper->json(array(
            'status' => 'success'
            
        ));
        
    }
    
    public function moveSessionPhotoAction(){
        $photoSession = new Zend_Session_Namespace('photo');
        $position = $this->getRequest()->getParam('position');
        $direction = $this->getRequest()->getParam('direction');
        if($direction == "up"):
            $newDirection = $position + 1;
        else:
            $newDirection = $position - 1;
        endif;
        $fileArray = array();
        foreach($photoSession->files as $key=>$files):
            $fileArray[] = $key;
        endforeach;
        $fileArray['temp'] = $fileArray[$newDirection];
        $fileArray[$newDirection] = $fileArray[$position];
        $fileArray[$position] = $fileArray['temp'];
        unset($fileArray['temp']);
        $resultArray = array();
        
        foreach($fileArray as $key2=>$value2):
            $resultArray[$value2] = $photoSession->files[$value2];
        endforeach;
        $photoSession->files = $resultArray;
        
       return $this->_helper->json(array(
            'status' => 'success'
            
        ));
        
    }
    
     public function movePhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Product photo not found');
        }

        $photoService->movePhoto($photo, $this->getRequest()->getParam('move', 'down'));
        
        $list = '';
        
        $root = $photo->getNode()->getParent();
        if(!$root->isInProxyState()) {
            $productPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/photo-list.phtml', 'media', array('photos' => $productPhotos,'elem_id' => $this->getRequest()->getParam('elem_id'),'actionName' => $this->getRequest()->getParam('actionName'),'moduleName' => $this->getRequest()->getParam('moduleName')));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
        ));
    }
    
    public function removePhotoAction() {   
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$photo = $photoService->getPhoto($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Photo not found');
        }
        
        $root = $photo->getNode()->getParent();
        
        try {
            $photoService->removePhoto($photo);
        } catch(Exception $e) {
            $this->_service->get('Logger')->log($e->getMessage(), 4);
        }
              
        $list = '';
        if(!$root->isInProxyState()) {
            $productPhotos = $photoService->getChildrenPhotos($root);
            $list = $this->view->partial('admin/photo-list.phtml', 'media', array('photos' => $productPhotos,'elem_id' => $this->getRequest()->getParam('elem_id'),'actionName' => $this->getRequest()->getParam('actionName'),'moduleName' => $this->getRequest()->getParam('moduleName')));
        }   
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
        ));
    }
    
     public function addMainPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$root = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Root not found');
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

                    $elem = ucfirst($this->getRequest()->getParam('elem'));
                    $dimensionsActionName = "get".$elem."PhotoDimensions";
                    
                     if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Media_Model_Doctrine_Photo::$dimensionsActionName()), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);       
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], array_keys(Media_Model_Doctrine_Photo::$dimensionsActionName()), false);                    
                    }
                    

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        
       
        $root->refresh();
        $list = $this->view->partial('admin/main-photo.phtml', 'media', array('photos' => $root,'elem_id' => $this->getRequest()->getParam('elem_id'),'actionName' => $this->getRequest()->getParam('actionName'),'moduleName' => $this->getRequest()->getParam('moduleName')));
        
        $this->_helper->json(array(
            'status' => 'success',
            'elem_id' => $this->getRequest()->getParam('elem_id'),
            'body' => $list,
        ));
        
    }
     public function addPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$root = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Root not found');
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

                        $elem = ucfirst($this->getRequest()->getParam('elem'));
                        $dimensionsActionName = "get".$elem."PhotoDimensions";
                        
                       $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Media_Model_Doctrine_Photo::$dimensionsActionName()), $root, true);

                       $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    } catch(Exception $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->_service->get('Logger')->log($e->getMessage(), 4);
                    }
                }
            }
        }

        
       
        $root->refresh();
        $photos = $photoService->getChildrenPhotos($root);
        $list = $this->view->partial('admin/photo-list.phtml', 'media', array('photos' => $photos,'elem_id' => $this->getRequest()->getParam('elem_id'),'actionName' => $this->getRequest()->getParam('actionName'),'moduleName' => $this->getRequest()->getParam('moduleName')));
       
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
        ));
        
    }
    
    public function editPhotoAction() {
        
            
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            $this->view->messages()->add($translator->translate('First you have to choose picture'), 'error');
        }
        
        $actionName = $this->getRequest()->getParam('actionName');
        $moduleName = $this->getRequest()->getParam('moduleName');
        $elem_id  = $this->getRequest()->getParam('elem_id');
        $elemDim = ucfirst($actionName);
        $dimensionsActionName = "get".$elemDim."PhotoDimensions";
        
        $form = $photoService->getPhotoForm($photo);
        
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-'.$actionName.'-photo', $moduleName, array('id' => $elem_id, 'photo' => $photo->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-'.$actionName, $moduleName, array('id' => $elem_id)));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Media_Model_Doctrine_Photo::$dimensionsActionName());
        $this->view->assign('form', $form);
        $this->view->assign('actionName', $actionName);
        $this->view->assign('moduleName', $moduleName);
        $this->view->assign('elem_id', $elem_id);
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
    
    public function removeMainPhotoAction() {
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$root = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Root not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                if($root && !$root->isInProxyState()) {
                    $photo = $photoService->updatePhoto($root);
                    $photo->setOffset(null);
                    $photo->setFilename(null);
                    $photo->setTitle(null);
                    $photo->save();
                }
        
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage(), 4);
        }
        
        $root->refresh();
        $list = $this->view->partial('admin/main-photo.phtml', 'media', array('photos' => $root));
        
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
        ));
        
    }
}
