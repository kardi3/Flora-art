<?php

/**
 * News_Service_News
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class News_Service_News extends MF_Service_ServiceAbstract{
    
    protected $newsTable;
    
    public function init() {
        $this->newsTable = Doctrine_Core::getTable('News_Model_Doctrine_News');
    }
    
    public function getAllNews($countOnly = false) {
        if(true == $countOnly) {
            return $this->newsTable->count();
        } else {
            return $this->newsTable->findAll();
        }
    }
    
    public function getAllArticles($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
       $q = $this->newsTable->getPublishNewsQuery();
        $q = $this->newsTable->getPhotoQuery($q);
        $q->orderBy('a.created_at DESC');
	$q->addWhere('a.publish = 1');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getLastNews($limit = 3,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->newsTable->getPublishNewsQuery();
        $q = $this->newsTable->getPhotoQuery($q);
	$q->leftJoin('a.Tags t');
	$q->addSelect('t.*');
	$q->limit($limit);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNews($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->newsTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getFullNews($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->newsTable->getPublishNewsQuery();
        $q = $this->newsTable->getPhotoQuery($q);
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
        $q = $this->newsTable->getPublishNewsQuery();
        $q = $this->newsTable->getPhotoQuery($q);
        $q = $this->newsTable->getLimitQuery($limit, $q);
        $q->orderBy('a.created_at DESC');
	$q->addWhere('a.publish = 1');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getRecentNews($article_id, $limit, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->newsTable->getPublishNewsQuery();
        $q = $this->newsTable->getPhotoQuery($q);
        $q = $this->newsTable->getLimitQuery($limit, $q);
	$q->addWhere('a.id != ?',$article_id);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNewsPaginationQuery($language) {
        $q = $this->newsTable->getNewsPaginationQuery();
        $q->andWhere('at.lang = ?', $language);
        $q->addOrderBy('a.publish_date DESC');
        return $q;
    }
    
    public function getTagPaginationQuery($slug) {
        $q = $this->newsTable->getNewsPaginationQuery();
	$q->leftJoin('a.Tags t');
	$q->addSelect('t.*');
        $q->andWhere('t.slug = ?', $slug);
        $q->addOrderBy('a.publish_date DESC');
        return $q;
    }
    
    public function getNewsForm(News_Model_Doctrine_News $news = null) {
         
       
        $form = new News_Form_News();
        $form->setDefault('publish', 1);
        
        if(null != $news) {
            $form->populate($news->toArray());
            
            if($publishDate = $news->getPublishDate()) {
                $form->getElement('publish_date')->setValue(MF_Text::timeFormat($publishDate,'d/m/Y H:i'));
            }
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('name')->setValue($news->Translation[$language]->name);
                    $i18nSubform->getElement('description')->setValue($news->Translation[$language]->description);
                }
            }
        }
        
        return $form;
    }
    
    public function saveNewsFromArray($values) {

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$news = $this->newsTable->getProxy($values['id'])) {
            $news = $this->newsTable->getRecord();
        }
       
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        if(strlen($values['publish_date'])) {
            $date = new Zend_Date($values['publish_date'], 'dd/MM/yyyy HH:mm');
            $values['publish_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } elseif(!strlen($news['publish_date'])) {
            $values['publish_date'] = date('Y-m-d H:i:s');
        }
        
        $news->fromArray($values);
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['name'])) {
                $news->Translation[$language]->name = $values['translations'][$language]['name'];
                $news->Translation[$language]->slug = MF_Text::createUniqueTableSlug('News_Model_Doctrine_NewsTranslation', $values['translations'][$language]['name'], $news->getId());
                $news->Translation[$language]->description = $values['translations'][$language]['description'];
            }
        }
	
	$news->unlink('Tags');
	foreach($values['tag_id'] as $tag_id):
	    $news->link('Tags',$tag_id);
	endforeach;
	
        $news->save();
       
         
        return $news;
    }
    
    public function removeNews(News_Model_Doctrine_News $news) {
        $news->delete();
    }
     
    public function searchNews($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->newsTable->getAllNewsQuery();
        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "news" as search_type');
        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
        return $q->execute(array(), $hydrationMode);
    }
}

