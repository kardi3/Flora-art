<?php

/**
 * Default_BootstrapForm
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Default_BootstrapForm extends Zend_Form {
    
    public static $elementDecorators = array(
        array('ViewHelper'),
        array('Errors'),
        array('Label'),
        array('HtmlTag', array('tag' => 'fieldset'))
    );
    
    public static $tinymceDecorators = array(
        'ViewHelper',
        array('Errors'),
        array('Description', array('tag' => 'span', 'class' => 'help-inline')), 
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'tinymce-wrapper')),
        array('Label', array('class' => 'control-label')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'control-group'))
    );
    
    public static $submitDecorators = array(
        'ViewHelper',
        array('Description', array('tag' => 'span', 'class' => 'help-inline')), 
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'controls')),
        array(array('Wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'control-group'))
    );
    
    public function isValid($data) {
        $valid = parent::isValid($data);
 
        foreach ($this->getElements() as $element) {
            if ($element->hasErrors()) {
                $oldClass = $element->getAttrib('class');
                if (!empty($oldClass)) {
                    $element->setAttrib('class', $oldClass . ' error');
                } else {
                    $element->setAttrib('class', 'error');
                }
            }
        }
 
        return $valid;
    }
}

