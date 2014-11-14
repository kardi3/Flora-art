<?php

/**
 * Newsletter_Form_Group
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Newsletter_Form_Group extends Admin_Form {
    
    public function init() {
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
      
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name');
        $name->setRequired();
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');

        
        $subscribers = $this->createElement('multiselect', 'subscribers');
        $subscribers->setLabel('Subscribers');
        $subscribers->setDecorators(self::$selectDecorators);
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $subscribers,
            $name,
            $id,
            $submit
        ));
    }
}

