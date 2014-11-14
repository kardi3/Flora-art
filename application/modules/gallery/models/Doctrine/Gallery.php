<?php

/**
 * Gallery_Model_Doctrine_Gallery
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Admi
 * @subpackage Gallery
 * @author     Michał Folga <michalfolga@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Gallery_Model_Doctrine_Gallery extends Gallery_Model_Doctrine_BaseGallery
{
    public static $types = array(
//        'homepage' => 'Homepage',
//        'terms' => 'Terms',
//        'faq' => 'FAQ'
    );
    
    
    public static function getAvailableTypes() {
        return self::$types;
    }
    
    public function setId($id) {
        $this->_set('id', $id);
    }
    
    public function getId() {
        return $this->_get('id');
    }
    
    public function setUserId($userId) {
        $this->_set('user_id', $userId);
    }
    
    public function getUserId() {
        return $this->_get('user_id');
    }
    
    public function setType($type) {
        $this->_set('type', $type);
    }
    
    public function getType() {
        return $this->_get('type');
    }
    
    public function setStatus($status) {
        $this->_set('status', $status);
    }
    
    public function getStatus() {
        return $this->_get('status');
    }
    public function getMetatagId() {
        return $this->_get('metatag_id');    
    }
    
    
    public function setUp() {
        parent::setUp();
        $this->hasOne('Default_Model_Doctrine_Metatag as Metatag', array(
            'local' => 'metatag_id',
            'foreign' => 'id'
        )); 
        
          $this->hasOne('Media_Model_Doctrine_Photo as PhotoRoot', array(
            'local' => 'photo_root_id',
            'foreign' => 'id'
        ));
	  
	   $this->hasMany('Media_Model_Doctrine_Photo as Photos', array(
            'local' => 'photo_root_id',
            'foreign' => 'root_id'
        ));
    }
}