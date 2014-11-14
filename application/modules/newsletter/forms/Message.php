<?php

/**
 * Newsletter_Form_Message
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Newsletter_Form_Message extends Admin_Form {
    
    public function init() {
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
      
        $title = $this->createElement('text', 'title');
        $title->setLabel('Title');
        $title->setDecorators(self::$textDecorators);
        $title->setAttrib('class', 'span8');

        $content = $this->createElement('textarea', 'content');
        $content->setLabel('Header');
        $content->setDecorators(self::$tinymceDecorators);
        $content->setAttrib('class', 'span8 tinymce');
        
        $subscribers = $this->createElement('select', 'subscribers');
        $subscribers->setLabel('Subscribers');
        $subscribers->setDecorators(self::$selectDecorators);
        
        $groups = $this->createElement('select', 'groups');
        $groups->setLabel('Groups');
        $groups->setDecorators(self::$selectDecorators)
                ->setIsArray(true);
        /*
        $repeat = $this->createElement('checkbox', 'repeat');
        $repeat->setLabel('Repeat');
        $repeat->setDecorators(self::$checkgroupDecorators);
        $repeat->setAttrib('class', 'span8');
         */
        $sendDate = $this->createElement('text', 'send_at');
        $sendDate->setLabel('Send date');
        $sendDate->setDecorators(self::$datepickerDecorators);
        $sendDate->setAttrib('class', 'span8');
       
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $title,
            $subscribers,
            $content,
            $groups,
            $id,
            $sendDate,
            $submit
        ));
    }
}

