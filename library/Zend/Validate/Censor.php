<?php  
require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Censor extends Zend_Validate_Abstract
    {
        const CENSOR = 'censor';
     
        protected $_messageTemplates = array(
            self::CENSOR => "Usuń niecenzuralne zwroty"
        );
     
        public function isValid($value)
        {
            $this->_setValue($value);
            
            $settingsService = new Job_Service_Settings();
            $censorWords = $settingsService->getCensorWords();
            
            
            foreach($censorWords as $word):
                if(strstr($value,$word)):
                      $this->_error(self::CENSOR);
                      return false;
                endif;
                        
            endforeach;
           
            return true;
        }
    }