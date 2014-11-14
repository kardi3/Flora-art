<?php

/**
 * Contact
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Product_Form_Contact extends Default_BootstrapForm {
    
    const SALT = 'cdf1fb4cd650bcf617ff89db3f0068f7';
    
    public function init() {
        
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name')
                ->setDecorators(self::$elementDecorators)
                ->setRequired()
                ->addValidators(
                        array(
                            array('NotEmpty', true)
                        ))
                ->setAttrib('class', 'wpcf7-text')
                ;
        
        
        
        $email = $this->createElement('text', 'email');
        $email->setLabel('Email')
                ->setDecorators(self::$elementDecorators)
                ->setRequired()
                ->addValidators(
                    array(
                        array('NotEmpty', true),
                        array('EmailAddress', true)
                        ))
                ->setAttrib('class', 'wpcf7-text')
                ->getValidator('EmailAddress')->setMessages(
                            array(
                                Zend_Validate_EmailAddress::INVALID => 'Invalid email address',
                                Zend_Validate_EmailAddress::INVALID_FORMAT => 'Invalid email address',
                                Zend_Validate_EmailAddress::DOT_ATOM => 'Invalid email address',
                                Zend_Validate_EmailAddress::INVALID_HOSTNAME => 'Invalid email address',
                                Zend_Validate_EmailAddress::INVALID_LOCAL_PART => 'Invalid email address',
                                Zend_Validate_EmailAddress::INVALID_MX_RECORD => 'Invalid email address',
                                Zend_Validate_EmailAddress::INVALID_SEGMENT => 'Invalid email address',
                                Zend_Validate_EmailAddress::LENGTH_EXCEEDED => 'Invalid email address',
                                Zend_Validate_EmailAddress::QUOTED_STRING => 'Invalid email address'
                            )
                        )
                ;
        
       
        $phone = $this->createElement('text', 'phone');
        $phone->setLabel('Phone')
                ->setDecorators(self::$elementDecorators)
                ->setRequired(false)
                ->setAttrib('class', 'wpcf7-text')
                ;
        
        
        $message = $this->createElement('textarea', 'message');
        $message->setLabel('Message')
                ->setDecorators(self::$elementDecorators)
                ->setAttrib('rows', 6)
                ->setAttrib('cols',22)
                ->setRequired()
                ->addValidators(
                        array(
                            array('NotEmpty', true)
                        ))
                ->setAttrib('class', 'wpcf7-textarea')
                ;
        
        $csrf = $this->createElement('hash', 'csrf');
        $csrf->setSalt(self::SALT);
        $csrf->setDecorators(array('ViewHelper'));
        
        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('Ask about product')
                ->setDecorators(self::$submitDecorators)
                ->setAttrib('type', 'submit')
                ->setAttrib('class', 'wpcf7-submit')
                ;
        
        $this->setElements(array(
            $name,
           
            $email,
            $message,
            $phone,
            $csrf,
            $submit
        ));
    }
}

