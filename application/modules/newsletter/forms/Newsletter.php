<?php

/**
 * Contact
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Newsletter_Form_Newsletter extends Admin_Form {
    
    const SALT = 'cdf1fb4cd650bcf617ff89db3f0068f7';
    
    public function init() {
        $email = new Glitch_Form_Element_Text_Email('email');
        $email->setLabel('E-mail');
        $email->setValidators(array('EmailAddress'));
        $email->setDecorators(self::$textDecorators);
        $email->setRequired();
        $email->setAttrib('class', 'span8');
        
        $name = $this->createElement('text', 'name');
        $name->setLabel('First name');
        $name->setDecorators(self::$textDecorators);
        $name->setRequired();
        $name->setAttrib('class', 'span8');
        
        $lastname = $this->createElement('text', 'lastname');
        $lastname->setLabel('Last name');
        $lastname->setDecorators(self::$textDecorators);
        $lastname->setRequired();
        $lastname->setAttrib('class', 'span8');
        
        $group = $this->createElement('multiCheckbox', 'group_id');
        $group->setLabel('Last name')
              ->setDecorators(array('ViewHelper','Errors'));
        $group->setRequired();
        $group->setAttrib('class', 'span8');
        
        $terms = $this->createElement('checkbox', 'terms');
        $terms->setLabel('I accept the rules of newsletter');
        $terms->setDecorators(array('ViewHelper','Errors'));
        $terms->setRequired();
        $terms->setAttrib('class', 'span8');
        $terms->addValidator(new Zend_Validate_InArray(array(1)));
        $terms->addErrorMessage("Accept the rules first!");
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Sign up!');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $email,
            $name,
            $group,
            $lastname,
            $terms,
            $submit
        ));
    }
}

