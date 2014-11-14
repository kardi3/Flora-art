<?php

/**
 * AdminFormErrors
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Admin_View_Helper_AdminFormErrors extends Zend_View_Helper_Abstract {
    
    public function adminFormErrors(Zend_Form $form, $delimiter = '<br/>') {
        $errors = '';
        foreach($form->getElements() as $element) {
            if($element->hasErrors()) {
                $element->setAttrib('class', 'error');
                $errors .= $element->getLabel() . ' - ' . array_shift($element->getMessages()) . $delimiter;
            }
        }
        return $errors;
    }
}

