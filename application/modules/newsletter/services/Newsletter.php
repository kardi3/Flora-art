<?php

/**
 * Newsletter_Service_Newsletter
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Newsletter_Service_Newsletter extends MF_Service_ServiceAbstract {
    
    protected $messageTable;
    protected $groupTable;
    protected $messageSubscriberTable;
    
    public function init() {
        $this->messageTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Message');
        $this->messageSubscriberTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_MessageSubscriber');
        $this->groupTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Group');
        parent::init();
    }
    
    public function getGroup($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->groupTable->findOneBy($field, $id, $hydrationMode);
    }
   
    public function getMessage($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->messageTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getMessageForm(Newsletter_Model_Doctrine_Message $message = null) {
        $form = new Newsletter_Form_Message();
        if(null != $message) {
            
            $dataArray = $message->toArray();
            
            foreach($message['MessageSubscribers'] as $subscriber):
                $dataArray['subscribers'][] = $subscriber['subscriber_id'];
            endforeach;
            
            foreach($message['Groups'] as $group):
                $dataArray['groups'][] = $group['id'];
            endforeach;
            
            $form->populate($dataArray);
        }
        return $form;
    }
    
    public function getAllNewsletterGroups($order=false){
        $q =  $this->groupTable->createQuery('ng');
        if($order){
            $q->orderBy('ng.name ASC');
        }
        return $q->execute(array(),Doctrine_Core::HYDRATE_RECORD);
    }
    
    public function getAllNewsletterGroupsByType($group_type=null){
        $q =  $this->groupTable->createQuery('ng');
        if($group_type){
            $q->addWhere('ng.group_type = ?',$group_type);
        }
        $q->addWhere('ng.visible = 1');
        $q->orderBy('ng.name ASC');
        return $q->execute(array(),Doctrine_Core::HYDRATE_RECORD);
    }
    
    public function getMessageSubscriber($message_id,$subscriber_id){
        $q = $this->messageSubscriberTable->createQuery('ms');
        $q->select('ms.*');
        $q->addWhere('ms.message_id = ?',$message_id);
        $q->addWhere('ms.subscriber_id = ?',$subscriber_id);
       return $q->fetchOne(array(), Doctrine_Core::HYDRATE_RECORD);
    }
    
    public function getAllSentMessages(){
        $q = $this->messageTable->createQuery('m');
        $q->select('m.*');
        $q->addWhere('sent = 1');
        $q->orderBy('m.send_at');
       return $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
    }
    
     public function setGroupUsersToSend(Newsletter_Model_Doctrine_Message $message){
        foreach($message['Groups'] as $group):
            foreach($group['Subscribers'] as $subscriber):
                $this->saveMessageSubscriber($message->id, $subscriber['id']);
            endforeach;
        endforeach;
    }
    public function sendMessage(Newsletter_Model_Doctrine_Message $message){
   
        foreach($message['MessageSubscribers'] as $messageSubscriber){
            if($messageSubscriber->sent==1)
                continue;
            
            $messageContent = $this->prepareContent($message['content'],$messageSubscriber['Subscriber']['token']);
       
            $mail = new Zend_Mail('UTF-8');
            $mail->setSubject($message['title']);
            $mail->addTo($messageSubscriber['Subscriber']['email']);
            $mail->setBodyHtml($messageContent);
            $mail->send();
            
            $messageSubscriber->setSent();
            $messageSubscriber->save();
                        
            sleep(2);
        }   
        
        $message->setSent();
        $date = new Zend_Date();
        $message->setSendAt($date->get('Y-MM-d H:m:s'));
        $message->save();
    }
    
    public function getNewsletterForm(Newsletter_Model_Doctrine_Newsletter $newsletter = null) {
        $form = new Newsletter_Form_Newsletter();
        if(null != $newsletter) {
            $form->populate($newsletter->toArray());
        }
        return $form;
    }
    
    public function prepareContent($content,$token){
        
        $domain = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('domain');
        
        $newContent = $content."<br /><br />Aby zrezygnować z otrzymywania newslettera kliknij w <a href='http://".$domain."/pl/unsubscribe/".$token."'>ten link</a>";
        
        return $newContent;
    }
    
    public function getMessagesToSend(){
        $q = $this->messageTable->createQuery('m');
        $q->select('m.*');
        $q->leftJoin('m.MessageSubscribers ms');
        $q->addWhere('m.send_at <= CURRENT_TIMESTAMP()');
        $q->addWhere('m.sent = 0');
        $q->addWhere('ms.sent = 0');
        return $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
        
    }
    
    public function saveMessageFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$message = $this->messageTable->getProxy($values['id'])) {
            $message = $this->messageTable->getRecord();
        }
        
        $message->fromArray($values);
        
        $message->unlink('MessageSubscribers');
        $message->unlink('Groups');
        foreach($values['subscribers'] as $key =>$subscriber):
            $message['MessageSubscribers'][$key]['subscriber_id'] = $subscriber;
            $message['MessageSubscribers'][$key]['message_id'] = $values['id'];
        endforeach;
        foreach($values['groups'] as $group):
            $message->link('Groups',$group);
        endforeach;
        $message->save();
        return $message;
    }
    
     public function saveMessageSubscriber($message_id,$subscriber_id){

            $data = array(
                'message_id' => $message_id,
                'subscriber_id' => $subscriber_id
            );
            if(!$this->getMessageSubscriber($message_id, $subscriber_id)){
                $messageSubscriber = $this->messageSubscriberTable->getRecord();
                $messageSubscriber->fromArray($data);
                $messageSubscriber->save();
            }
    }
    
    public function saveMessageSubscriberFromArray(Newsletter_Model_Doctrine_Message $message){

        
        // put all users that mail should be sent to into one table
        $message_id = $message->id;
        foreach($message['Groups'] as $group):
            foreach($group['Subscribers'] as $subscriber):
                $data = array(
                    'message_id' => $message_id,
                    'subscriber_id' => $subscriber->id
                );
                // avoid duplicate rows
                if(!$this->getMessageSubscriber($message_id, $subscriber->id)){
                    $messageSubscriber = $this->messageSubscriberTable->getRecord();
                    $messageSubscriber->fromArray($data);
                    $messageSubscriber->save();
                }
            endforeach;
        endforeach;
    }
    
    public function saveSettingsFromArray($values) {
        
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$message = $this->settingsTable->getProxy($values['id'])) {
            $message = $this->settingsTable->getRecord();
        }

        $message->fromArray($values);

        $message->save();
        
        return $message;
    }
    
    public function getMessageById($id,$field='id'){
        return $this->messageTable->findOneBy($field, $id);
    }
    public function getMessageSettings($id){
        return $this->settingsTable->findOneBy('id', $id);
    }
    
    
    
}

