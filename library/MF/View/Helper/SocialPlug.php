<?php 
class MF_View_Helper_SocialPlug extends Zend_View_Helper_Abstract
{
    public function socialPlug($url=null, $type = 'big') {
        if($url==null):
            $url = $this->view->url();
        endif;
        return $this->view->partial('_social-plug.phtml', array('url' => $url, 'type' => $type));
    }
}