<?php

/**
 * Offer_Form_Offer
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Offer_Form_Offer extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $categoryId = $this->createElement('select', 'category_id');
        $categoryId->setLabel('Category');
        $categoryId->setDecorators(self::$selectDecorators);
        $categoryId->setAttrib('class','nostyle');
        
        
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
            $name->setLabel('Title');
            $name->setDecorators(self::$textDecorators);
            $name->setAttrib('class', 'span8');
            
            $description = $translationForm->createElement('textarea', 'description');
            $description->setBelongsTo($language);
            $description->setLabel('Content');
            $description->setDecorators(self::$tinymceDecorators);
            $description->setAttrib('class', 'span8 tinymce');
            
            $translationForm->setElements(array(
                $name,
                $description
            ));

            $translations->addSubForm($translationForm, $language);
        }
        
        $this->addSubForm($translations, 'translations');

        $publish = $this->createElement('checkbox', 'publish');
        $publish->setLabel('Publish');
        $publish->setDecorators(self::$checkgroupDecorators);
        $publish->setAttrib('class', 'span8');
        
        $publishDate = $this->createElement('text', 'publish_date');
        $publishDate->setLabel('Publish date');
        $publishDate->setDecorators(self::$datepickerDecorators);
        $publishDate->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $categoryId,
            $publish,
            $publishDate,
            $submit
        ));
    }
}

