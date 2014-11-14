<?php

/**
 * Newsletter_IndexController
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Newsletter_IndexController extends MF_Controller_Action {
    
   public function newsletterAction(){
        
       
        $newsletterService = $this->_service->getService('Newsletter_Service_Newsletter');
        $subscriberService = $this->_service->getService('Newsletter_Service_Subscriber');
    
        $form = $newsletterService->getNewsletterForm();
        $form->removeElement('name');
        $form->removeElement('lastname');
        $form->removeElement('terms');
        
        $messages = $newsletterService->getAllSentMessages();
        
        // tworzymy kopie formularzy ponieważ opcje muszą być pogrupowane wg typu
        
        $kimJestesForm = clone($form);
        $tradeForm = clone($form);
        $eventForm = clone($form);
        $designForm = clone($form);
        $academyForm = clone($form);
        
        $kimJestesGroups = $newsletterService->getAllNewsletterGroupsByType('kim-jestes');
        $tradeGroups = $newsletterService->getAllNewsletterGroupsByType('trade');
        $eventGroups = $newsletterService->getAllNewsletterGroupsByType('event');
        $designGroups = $newsletterService->getAllNewsletterGroupsByType('design');
        $academyGroups = $newsletterService->getAllNewsletterGroupsByType('academy');
        
        
        foreach($kimJestesGroups as $group):
            $kimJestesForm->getElement('group_id')->addMultiOption($group->id,$group->name);
        endforeach;
        foreach($tradeGroups as $group):
            $tradeForm->getElement('group_id')->addMultiOption($group->id,$group->name);
        endforeach;
        foreach($eventGroups as $group):
            $eventForm->getElement('group_id')->addMultiOption($group->id,$group->name);
        endforeach;
        foreach($designGroups as $group):
            $designForm->getElement('group_id')->addMultiOption($group->id,$group->name);
        endforeach;
        foreach($academyGroups as $group):
            $academyForm->getElement('group_id')->addMultiOption($group->id,$group->name);
        endforeach;
        
        $this->view->assign('kimJestesForm',$kimJestesForm);
        $this->view->assign('tradeForm',$tradeForm);
        $this->view->assign('eventForm',$eventForm);
        $this->view->assign('designForm',$designForm);
        $this->view->assign('academyForm',$academyForm);
        
        //$form->getElement('group_id')->setMultiOptions(array('group1'=>array('opcja1','opcja2','opcja3'),'group2'=>array('opcja4','opcja5')));
        
        if($this->getRequest()->isPost()) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $values = $_POST;
                Zend_Debug::dump($values);exit;
                $this->view->messages()->clean();
                if($subscriber = $subscriberService->getSubscriber($values['email'],'email')) {
                    $this->view->messages()->add($this->view->translate('This mail is already in our database'));
                }
                else{
                    $subscriberService->saveSubscriberFromArray($values);
                    $this->view->messages()->add($this->view->translate('Your mail has been added to our database'));
                }
                $this->_service->get('doctrine')->getCurrentConnection()->commit();

                $form->reset();

                //$this->_helper->redirector->gotoUrl($this->view->adminUrl('list-message', 'newsletter'));
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                $this->_service->get('log')->log($e->getMessage(), 4);
            }
        }
        $this->view->assign('form', $form);
        $this->view->assign('messageList', $messages);
        $this->_helper->actionStack('layout-serwis10', 'index', 'default');
   }
   
   public function messageViewAction(){
        
       
        $newsletterService = $this->_service->getService('Newsletter_Service_Newsletter');
        $subsService = $this->_service->getService('Newsletter_Service_Subscriber');
        
        $message_id = $this->getRequest()->getParam('id');
        //echo $message_id;
        $message = $newsletterService->getMessageById($this->getRequest()->getParam('slug'),'slug');
        
        $this->view->assign('message', $message);
        
    
   } 
   
   public function newsletterUnsubscribeAction(){
        
       $this->_helper->layout->setLayout('page');
       
        $subscriberService = $this->_service->getService('Newsletter_Service_Subscriber');
        
        $token = $this->getRequest()->getParam('token');
        
        $result = $subscriberService->removeSubscriber($token,'token');
        
        $this->view->assign('result',$result);
    
   } 
    
}

