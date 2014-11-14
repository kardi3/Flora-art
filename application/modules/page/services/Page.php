<?php

/**
 * Page
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Page_Service_Page extends MF_Service_ServiceAbstract {
    
    protected $pageTable;
    
    public function init() {
        $this->pageTable = Doctrine_Core::getTable('Page_Model_Doctrine_Page');
    }
    
    public function getPage($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->pageTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function fetchPage($type, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $serviceBroker = $this->getServiceBroker();
        $translator = $serviceBroker->get('translate');
        
        $pageTypes = Page_Model_Doctrine_Page::getAvailableTypes();
        
        if(!$page = $this->getPage($type, 'type', $hydrationMode)) {
            $page = $this->pageTable->getRecord();
            $page->Translation[$language]->name = $translator->translate($pageTypes[$type], $language);
            $page->Translation[$language]->slug = MF_Text::createSlug($page->Translation[$language]->name);
            $page->setType($type);
            $page->save();
        }
        return $page;
    }
    
    public function getI18nPage($id, $field = 'id', $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->pageTable->getFullPageQuery();
        switch($field) {
            case 'slug':
            case 'name':
                $q->andWhere('t.' . $field . ' = ?', $id);
                break;
            default:
                $q->andWhere('p.' . $field . ' = ?', $id);
        }
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getAllTypePages($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->pageTable->getFullPageQuery();
        $q->addWhere('p.type IS NOT NULL');
	$q->addWhere('t.name IS NOT NULL');
	$q->orderBy('t.name ASC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllPages($count = false) {
        if(!$count)
            return $this->pageTable->findAll();
        else
            return $this->pageTable->count();
    }
    
    public function getPageSelectOptions($language, $prependEmptyValue = false, $idPrefix = '') {
        $pages = $this->getAllPages();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = null;
        }
        foreach($pages as $page) {
            $result[$idPrefix . $page->getId()] = $page->get('Translation')->get($language)->name;
        }
        return $result;
    }
    
    public function getAllPageRoutes() {
        $pages = $this->getAllTypePages(Doctrine_Core::HYDRATE_ARRAY);
        $result = array();
        foreach($pages as $page) {
            $result['domain-i18n:page_'.$page['Translation']['pl']['slug']] = $page['Translation']['pl']['name'];
        }
        return $result;
    }
    
    public function getPageForm(Page_Model_Doctrine_Page $page = null) {
        $form = new Page_Form_Page();
        if(null !== $page) {
            $form->populate($page->toArray());
        }
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            $i18nSubform = $form->translations->getSubForm($language);
            if($i18nSubform) {
                $i18nSubform->getElement('name')->setValue($page->Translation[$language]->name);
                $i18nSubform->getElement('description')->setValue($page->Translation[$language]->description);
            }
        }
        return $form;
    }
    
    public function savePageFromArray(array $values) {
        $serviceBroker = $this->getServiceBroker();
        $translator = $serviceBroker->get('translate');
        
        $types = Page_Model_Doctrine_Page::getAvailableTypes();

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
                
        if($values['type']) {
            if(!$page = $this->getPage($values['type'], 'type')) {
                if(!$page = $this->pageTable->getProxy($values['id'])) {
                    $page = $this->pageTable->getRecord();
                }
            }
        } else {
            if(!$page = $this->pageTable->getProxy($values['id'])) {
                $page = $this->pageTable->getRecord();
            }
        }
        
        $page->fromArray($values);
        foreach($values['translations'] as $language => $translation) {
           // echo $language;
//            if($values['type']) {
//                $page->Translation[$language]->name = $translator->translate($types[$values['type']]);
//            } else {
//                $page->Translation[$language]->name = $translation['name'];
//            }
            $page->Translation[$language]->name = $translation['name'];
            $page->Translation[$language]->slug = MF_Text::createSlug($values['translations'][$language]['name']);
            $page->Translation[$language]->description = $translation['description'];
        }
        $page->save();
        return $page;
    }
    
    public function removePage(Page_Model_Doctrine_Page $page) {
        $page->get('Translation')->delete();
        $page->delete();
    }
    
}

