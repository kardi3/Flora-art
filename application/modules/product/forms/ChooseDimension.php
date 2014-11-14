<?php

/**
 * Product_Form_ChooseDimension
 *
 * @author Michał Kowalik
 */
class Product_Form_ChooseDimension extends Admin_Form {
    
    
    public function init() {
        $dimension = $this->createElement('select', 'dimension');
        $dimension->setLabel('Dimension');
        
        $this->addElement($dimension);
    }
    
    public function addDimensions($dimensions){
        foreach ($dimensions as $dimension){
            $value = 'aaaa';
            $this->dimension->addMultiOption($dimension['id'], $value);
        }
    }
}