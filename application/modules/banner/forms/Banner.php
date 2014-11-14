<?php

/**
 * Banner_Form_Banner
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Banner_Form_Banner extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
         
        $languages = $i18nService->getLanguageList();

        $translations = new Zend_Form_SubForm();

        foreach($languages as $language) {
            $translationForm = new Zend_Form_SubForm();
            $translationForm->setName($language);
            $translationForm->setDecorators(array(
                'FormElements'
            ));

            $name = $translationForm->createElement('text', 'name');
            $name->setBelongsTo($language);
            $name->setLabel('Name');
            $name->setDecorators(self::$textDecorators);
            $name->setAttrib('class', 'span8');
            
            $translationForm->setElements(array(
                $name
            ));

            $translations->addSubForm($translationForm, $language);
        }

        $this->addSubForm($translations, 'translations');
        
        $website = $this->createElement('text', 'website');
        $website->setLabel('Website');
        $website->setRequired();
        $website->setDecorators(self::$textDecorators);
        $website->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $website,
            $submit
        ));
    }
}

