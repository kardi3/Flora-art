<?php

/**
 * Newsletter_Service_Subscriber
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Newsletter_Service_Subscriber extends MF_Service_ServiceAbstract {
    
    protected $groupTable;
    protected $subscriberTable;
    
    public function init() {
        $this->groupTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Group');
        $this->subscriberTable = Doctrine_Core::getTable('Newsletter_Model_Doctrine_Subscriber');
        parent::init();
    }
    
    public function getGroup($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->groupTable->findOneBy($field, $id, $hydrationMode);
    }
    
     public function getSubscriber($id,$field='id'){
        return $this->subscriberTable->findOneBy($field, $id);
        
    }
    
    public function getParameters(){ 
        $q = $this->parametersTable->createQuery('i')
                ->select('i.*')
                ->orderBy('i.id', 'ASC');
        return $q->execute();
    }
    
    public function getGroupForm(Newsletter_Model_Doctrine_Group $group = null) {
        $form = new Newsletter_Form_Group();
        if(null != $group) {
            $groupArray = $group->toArray();
            
            foreach($group['Subscribers'] as $subscriber):
                $groupArray['subscribers'][] = $subscriber['id'];
            endforeach;
            
            $form->populate($groupArray);
        }
        return $form;
    }
    
    public function getSubscriberForm(Newsletter_Model_Doctrine_Subscriber $subscriber = null) {
     
        $form = new Newsletter_Form_Subscriber();
        if(null != $subscriber) {
            
            $subscriberData = $subscriber->toArray();
            
            foreach($subscriber['Groups'] as $group):
                $subscriberData['group_id'][] = $group->id;
            endforeach;
            
            $form->populate($subscriberData);
        }
        return $form;
    }
    
    public function removeGroup($id){
        $subs = $this->groupTable->findBy('id', $id);
        $subs->delete();        
    }
    
    
    public function saveSubscriberFromArray($values) {
        
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$subscriber = $this->subscriberTable->getProxy($values['id'])){
            $subscriber = $this->subscriberTable->getRecord();
        }
        
        $values['token'] = MF_Text::createUniqueToken();
        
        $subscriber->fromArray($values);
        
        $subscriber->unlink('Groups');
        foreach($values['group_id'] as $group_id):
            $subscriber->link('Groups',$group_id);
        endforeach;
        $subscriber->save();
        return $subscriber;
    }
    
    public function saveGroupFromArray($values) {
        
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$message = $this->groupTable->getProxy($values['id'])) {
            $message = $this->groupTable->getRecord();
        }

        $message->fromArray($values);
        
        $message->unlink('Subscribers');
        foreach($values['subscribers'] as $subscriber):
            $message->link('Subscribers',$subscriber['id']);
        endforeach;
        
        $message->save();
        
        return $message;
    }
    
    public function saveGroupSubscriberFromArray($values) {
        
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$message = $this->groupsubsTable->getProxy($values['id'])) {
            $message = $this->groupsubsTable->getRecord();
        }

        //$values['send_date'] = strlen($values['send_date']) ? $values['send_date'] : date('d/m/Y H:i');
        //$values['send_date'] = MF_Text::timeFormat($values['send_date'], 'Y-m-d H:i:s', 'd/m/Y H:i');
        
        $message->fromArray($values);

        $message->save();
        
        return $message;
    }
    
    public function getSubscriberById($id){
        return $this->subscriberTable->findOneBy('id', $id);         
    }
    
    
    public function getSubscribers(){
        $tab = $this->subscriberTable->findAll()->toArray();
        $ret = array();
        foreach($tab as $key => $item){
            $ret[$item['id']] = $item['name'].' '.$item['lastname'].' ('.$item['email'].')';
        }

        return $ret;
    }
       
    public function getGroups(){
        $tab = $this->groupTable->findAll()->toArray();
        $ret = array();
        foreach($tab as $key => $item){
            $ret[$item['id']] = $item['name'];
        }

        return $ret;
    }
    
    
    /**
     * 
     * @param int $id
     * @return array
     */
    public function getSubscribersByGroup($id){
        $res1 = $this->groupsubsTable->findBy('group_id', $id);
        $result = array();
        foreach($res1 as $val){
            $tmp = $this->getSubscriberById($val->subscriber_id);
            $result[$val->subscriber_id] = $tmp->name.' '.$tmp->lastname;
        }
        return $result;
    }
        
     public function getAllSubscribers(){
        return $this->subscriberTable->findAll();
    }
    
    public function removeSubscriber($value,$field = "id"){
        $subscriber = $this->subscriberTable->findOneBy($field, $value);
        if(is_object($subscriber)){   
            $subscriber->unlink('Groups');
            $subscriber->delete();
            return true;
        }
        else{
            return false;
        }
        
    }
    
    public function removeGroupSubscriberByGroup($id){
        $subs = $this->groupsubsTable->findBy('group_id', $id);
        $subs->delete();        
    }
     public function getFullSubscribersByGroup($id){
         $q = $this->subscriberTable->createQuery('s');
         $q->addSelect('s.*');
         $q->addSelect('sg.*');
         $q->leftJoin('s.Groups sg');
         $q->addWhere('sg.id = ?',$id);
         return $q->execute(array(),Doctrine_Core::HYDRATE_ARRAY);
      
    }
    
     public function getAllSubscribersToSend(){
         $q = $this->subscriberTable->createQuery('s');
         $q->addSelect('s.*');
	 $q->addWhere('s.send = 0');
         return $q->execute(array(),Doctrine_Core::HYDRATE_RECORD);
      
    }
}

