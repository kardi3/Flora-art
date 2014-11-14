<?php
class MF_Controller_Plugin_Mobile extends Zend_Controller_Plugin_Abstract
{
    
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $req)
    {
        
                
                Zend_Layout::getMvcInstance()->setLayout('mobile');
                return;
            
    } 
}