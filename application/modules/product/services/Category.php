<?php

/**
 * Product_Service_Category
 *
@author Tomasz Kardas <kardi31@tlen.pl>
 */
class Product_Service_Category extends MF_Service_ServiceAbstract {
    
    protected $categoryTable;
    
    public function init() {
        $this->categoryTable = Doctrine_Core::getTable('Product_Model_Doctrine_Category');
        parent::init();
    }
    
    public function getCategory($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->categoryTable->findOneBy($field, $id, $hydrationMode);
    }
    public function getNewsletterCategories(){
        $tab = $this->categoryTable->findAll()->toArray();
        $ret = array();
        foreach($tab as $key => $item){
            $ret[$item['id']] = $item['name'];
        }

        return $ret;
    }
    
    public function getFullCategory($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) { 
        $q = $this->categoryTable->getCategoryQuery();
        $q->andWhere('tr.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getCategoriesForNewsletter($hydrationMode = Doctrine_Core::HYDRATE_RECORD) { 
        $q = $this->categoryTable->getCategoryQuery();
        $q->addOrderBy('tr.name');
        $q->addWhere('tr.slug != "kategorie"');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getCategoryWithProducts($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) { 
        $q = $this->categoryTable->getCategoryQuery();
        $q->leftJoin('cat.Products pro');
        $q->leftJoin('pro.Translation pt');
        $q->andWhere('tr.' . $field . ' = ?', $id);
        $q->addWhere('pro.active = 1');
        $q->orderBy('pt.name ASC');
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function findElements($slug, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) { 
        $q = $this->categoryTable->getCategoryQuery();
        $q->leftJoin('cat.Photos p');
        $q->leftJoin('p.Translation pt');
        $q->andWhere('pt.slug = ?', $slug);
        $q->orderBy('pt.name ASC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getCategoryForm(Product_Model_Doctrine_Category $category = null) {
        $form = new Product_Form_Category();
        if(null != $category) {
            $form->populate($category->toArray());
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('name')->setValue($category->Translation[$language]->name);
                    $i18nSubform->getElement('description')->setValue($category->Translation[$language]->description);
                }
            }
        }
        return $form;
    }
    
    public function getAllLampCategories() {
         $q = $this->categoryTable->createQuery('cat');
         $q->leftJoin('cat.Translation ct');
         $q->addWhere('ct.slug != "abazury"');
        
         return $q->execute(array(),Doctrine_Core::HYDRATE_RECORD);
        
    }
    
    public function getCategoryRoot() {
        return $this->getCategoryTree()->fetchRoot();
    }
    
    public function getCategoryTree() {
        return $this->categoryTable->getTree();
    }
    
     public function getPromotedCategories() {
         $q = $this->categoryTable->createQuery('cat');
         $q->addWhere('cat.promoted = 1');
        
         return $q->execute(array(),Doctrine_Core::HYDRATE_RECORD);
    }
    
    
    
    public function createCategoryRoot($languagesDefined) {
        $category = $this->categoryTable->getRecord();
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            $category->Translation[$language]->name = $languagesDefined[$language];
        }
        $category->save();
        
        $tree = $this->getCategoryTree();
        $tree->createRoot($category);
        
        return $category;
    }     
   
    public function saveCategoryFromArray(array $values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$category = $this->categoryTable->getProxy($values['id'])) {
            $category = $this->categoryTable->getRecord();
        }
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');

        $category->fromArray($values);
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['name'])) {
                $category->Translation[$language]->name = $values['translations'][$language]['name'];
                $category->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Product_Model_Doctrine_CategoryTranslation', $values['translations'][$language]['name'], $category->getId());
                $category->Translation[$language]->description = $values['translations'][$language]['description'];
            }
        }
        
        $category->save();
        
        if(isset($values['parent_id'])) {
            $parent = $this->getCategory((int) $values['parent_id']);
            $category->getNode()->insertAsLastChildOf($parent);
        }
        
        return $category;
    }
    
    public function moveCategory($category, $dest, $mode) {
        switch($mode) {
            case 'before':
                if($dest->getNode()->isRoot()) {
                    throw new Exception('Cannot move category on root level');
                }
                $category->getNode()->moveAsPrevSiblingOf($dest);
                break;
            case 'after':
                if($dest->getNode()->isRoot()) {
                    throw new Exception('Cannot move category on root level');
                }
                $category->getNode()->moveAsNextSiblingOf($dest);
                break;
            case 'over':
                $category->getNode()->moveAsLastChildOf($dest);
                break;
        }
    }
    
    public function removeCategory($category) {
        $category->get('Translation')->delete();
	$category->delete();
    }
    
    public function refreshStatusCategory($category){
        if ($category->isStatus()):
            $category->setStatus(0);
        else:
            $category->setStatus(1);
        endif;
        $category->save();
    }
    
    public function getAllCategories($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->categoryTable->getCategoryQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetCategorySelectOptions($prependEmptyValue = false, $language = null) {
        $items = $this->getAllCategories();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
            if ($item->level > 0):
                $result[$item->getId()] = $item->Translation[$this->language]->name;
            endif;
        }
        return $result;
    } 
    
     public function getAllCategoriesForCount($count = null) {
        if(null != $count) {
            return $this->categoryTable->count();
        } else {
            return $this->categoryTable->findAll();
        }
    }
    
    public function getMainCategories($limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->categoryTable->getMainCategoriesQuery();
        $q->limit($limit);
        $q->andWhere('ct.lang = ?', $language);
        return $q->execute(array(), $hydrationMode);
    }
    
//    public function getTargetCategorySelectOptions($prependEmptyValue = false) {
////        if(null == $category) {
////            $category = $this->getCategoryRoot();
////        }
////        
////        foreach($category->getNode()->getChildren() as $child) {
////            if($child->getNode()->getChildren()) {
////                $result[$child->getName()] = $this->getTargetCategorySelectOptions($child);
////            } else {
////                $result[$child->getId()] = $child->getName();
////            }
////        }
////        return $result;
//        $items = $this->getAllCategories();
//        $result = array();
//        if($prependEmptyValue) {
//            $result[''] = ' ';
//        }
//        foreach($items as $item) {
////            $name = "";
////            $descendants = $item->getNode()->getDescendants(null, true);
////            foreach($descendants as $desc):
////                $name .= $desc->name."->";
////            endforeach;
////            if ($item->level > 0):
////                $result[$item->getId()] = $name;
////            endif;
//             if ($item->level > 0):
//            $result[$item->getId()] = str_repeat('|----', $item->level) . $item->name;
//         endif;
//             
//        }
//        return $result;
//    }  
    
}
?>