<?php

/**
 * Offer_Service_Offer
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Offer_Service_Offer extends MF_Service_ServiceAbstract{
    
    protected $offerTable;
    
    public function init() {
        $this->offerTable = Doctrine_Core::getTable('Offer_Model_Doctrine_Offer');
    }
    
    public function getAllOffer($countOnly = false) {
        if(true == $countOnly) {
            return $this->offerTable->count();
        } else {
            return $this->offerTable->findAll();
        }
    }
    
    public function getAllOffers($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
       $q = $this->offerTable->getPublishOfferQuery();
//        $q = $this->offerTable->getPhotoQuery($q);
//        $q->orderBy('a.created_at DESC');
//	$q->addWhere('a.publish = 1');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getOffer($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->offerTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getFullOffer($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->offerTable->getPublishOfferQuery();
        $q = $this->offerTable->getPhotoQuery($q);
        if(in_array($field, array('id'))) {
            $q->andWhere('a.' . $field . ' = ?', array($id));
        } elseif(in_array($field, array('slug'))) {
            $q->andWhere('at.' . $field . ' = ?', array($id));
            $q->andWhere('at.lang = ?', 'pl');
        }
	$q->addWhere('a.publish = 1');
        return $q->fetchOne(array(), $hydrationMode);
    }

    public function getArticles($limit, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->offerTable->getPublishOfferQuery();
        $q = $this->offerTable->getPhotoQuery($q);
        $q = $this->offerTable->getLimitQuery($limit, $q);
        $q->orderBy('a.created_at DESC');
	$q->addWhere('a.publish = 1');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getOfferPaginationQuery($language) {
        $q = $this->offerTable->getOfferPaginationQuery();
        $q->andWhere('at.lang = ?', $language);
        $q->addOrderBy('a.publish_date DESC');
        return $q;
    }
    
    public function getOfferForm(Offer_Model_Doctrine_Offer $offer = null) {
         
       
        $form = new Offer_Form_Offer();
        $form->setDefault('publish', 1);
        
        if(null != $offer) {
            $form->populate($offer->toArray());
            
            if($publishDate = $offer->getPublishDate()) {
                $form->getElement('publish_date')->setValue(MF_Text::timeFormat('d/m/Y H:i'));
            }
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('name')->setValue($offer->Translation[$language]->name);
                    $i18nSubform->getElement('description')->setValue($offer->Translation[$language]->description);
                }
            }
        }
        
        return $form;
    }
    
    public function saveOfferFromArray($values) {

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$offer = $this->offerTable->getProxy($values['id'])) {
            $offer = $this->offerTable->getRecord();
        }
       
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        if(strlen($values['publish_date'])) {
            $date = new Zend_Date($values['publish_date'], 'dd/MM/yyyy HH:mm');
            $values['publish_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($offer['publish_date'])) {
            $values['publish_date'] = date('Y-m-d H:i:s');
        }
        
        $offer->fromArray($values);
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['name'])) {
                $offer->Translation[$language]->name = $values['translations'][$language]['name'];
                $offer->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Offer_Model_Doctrine_OfferTranslation', $values['translations'][$language]['name'], $offer->getId());
                $offer->Translation[$language]->description = $values['translations'][$language]['description'];
            }
        }
        
        $offer->save();
       
         
        return $offer;
    }
    
    public function removeOffer(Offer_Model_Doctrine_Offer $offer) {
        $offer->delete();
    }
     
    public function searchOffer($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->offerTable->getAllOfferQuery();
        $q->addSelect('TRIM(at.name) AS search_name, TRIM(at.description) as search_description, "offer" as search_type');
        $q->andWhere('at.name LIKE ? OR at.description LIKE ?', array("%$phrase%", "%$phrase%"));
        return $q->execute(array(), $hydrationMode);
    }
}

