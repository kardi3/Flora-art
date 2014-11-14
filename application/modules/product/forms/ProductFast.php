<?php

/**
 * Product
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Product_Form_ProductFast extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
                       
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name of product');
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');
              
        $categoryId = $this->createElement('select', 'category_id');
        $categoryId->setLabel('Categories');
        $categoryId->setDecorators(self::$selectDecorators);
        
        $collectionId = $this->createElement('select', 'collection_id');
        $collectionId->setLabel('Collection');
        $collectionId->setDecorators(self::$selectDecorators);
        

        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('id' => 'btnSubmit', 'class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $categoryId,
            $name,
            $collectionId,
       
            $submit,
        ));
         $this->setMethod(Zend_Form::METHOD_POST);
        $this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
       
 
        
    }
    
    
    
    
    
    
    ///Funkcje wywoływane w ajaxie ( dodawanie nowych rozmiarów/cen itd)
    
    public function getDiscountElement($discounts){
        $discount = $this->createElement('select', 'newdiscount');
        $discount->setDecorators(array('ViewHelper'));
        $discount->addMultiOptions($discounts);
        
        
        /// Zwracanie kodu html bez znaków nowej linii:
        $output = str_replace(array("\r\n", "\r"), "\n", $discount->render());
        $lines = explode("\n", $output);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
    }
    
    
    public function getDimensionElement($dimensions){
        $dimension = $this->createElement('select', 'dimension');
        $dimension->setDecorators(array('ViewHelper'));
        $dimension->addMultiOptions($dimensions);
        
        
        
        /// Zwracanie kodu html bez znaków nowej linii:
        $output = str_replace(array("\r\n", "\r"), "\n", $dimension->render());
        $lines = explode("\n", $output);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
    }
    
    public function getPriceElement(){
        $price = $this->createElement('text', 'newprice');
        $price->setDecorators(array('ViewHelper'));
        $price->setAttrib('class', 'span6');
        
        
        /// Zwracanie kodu html bez znaków nowej linii:
        $output = str_replace(array("\r\n", "\r"), "\n", $price->render());
        $lines = explode("\n", $output);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
    }
    
    public function getAvailabilityElement(){
        $availability = $this->createElement('text', 'newavailability');
        $availability->setDecorators(array('ViewHelper'));
        $availability->setAttrib('class', 'span6');
        
        // Zwracanie kodu html bez znaków nowej linii:
        $output = str_replace(array("\r\n", "\r"), "\n", $availability->render());
        $lines = explode("\n", $output);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
    }
    
     public function getPromotedElement(){
        $promoted = $this->createElement('checkbox', 'newpromoted');
        $promoted->setDecorators(array('ViewHelper'));
        
        // Zwracanie kodu html bez znaków nowej linii:
        $output = str_replace(array("\r\n", "\r"), "\n", $promoted->render());
        $lines = explode("\n", $output);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
         
    }
    
    public function getNewElement(){
        $new = $this->createElement('checkbox', 'lastnew');
        $new->setDecorators(array('ViewHelper'));
        
        // Zwracanie kodu html bez znaków nowej linii:
        $output = str_replace(array("\r\n", "\r"), "\n", $new->render());
        $lines = explode("\n", $output);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
         
    }
    
    public function getMostFrequentlyPurchasedElement(){
        $mostFrequentlyPurchased = $this->createElement('checkbox', 'mostfrequentlypurchased');
        $mostFrequentlyPurchased->setDecorators(array('ViewHelper'));
        
        // Zwracanie kodu html bez znaków nowej linii:
        $output = str_replace(array("\r\n", "\r"), "\n", $mostFrequentlyPurchased->render());
        $lines = explode("\n", $output);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
         
    }
    
    public function getPromotionElement(){
        $promotion = $this->createElement('checkbox', 'newpromotion');
        $promotion->setDecorators(array('ViewHelper'));
        $promotion->setAttrib('style', 'margin-right: 20px; margin-top: -8px;');
        
        // Zwracanie kodu html bez znaków nowej linii:
        $output = str_replace(array("\r\n", "\r"), "\n", $promotion->render());
        $lines = explode("\n", $output);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
    }
    
    public function getPromotionPriceElement(){
        $promotionPrice = $this->createElement('text', 'newpromotionprice');
        $promotionPrice->setDecorators(array('ViewHelper'));
        $promotionPrice->setAttrib('class', 'span6');
        $promotionPrice->setAttrib('readonly', true);
        
        
        /// Zwracanie kodu html bez znaków nowej linii:
        $output = str_replace(array("\r\n", "\r"), "\n", $promotionPrice->render());
        $lines = explode("\n", $output);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
    }
    
    public function getDiscountsElement($discounts=null){
        $discount = $this->createElement('select', 'discount');
        $discount->setLabel('Discount');
        $discount->setDecorators(array('ViewHelper'));
        $discount->setMultiOptions($discounts);
        
        // Zwracanie kodu html bez znaków nowej linii:
        $output = str_replace(array("\r\n", "\r"), "\n", $discount->render());
        $lines = explode("\n", $output);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
    }
    
}
?>