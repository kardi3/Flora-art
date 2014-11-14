<?php

/**
 * News_IndexController
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class News_IndexController extends MF_Controller_Action {
 
    public static $articleItemCountPerPage = 10;
    
    public function articlesAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        
        $query = $newsService->getNewsPaginationQuery($this->language);

        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_RECORD);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage(4);
        
        $this->view->assign('paginator', $paginator);
        
        $this->_helper->layout->setLayout('page');
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function articleAction() {
        $newsService = $this->_service->getService('News_Service_News');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        if(!$article = $newsService->getFullNews($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('News '.$this->getRequest()->getParam('slug').' not found', 404);
        }
        $metatagService->setViewMetatags($article->get('Metatags'), $this->view);
       
	$pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';

	if(!$pageWasRefreshed ) {
	   $article->increaseViews();
	}
	
	$recentNews = $newsService->getRecentNews($article['id'],4,Doctrine_Core::HYDRATE_ARRAY);
	
        $this->view->assign('article', $article);
        $this->view->assign('recentNews', $recentNews);
        
        $this->_helper->layout->setLayout('page');
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function tagAction() {
        $newsService = $this->_service->getService('News_Service_News');
	
        $tagService = $this->_service->getService('News_Service_Tag');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
	if(!$tag = $tagService->getTag($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Tag '.$this->getRequest()->getParam('slug').' not found', 404);
        }
        
	$metatagService->setViewMetatags($tag['metatag_id'],$this->view);
	
        $query = $newsService->getTagPaginationQuery($tag['slug']);

        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_RECORD);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage(4);
        
        $this->view->assign('tag', $tag);
        $this->view->assign('paginator', $paginator);
        
        $this->_helper->layout->setLayout('page');
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    
}

