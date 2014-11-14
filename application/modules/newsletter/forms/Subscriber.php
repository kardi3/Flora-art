<?php

/**
 * Newsletter_Form_Subscriber
 *
 * @author Tomasz Kardas<kardi31@o2.pl>
 */
class Newsletter_Form_Subscriber extends Admin_Form {
    
    public function init() {
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
      
        $name = $this->createElement('text', 'name');
        $name->setLabel('First Name');
        $name->setRequired();
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');

        $lastname = $this->createElement('text', 'lastname');
        $lastname->setLabel('Last Name');
        $lastname->setRequired();
        $lastname->setDecorators(self::$textDecorators);
        $lastname->setAttrib('class', 'span8');
        
        $email = $this->createElement('text', 'email');
        $email->setLabel('Email');
        $email->setRequired();
        $email->setDecorators(self::$textDecorators);
        $email->setAttrib('class', 'span8');
        
        $groups = $this->createElement('multiselect', 'group_id');
        $groups->setLabel('Groups');
        $groups->setDecorators(self::$selectDecorators);
        
        /*
        $repeat = $this->createElement('checkbox', 'repeat');
        $repeat->setLabel('Repeat');
        $repeat->setDecorators(self::$checkgroupDecorators);
        $repeat->setAttrib('class', 'span8');
        
        $sendDate = $this->createElement('text', 'send_date');
        $sendDate->setLabel('Send date');
        $sendDate->setDecorators(self::$datepickerDecorators);
        $sendDate->setAttrib('class', 'span8');
        */
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $groups,
            $lastname,
            $email,
            $name,
            $id,
            $submit
        ));
    }
}

