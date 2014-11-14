<?php

/**
 * Product_Service_Product
 *
@author Tomasz Kardas <kardi31@tlen.pl>
 */
class Product_Service_Product extends MF_Service_ServiceAbstract {
    
    protected $productTable;
    
    public function init() {
        $this->productTable = Doctrine_Core::getTable('Product_Model_Doctrine_Product');
        parent::init();
    }
    
    
    
    public function getNewProducts() {    
        $q = $this->productTable->getProductQuery();
        $q->andWhere('new = 1');
        $q->addWhere('pro.active = 1');
        return $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
    }
    
    public function getSliderProducts() {    
        $q = $this->productTable->getProductQuery();
        $q->andWhere('slider = 1');
        $q->addWhere('pro.active = 1');
        return $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
    }
    
     public function getProductsToSend() {    
        $q = $this->productTable->getProductQuery();
        $q->andWhere('newsletter_send = 0');
        return $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
    }
    
    public function searchProducts($query) {    
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.id = ? or tr.name like ?',array($query,'%'.$query.'%'));
        return $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
    }
    
    
    
    public function getProduct($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->productTable->findOneBy($field, $id, $hydrationMode);
    }
    
    
    public function getFullProduct($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) { 
        $q = $this->productTable->getProductQuery();
        
//        $q->andWhere('pro.' . $field . ' = ?', $id);
        $q->andWhere('tr.' .$field.' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getCategoryProducts($category_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) { 
        $q = $this->productTable->getProductQuery();
        $q->addWhere('cat.id = ?',$category_id);
        $q->addWhere('pro.active = 1');
        return $q->execute(array(), $hydrationMode);
    }
    
     public function getShortProduct($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) { 
        $q = $this->productTable->getProductQuery();
        
        $q->andWhere('pro.' . $field . ' = ?', $id);

        return $q->fetchOne(array(), $hydrationMode);
    }
    public function getProductCategory()
    {
        $q = $this->productTable->getProductQuery();
        
      //  $q->andWhere('pro.' . $field . ' = ?', $id);
        $q->andWhere('tr.' .$field.' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    public function getProductForm(Product_Model_Doctrine_Product $product = null) {
        $form = new Product_Form_Product();
        if(null != $product) {
            
            $productArray = $product->toArray();
            
            foreach($product['Categories'] as $category):
                $productArray['category_id'][] = $category->id;
            endforeach;
            
            $form->populate($productArray);
                 
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('name')->setValue($product->Translation[$language]->name);
                    $i18nSubform->getElement('description')->setValue($product->Translation[$language]->description);
                }
            }
            
        }
        return $form;
    }
    
    public function saveProductFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }

        if(!$product = $this->productTable->getProxy($values['id'])) {
            $product = $this->productTable->getRecord();
        }
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');

        $product->fromArray($values);
        
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['name'])) {
                $product->Translation[$language]->name = $values['translations'][$language]['name'];
                $product->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Product_Model_Doctrine_ProductTranslation', $values['translations'][$language]['name'], $product->getId());
                $product->Translation[$language]->description = $values['translations'][$language]['description'];
            }
        }
                
        $product->unlink('Categories');
        foreach($values['category_id'] as $category):
            $product->link('Categories',$category);
        endforeach;
        
        $product->save();
        
        return $product;
    }   
    
    public function refreshStatusProduct($product){
        if ($product->isStatus()):
            $product->setStatus(0);
        else:
            $product->setStatus(1);
        endif;
        $product->save();
    }
    
    public function refreshPromotionProduct($product){
        if ($product->isPromoted()):
            $product->setPromoted(0);
        else:
            $product->setPromoted(1);
        endif;
        $product->save();
    }
    
    public function removeProduct(Product_Model_Doctrine_Product $product) {
        $product->unlink('Categories');
        $product->get('Translation')->delete();
//        $product->get('DimensionPrices')->delete();
        $product->save();
        $product->delete();
    }
    
    public function getAllProducts($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductQuery();
        return $q->execute(array(), $hydrationMode);
    }
    public function getAllProductsGroupCollection($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductQuery();
        $q->groupBy('col.id');
        return $q->execute(array(), $hydrationMode);
    }
     public function getAllProductsGroupCollectionPromoted($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductQuery();
        $q->where('col.promoted = 1');
        $q->groupBy('col.id');
        return $q->execute(array(), $hydrationMode);
    }
    public function getCollectionProducts($id_collection,$limit=1500,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductQuery();
        $q->where('col.id = ?',$id_collection);
        $q->limit('limit ?',$limit);
        return $q->execute(array(), $hydrationMode);
    }
    public function getTargetProductSelectOptions($prependEmptyValue = false, $language = null) {
        $items = $this->getAllProducts();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }

        foreach($items as $item) {
            if($item['DimensionPrices']->toArray()){
                foreach($item['DimensionPrices'] as $dimensionPrice){
                    $w = $dimensionPrice['Dimension']['width'];
                    $h = $dimensionPrice['Dimension']['height'];
                    $result['d'.$dimensionPrice->getId()] = $item->Translation[$language]->name.' ('.$w.'cm x '.$h.'cm)';
                }
            }else{
                $result[$item->getId()] = $item->Translation[$language]->name;
            }
        }
        
        return $result;
    }
    
//    public function getUnSelectedDiscountSelectOptions($discountId, $language = null) {
//        $q = $this->productTable->getProductQuery();
//        $q->andWhere('pro.discount_id != ? OR pro.discount_id IS NULL', $discountId);
//        $items = $q->execute(array(), $hydrationMode);
//        $result = array();
//        foreach($items as $item) {
//                $result[$item->getId()] = $item->Translation[$language]->name;
//        }
//        return $result;
//    }
    
    public function getUnSelectedDiscountSelectOptions($discountId, $language = null, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('pro.discount_id != ? OR pro.discount_id IS NULL', $discountId);
        $items = $q->execute(array(), $hydrationMode);
        $result = array();
        foreach($items as $item) {
            if($item['dimensions']){
                foreach($item['DimensionPrices'] as $dimensionPrice){
                    if($dimensionPrice['discount_id']) continue;
                    $w = $dimensionPrice['Dimension']['width'];
                    $h = $dimensionPrice['Dimension']['height'];
                    $result['d'.$dimensionPrice->getId()] = $item->Translation[$language]->name.' ('.$w.'cm x '.$h.'cm)';
                }
            }else{
                $result[$item->getId()] = $item->Translation[$language]->name;
            }
        }
        return $result;
    }
    
    public function getSelectedDiscountSelectOptions($discountId, $language = null, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductQuery();
//        $q->andWhere('pro.discount_id = ?', $discountId);
        $items = $q->execute(array(), $hydrationMode);
        $result = array();
        foreach($items as $item) {
            if($item['discount_id'] == $discountId){
                $result[$item->getId()] = $item->Translation[$language]->name;
            }else{
                if($item['dimensions'] && $item['DimensionPrices']->toArray()){
                    foreach($item['DimensionPrices'] as $dimensionPrice){
                        if(!$dimensionPrice['discount_id']) continue;
                            $w = $dimensionPrice['Dimension']['width'];
                            $h = $dimensionPrice['Dimension']['height'];
                            $result['d'.$dimensionPrice->getId()] = $item->Translation[$language]->name.' ('.$w.'cm x '.$h.'cm)';
                    }
                }
            }
//            if($item['dimensions'] && $item['DimensionPrices']->toArray()){
//                foreach($item['DimensionPrices'] as $dimensionPrice){
//                    if(!$dimensionPrice['discount_id']) continue;
//                    $w = $dimensionPrice['Dimension']['width'];
//                    $h = $dimensionPrice['Dimension']['height'];
//                    $result['d'.$dimensionPrice->getId()] = $item->Translation[$language]->name.' ('.$w.'cm x '.$h.'cm)';
//                }
//            }elseif($item['discount_id']){
//                $result[$item->getId()] = $item->Translation[$language]->name;
//            }
        }
        return $result;
    }
    
    public function unSelectDiscountProducts($selectedProducts, $newSelectedProducts){
        foreach($selectedProducts as $key => $selectedProduct):
            $flag = false;
            if(!is_numeric($key)) continue;
            foreach($newSelectedProducts as $newSelectedProduct):
                if ($key == $newSelectedProduct):
                    $flag = true;
                endif;
            endforeach;
            if ($flag == false):
                $product = $this->getProduct($key);
                $product->setDiscountId(NULL);
                $product->save();
            endif;
        endforeach;
    }
    
    public function saveAssignedDiscountsFromArray($values, $discountId){
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        $selectedProducts = $this->getSelectedDiscountSelectOptions($discountId);
        $this->unSelectDiscountProducts($selectedProducts, $values);
        foreach($values as $value):
            $product = $this->getProduct($value);
            $product->setDiscountId($discountId);
            $product->save();
        endforeach;
    }
    
    public function getAllProductsForCount($countOnly = false) {
        if(true == $countOnly) {
            return $this->productTable->count();
        } else {
            return $this->productTable->findAll();
        }
    }
    
    public function getProductForCategory($categoryId) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('cat.id = ?', $categoryId);
        $q->addOrderBy('rand()');
        return $q;
    }
    public function getNextProduct(Product_Model_Doctrine_Product $product) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('cat.id = ?', $product['Categories'][0]->getId());
        $q->addWhere('pro.id < ?',$product->getId());
        return $q->fetchOne(array(),Doctrine_Core::HYDRATE_ARRAY);
    }
    
    public function getPreviousProduct(Product_Model_Doctrine_Product $product) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('cat.id = ?', $product['Categories'][0]->getId());
        $q->addWhere('pro.id > ?',$product->getId());
        return $q->fetchOne(array(),Doctrine_Core::HYDRATE_ARRAY);
    }
    
     public function getCatId($catId,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('cat.id = ?',$catId);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getProducerProducts($producerId) {
        $q = $this->productTable->getProductQuery();
        $q->andWhere('prod.id = ?', $producerId);
        $q->addOrderBy('rand()');
        return $q;
    }
    
    public function getPreSortedProductPaginationQuery($productIds) {
        $q = $this->productTable->getProductQuery();
        $q->where('pro.id IN ?', array($productIds));
        if(is_array($productIds)):
            $q->addOrderBy('FIELD(pro.id, '.implode(', ', $productIds).')');
        endif; 
        return $q;  
    }
    
    public function getPreSortedProducerProductPaginationQuery($productIds) {
        $q = $this->productTable->getProductQuery();
        $q->where('pro.id IN ?', array($productIds));
        if(is_array($productIds)):
            $q->addOrderBy('FIELD(pro.id, '.implode(', ', $productIds).')');
        endif; 
        return $q;  
    }
    
    public function getIdProducts($categoryId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getIdsProductsQuery();
        $q->andWhere('cat.id = ?', $categoryId);
        $q->addOrderBy('rand()');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getMostFrequenltyPurchasedProducts($limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('tr.lang = ?', $language);
      //  $q->andWhere('pro.most_frequently_purchased = ?', 1);
        $q->addOrderBy('pro.created_at');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getCategoryBestSellers($categoryId, $limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('tr.lang = ?', $language);
        $q->andWhere('cat.id = ?', $categoryId);
      //  $q->andWhere('pro.most_frequently_purchased = ?', 1);
        $q->addOrderBy('pro.created_at');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getProductDiscounts($limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('tr.lang = ?', $language);
        $q->andWhere('pro.discount_id IS NOT NULL');
        $q->addOrderBy('d.amount_discount');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getProductPromotions($limit, $language){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('tr.lang = ?', $language);
        $q->andWhere('pro.promotion = 1');
        $products = $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
        return $products; 
    }
    public function getPromotedProducts($language='pl'){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('tr.lang = ?', $language);
        $q->andWhere('pro.promoted = 1');
        $products = $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
        return $products; 
    }
    
    public function getPromotionProducts($language='pl'){
        $q = $this->productTable->getProductQuery();
        $q->andWhere('tr.lang = ?', $language);
        $q->addWhere('pro.active = 1');
        $q->andWhere('pro.promotion = 1');
        $products = $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
        return $products; 
    }
    
    public function getProducerIdProducts($producerId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->productTable->getProducerIdsProductsQuery($producerId);
        $q->addOrderBy('rand()');
        return $q->execute(array(), $hydrationMode);
    }
       public function getPopularProducts($language) {    
        $q = $this->productTable->getProductQuery();
    //    $q->where('tr.lang = ?', $language);
        $q->where('pro.purchased_number > 0');
        $q->orderBy('pro.purchased_number DESC');
        $q->limit(3);
        
        return $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
    }
    
    
    
}
?>