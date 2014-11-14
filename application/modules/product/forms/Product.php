<?php

/**
 * Product
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Product_Form_Product extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
               
        $code = $this->createElement('text', 'code');
        $code->setLabel('Product code');
        $code->setRequired(false);
        $code->setDecorators(self::$textDecorators);
        $code->setAttrib('class', 'span8');
        
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name of product');
        $name->setRequired();
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');
//        
//        $producerId = $this->createElement('select', 'producer_id');
//        $producerId->setLabel('Producer');
//        $producerId->setDecorators(self::$selectDecorators);
        
        $categoryId = $this->createElement('multiselect', 'category_id');
        $categoryId->setLabel('Categories');
        $categoryId->setRequired();
        $categoryId->setDecorators(self::$selectDecorators);
        $categoryId->setAttrib('multiple', 'multiple');
     
        
        $price = $this->createElement('text', 'price');
        $price->setLabel('Price');
//        $price->setRequired();
        $price->addValidators(array(
            array('Float', true)
        ));
        $price->setDecorators(self::$textDecorators);
        $price->setAttrib('class', 'span8');
        
        $dimensions = $this->createElement('checkbox', 'dimensions');
        $dimensions->setLabel('Product in different sizes');
        $dimensions->setDecorators(self::$checkboxDecorators);
        $dimensions->setAttrib('class', 'ibutton nostyle');
        
        $availability = $this->createElement('text', 'availability');
        $availability->setLabel('Availability');
        $availability->addValidators(array(
            array('Int', true)
        ));
        $availability->setDecorators(self::$textDecorators);
        $availability->setAttrib('class', 'span8');
        
        $discountId = $this->createElement('select', 'discount_id');
        $discountId->setLabel('Discount');
        $discountId->setDecorators(self::$selectDecorators);
        
        $description = $this->createElement('textarea', 'description');
        $description->setLabel('Description');
        $description->setDecorators(self::$tinymceDecorators);
        $description->setAttrib('class', 'span8 tinymce');
        
        $slider = $this->createElement('checkbox', 'slider');
        $slider->setLabel('Slider');
        $slider->setDecorators(self::$checkboxDecorators);
        $slider->setAttrib('class', 'span8');
        
        $promotion = $this->createElement('checkbox', 'promotion');
        $promotion->setLabel('Promotion');
        $promotion->setDecorators(self::$checkboxDecorators);
        $promotion->setAttrib('class', 'span8');
        
        $promotionPrice = $this->createElement('text', 'promotion_price');
        $promotionPrice->setLabel('Promotion price');
        $promotionPrice->setDecorators(self::$textDecorators);
        $promotionPrice->setAttrib('class', 'span8');
        
        $new = $this->createElement('checkbox', 'new');
        $new->setLabel('Last new');
        $new->setDecorators(self::$checkboxDecorators);
        $new->setAttrib('class', 'span8');
        
        $active = $this->createElement('checkbox', 'active');
        $active->setLabel('Active');
        $active->setDecorators(self::$checkboxDecorators);
        $active->setAttrib('class', 'span8');
        
        $sold = $this->createElement('checkbox', 'sold');
        $sold->setLabel('Sold');
        $sold->setDecorators(self::$checkboxDecorators);
        $sold->setAttrib('class', 'span8');
        
        $mostFrequentlyPurchased = $this->createElement('checkbox', 'most_frequently_purchased');
        $mostFrequentlyPurchased->setLabel('Most frequently purchased');
        $mostFrequentlyPurchased->setDecorators(self::$checkboxDecorators);
        $mostFrequentlyPurchased->setAttrib('class', 'span8');
        
        $youtube = $this->createElement('text', 'youtube');
        $youtube->setLabel('Youtube');
        $youtube->setRequired(false);
        $youtube->setDecorators(self::$textDecorators);
        $youtube->setAttrib('class', 'span8');
        
        $languages = $i18nService->getLanguageList();

        $translations = new Zend_Form_SubForm();

        foreach($languages as $language) {
            $translationForm = new Zend_Form_SubForm();
            $translationForm->setName($language);
            $translationForm->setDecorators(array(
                'FormElements'
            ));
            
            $name = $translationForm->createElement('text', 'name');
            $name->setBelongsTo($language);
            $name->setLabel('Name of product');
            $name->setDecorators(self::$textDecorators);
            $name->setAttrib('class', 'span8');
           
            
            $description = $translationForm->createElement('textarea', 'description');
            $description->setBelongsTo($language);
            $description->setLabel('Description');
            $description->setRequired(false);
            $description->setDecorators(self::$tinymceDecorators);
            $description->setAttrib('class', 'span8 tinymce');
            
            $translationForm->setElements(array(
                $name,
                $description
            ));

            $translations->addSubForm($translationForm, $language);
        }
        
        $this->addSubForm($translations, 'translations');
         
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('id' => 'btnSubmit', 'class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $code,
//            $producerId,
            $categoryId,
            $discountId,
            $price,
            $dimensions,
            $availability,
            $slider,
            $promotion,
            $promotionPrice,
            $sold,
            $active,
            $new,
            $mostFrequentlyPurchased,
            $youtube,
            $submit,
        ));
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
        $slider = $this->createElement('checkbox', 'newslider');
        $slider->setDecorators(array('ViewHelper'));
        
        // Zwracanie kodu html bez znaków nowej linii:
        $output = str_replace(array("\r\n", "\r"), "\n", $slider->render());
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