<?php

/**
 * Media_Service_Attachment
 *
@author Tomasz Kardas <kardi31@o2.pl>
 */
class Media_Service_Attachment extends MF_Service_ServiceAbstract {
    
    protected $attachmentTable;
    
    public $attachmentsDir;
    
    public function init() {
        $bootstrap = $this->getServiceBroker()->get('FrontController')->getParam('bootstrap');
        if(null == $this->attachmentsDir = $bootstrap->getOption('attachmentsDir')) {
            throw new Zend_Controller_Action_Exception('Attachments directory configuration missing');
        }
        $this->attachmentTable = Doctrine_Core::getTable('Media_Model_Doctrine_Attachment');
        parent::init();
    } 
    
    public function getAttachment($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->attachmentTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function createAttachmentRoot() {
        $tree = $this->attachmentTable->getTree();
        $attachment = $this->attachmentTable->getRecord();
        $attachment->save();
        $tree->createRoot($attachment);
        return $attachment;
    }
    
    
    public function getAttachmentForm(Media_Model_Doctrine_Attachment $attachment = null) {
        $form = new Product_Form_Attachment();
        if(null != $attachment) { 
            $form->populate($attachment->toArray());
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('title')->setValue($attachment->Translation[$language]->title);
                    $i18nSubform->getElement('description')->setValue($attachment->Translation[$language]->description);
                }
            }
        }
        return $form;   
    }
    
    public function saveAttachmentFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$attachment = $this->getAttachment((int) $values['id'])) {
            $attachment = $this->attachmentTable->getRecord();
        }
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $attachment->fromArray($values);
        
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['title'])) {
                $attachment->Translation[$language]->title = $values['translations'][$language]['title'];
                $attachment->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Media_Model_Doctrine_AttachmentTranslation', $values['translations'][$language]['title'], $attachment->getId());
                $attachment->Translation[$language]->description = $values['translations'][$language]['description'];
            }
        }
        
        $attachment->save();
        
        return $attachment;
    }
    
    public function createUniquefileName($fileName, $directory = null) {
        if(null == $directory) {
            $directory = $this->attachmentsDir;
        }
        if(is_dir($directory)) {
            $filePath = realpath($directory . DIRECTORY_SEPARATOR . $fileName);
            if(file_exists($filePath)) {
                $pathinfo = pathinfo($filePath);
                $name = $pathinfo['filename'];
                $ext = (isset($pathinfo['extension'])) ? $pathinfo['extension'] : '';
                $suffix = 1;
                $uniqueName = $fileName;
                do {
                    $uniqueName = $name . '-' . $suffix . '.' . $ext;
                    $suffix++;                    
                } while(file_exists($directory . DIRECTORY_SEPARATOR . $uniqueName));
                  
                return $uniqueName;
            }
            return $fileName;
        }
    }
    
    public function getExtension($fileName, $directory = null) {
        if(null == $directory) {
            $directory = $this->attachmentsDir;
        }
        if(is_dir($directory)) {
            $filePath = realpath($directory . DIRECTORY_SEPARATOR . $fileName);
            if(file_exists($filePath)) {
                $pathinfo = pathinfo($filePath);
                $extension = $pathinfo['extension'];
            }
        return $extension;
        }
     }
 
    public function createAttachmentFromUpload($uploadFileIndex, $name, $parent, $title = null, $languageAdmin) {
        $pathinfo = pathinfo($name);
        $extension = $pathinfo['extension'];
        $name = MF_Text::createSlug($name);
        $filename = $this->createUniquefileName($name);
        $title = null == $title ? pathinfo($name, PATHINFO_FILENAME) : $title;
        if(@move_uploaded_file($_FILES[$uploadFileIndex]['tmp_name'], $this->attachmentsDir . DIRECTORY_SEPARATOR . $filename)) {
                $attachment = $this->attachmentTable->getRecord();
                $attachment->setFilename($filename);
                $attachment->Translation[$languageAdmin]->title = $name;
                $attachment->Translation[$languageAdmin]->slug = MF_Text::createUniqueTableSlug('Media_Model_Doctrine_AttachmentTranslation', $name, $attachment->getId());
                $attachment->setExtension($extension);
                $attachment->save();
                
                if(null != $parent) {
                    if(is_integer($parent)) {
                        $parent = $this->attachmentTable->find($parent);
                    } 
                    if($parent instanceof Media_Model_Doctrine_Attachment) {
                        $attachment->getNode()->insertAsLastChildOf($parent);
                    }
                } else {
                    $tree = $this->attachmentTable->getTree();
                    $tree->createRoot($attachment);
                }
                
                return $attachment;
            }
    }
    
    public function removeAttachmentFile($fileName) {
        if(file_exists($this->attachmentsDir . DIRECTORY_SEPARATOR . $fileName)) {
            @unlink($this->attachmentsDir . DIRECTORY_SEPARATOR . $fileName);
        }
        return true;
    }
    
    public function removeAttachment(Media_Model_Doctrine_Attachment $attachment) {
        if($this->removeAttachmentFile($attachment->getFilename())){
            $attachment->delete();
        }
    }
    
    public function removeAllAtachments($productId) {
        $attachments = $this->attachmentTable->findAll();
        foreach($attachments as $attachment):
            if ($attachment['product_id'] == $productId):
                $this->removeAttachment($attachment);
            endif;
        endforeach;
        
    }
}
?>