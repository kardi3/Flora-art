<?php

/**
 * Menu_IndexController
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Menu_IndexController extends MF_Controller_Action
{
    public function mainMenuAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        if(!$menu = $menuService->getMenu(1)) {
            throw new Zend_Controller_Action_Exception('Menu not found');
        }
        
        $tree = $menuService->getMenuItemTree($menu, $this->view->language);
        
        $this->view->assign('menu', $menu);
        $this->view->assign('tree', $tree[0]->getNode()->getChildren());
        
        $this->_helper->viewRenderer->setResponseSegment('mainMenu');
    }
    
    public function categoryMenuAction() {
        $menuService = $this->_service->getService('Product_Service_Category');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
	if(!$category = $menuService->getMenu(1)) {
            throw new Zend_Controller_Action_Exception('Menu not found');
        }
	
        if(!$menu = $menuService->getMenu(1)) {
            throw new Zend_Controller_Action_Exception('Menu not found');
        }
        
        $tree = $menuService->getMenuItemTree($menu, $this->view->language);
        
        $this->view->assign('menu', $menu);
        $this->view->assign('tree', $tree[0]->getNode()->getChildren());
        
    }
   
}

