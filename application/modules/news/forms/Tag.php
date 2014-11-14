<?php

/**
 * News_Form_Category
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class News_Form_Tag extends Admin_Form {
    
    public function init() {
	
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
  
        
	$name = $this->createElement('text', 'name');
	$name->setLabel('Name');
	$name->setDecorators(self::$textDecorators);
	$name->setAttrib('class', 'span8');
            
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
	    $name,
            $submit
        ));
    }
}

