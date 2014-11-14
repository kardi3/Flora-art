<?php

/**
 * Menu
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Menu_Service_Footer extends MF_Service_ServiceAbstract {
    
    protected $footerTable;
    protected $footerItemTable;
    protected $availablePages = array(
      'home' => 'domain-homepage',
      'products' => 'domain-i18n:products',
      'pages' => 'domain-i18n:page',
      'news' => 'domain-i18n:news',
      'contact' => 'domain-contact',
      'collections' => 'domain-collection-list',
      'categories' => 'domain-categories',
      'register' => 'domain-i18n:register',
      'login' => 'domain-login'
    );
    
    public function init() {
        $this->footerTable = Doctrine_Core::getTable('Menu_Model_Doctrine_Footer');
        $this->footerItemTable = Doctrine_Core::getTable('Menu_Model_Doctrine_FooterItem');
    }
    
    public function getFooter($id, $field = 'id') {
        return $this->footerTable->findOneBy($field, $id);
    }
    
    public function getAllFooters() {
        return $this->footerTable->findAll();
    }
    public function getAvailableRoutes() {
        foreach($this->availablePages as $key=>$value):
            $pages[$value] = $key;
        endforeach;
        return $pages;
    }
    public function getFooterItem($id, $field = 'id') {
        return $this->footerItemTable->findOneBy($field, $id);
    }
    public function fetchMainFooter($limit = 10, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->footerItemTable->getFooterMainItemsQuery(1);
        $q->limit($limit);
        $banners = $q->execute(array(), $hydrationMode);
        //$this->incrementViews($banners);
        return $banners;
    }
    public function fetchSubFooter($od,$do,$limit = 10, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->footerItemTable->getSubFooterItemsQuery($od,$do);
    
        $q->limit($limit);
        $banners = $q->execute(array(), $hydrationMode);
        return $banners;
    }
    public function fetchFooter($limit = 10, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->footerItemTable->getFooterItemQuery();
        $q->limit($limit);
        $banners = $q->execute(array(), $hydrationMode);
        //$this->incrementViews($banners);
        return $banners;
    }
    public function findFooterItemByUniqueId($uniqueId) {
        return $this->getFooterItem($uniqueId, 'unique_id');
    }
    
    public function findFooterItemByTargetType($targetType) {
        return $this->getFooterItem($targetType, 'target_type');
    }
    
    public function findFooterItemByTargetTypeAndTargetId($targetType, $targetId) {
        return $this->footerItemTable->findByDql('target_type = ? AND target_id = ?', array($targetType, $targetId))->getFirst();
    }
    
    public function getFooterItemTree($footer, $language, $deep = false, $hydrationMode = Doctrine_Core::HYDRATE_RECORD_HIERARCHY) {
        $root = $footer->get('FooterItemRoot');
        if(false == $deep) {
            return $this->footerItemTable->getTree()->fetchBranch($root->getId(), array(), $hydrationMode);
        } else {    
            $q = $this->footerItemTable->createQuery('i')
                ->select('i.*')
                ->addSelect('it.*')
                ->addSelect('ip.id')
                ->addSelect('ipt.*')
                ->leftJoin('i.Translation it')
                ->leftJoin("i.Page ip ON i.target_id = ip.id AND i.target_type = 'page'")
                ->leftJoin('ip.Translation ipt')
                ->where('i.root_id = ?', array($root->id))
                ->andWhere('it.lang = ?', array($language))
                ->andWhere('(ipt.lang = ? OR ipt.lang IS NULL)', array($language))
                ;
            $tree = $this->footerItemTable->getTree();           
            $tree->setBaseQuery($q);
            $footerTree = $tree->fetchTree(array('root_id' => $root->id), $hydrationMode);
            $tree->resetBaseQuery();
            return $footerTree;
        }
    }
    
    public function getFooterForm(Menu_Model_Doctrine_Footer $footer = null) {
        $form = new Menu_Form_Footer();
        if(null !== $footer) {
            $form->populate($footer->toArray());
        }
        return $form;
    }
    
    public function getFooterItemForm(Menu_Model_Doctrine_FooterItem $footerItem = null) {
        $form = new Menu_Form_FooterItem();
        if(null !== $footerItem) {
            $form->populate($footerItem->toArray());
        }
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            $i18nSubform = $form->translations->getSubForm($language);
            if($i18nSubform) {
                $i18nSubform->getElement('title')->setValue($footerItem->Translation[$language]->title);
            }
        }
        return $form;
    }
    
    public function saveFooterFromArray($data) {
        foreach($data as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $data[$key] = NULL;
            }
        }
   
        if(!$footer = $this->footerTable->getProxy($data['id'])) {
            $footer = $this->footerTable->getRecord();
        }
       
//        if(strlen($data['location'])) {
//            $this->footerTable->resetLocation($data['location']);
//        }
       
        $footer->refresh();
        $footer->fromArray($data);
         
        $footer->save();
        return $footer;
    }
    
    public function saveFooterItemFromArray($data) {
        foreach($data as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $data[$key] = NULL;
            }
        }
        if(!$footerItem = $this->footerItemTable->getProxy($data['id'])) {
            $footerItem = $this->footerItemTable->getRecord();
        }
        $data['route'] = $data['target'];
        $footerItem->fromArray($data);
        
        foreach($data['translations'] as $language => $translation) {
            $footerItem->Translation[$language]->title = $translation['title'];
            $footerItem->Translation[$language]->title_attr = $translation['title_attr'];
            $footerItem->Translation[$language]->slug = MF_Text::createSlug($data['translations']['en']['title']);
        }
      //  exit;
        if(preg_match('/^(\w+)_(\d+)$/', $data['target'], $match)) {
            if(isset($match[1]) && isset($match[2])) {
                $footerItem->setTargetType($match[1]);
                $footerItem->setTargetId($match[2]);
            }
        } elseif(preg_match('/^(homepage|contact|login|logout)$/', $data['target'], $match)) {
            if(isset($match[1])) {
                $footerItem->setTargetType($match[1]);
                $footerItem->setTargetId(NULL);
            }
        } else {
            $footerItem->setTargetType(null);
            $footerItem->setTargetId(null);
        }
        $footerItem->save();
        if(strlen($data['parent_id'])) {
            if($parent = $this->getFooterItem($data['parent_id']))
                $footerItem->getNode()->insertAsLastChildOf($parent);
        } else {
            if($footer = $this->getFooter($data['menu_id'])) {
                if($root = $this->getFooterItemRoot($footer))
                    $footerItem->getNode()->insertAsLastChildOf($root);
            }
        }
        
        return $footerItem;
    }

    public function createFooterItemRoot(Menu_Model_Doctrine_Footer $footer) {
        $footerItem = $this->footerItemTable->getRecord();
        $tree = $this->footerItemTable->getTree();
        $footerItem->save();
        $root = $tree->createRoot($footerItem);
        $footer->FooterItemRoot = $root;
        $footer->FooterItems[] = $root;
        $footer->save();
    }
    
    public function getFooterItemRoot($footer) {
        $root = $footer->get('FooterItemRoot');
        return (!$root->isInProxyState()) ? $root : null;
    }
    
    public function moveFooterItem(Menu_Model_Doctrine_FooterItem $item, $direction = 'down') {
        if($direction == 'up') {
            $prevSibling = $item->getNode()->getPrevSibling();
            if($prevSibling) {
                $item->getNode()->moveAsPrevSiblingOf($prevSibling);
            }
        } elseif($direction == 'down') {
            $nextSibling = $item->getNode()->getNextSibling();
            if($nextSibling) {
                $item->getNode()->moveAsNextSiblingOf($nextSibling);
            }
        }
    }
    
    public function removeFooterItem(Menu_Model_Doctrine_FooterItem $footerItem) {
        $footerItem->getNode()->delete();
    }
}

