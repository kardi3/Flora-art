<?php

/**
 * Product_Form_Dimension
 *
 * @author Michał Kowalik
 */
class Product_Form_Dimension extends Admin_Form {
    
    public function init() {
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $width = $this->createElement('text', 'width');
        $width->setDecorators(self::$textDecorators);
        $width->setLabel('Width');
        $width->setAttrib('style', 'border:0; color:#ED7A53; font-weight:bold; box-shadow:none; margin-top: -15px; background: #FFF;');
        
        $height = $this->createElement('text', 'height');
        $height->setDecorators(self::$textDecorators);
        $height->setLabel('Height');
        $height->setAttrib('style', 'border:0; color:#ED7A53; font-weight:bold; box-shadow:none; margin-top: -15px; background: #FFF;');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Add');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $width,
            $height,
            $submit
        ));
    } 
}