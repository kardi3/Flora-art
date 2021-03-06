<?php

/**
 * Media_Form_Video
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Media_Form_Video extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $title = $this->createElement('text', 'title');
        $title->setLabel('Title');
        $title->setDecorators(self::$_standardFormDecorators);
        
        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('OK');
        $submit->setDecorators(array('ViewHelper'));

        $this->setElements(array(
            $id,
            $title,
            $submit
        ));
    }
}

