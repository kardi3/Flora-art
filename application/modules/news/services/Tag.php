<?php

/**
 * News_Service_News
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class News_Service_Tag extends MF_Service_ServiceAbstract{
    
    protected $tagTable;
    
    public function init() {
        $this->tagTable = Doctrine_Core::getTable('News_Model_Doctrine_Tag');
    }
    
    public function getAllTags($countOnly = false) {
        if(true == $countOnly) {
            return $this->tagTable->count();
        } else {
            return $this->tagTable->findAll();
        }
    }
    
    public function getAllArticles($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
       $q = $this->tagTable->getPublishNewsQuery();
        $q = $this->tagTable->getPhotoQuery($q);
        $q->orderBy('a.created_at DESC');
	$q->addWhere('a.publish = 1');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getLastNews($limit = 3,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->tagTable->getPublishNewsQuery();
        $q = $this->tagTable->getPhotoQuery($q);
	$q->limit($limit);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTag($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->tagTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getFullNews($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->tagTable->getPublishNewsQuery();
        $q = $this->tagTable->getPhotoQuery($q);
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
        $q = $this->tagTable->getPublishNewsQuery();
        $q = $this->tagTable->getPhotoQuery($q);
        $q = $this->tagTable->getLimitQuery($limit, $q);
        $q->orderBy('a.created_at DESC');
	$q->addWhere('a.publish = 1');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTagPaginationQuery($slug) {
        $q = $this->tagTable->getTagQuery();
        $q->andWhere('t.slug = ?', $slug);
        return $q;
    }
    
    public function prependSelectTagsOptions($prependEmptyValue = false){
	$tags = $this->getAllTags();
	$options = array();
	if($prependEmptyValue)
	    $options[''] = " ";
	
	foreach($tags as $tag):
	    $options[$tag['id']] = $tag['name'];
	endforeach;
	
	return $options;
    }
    
    public function getTagForm(News_Model_Doctrine_Tag $tag = null) {
         
       
        $form = new News_Form_Tag();
        
        if(null != $tag) {
            $form->populate($tag->toArray());
            
           
        }
        
        return $form;
    }
    
    public function saveTagFromArray($values) {

        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$tag = $this->tagTable->getProxy($values['id'])) {
            $tag = $this->tagTable->getRecord();
        }
        $tag->fromArray($values);
	$tag->slug = MF_Text::createUniqueTableSlug('News_Model_Doctrine_Tag', $values['name'], $tag->get('id'));
              
        
        $tag->save();
       
         
        return $tag;
    }
    
    public function removeNews(News_Model_Doctrine_News $news) {
        $news->delete();
    }
     
    public function searchNews($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->tagTable->getAllNewsQuery();
        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "news" as search_type');
        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
        return $q->execute(array(), $hydrationMode);
    }
}

