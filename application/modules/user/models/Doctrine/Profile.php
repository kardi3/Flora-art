<?php

/**
 * User_Model_Doctrine_Profile
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Admi
 * @subpackage User
 * @author     Andrzej Wilczyński <and.wilczynski@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class User_Model_Doctrine_Profile extends User_Model_Doctrine_BaseProfile
{
    public static $photoDimensions = array(
        '30x30' => 'Thumbnail',
        '40x40' => 'Admin thumbnail',
        '200x200' => 'Main photo',
        '122x122' => 'Search thumbnail'
    );
    
    public static function getProfilePhotoDimensions() {
        return self::$photoDimensions;
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
        return $this->_get('id');
    }
    
    public function setAbout($about) {
        $this->_set('about', $about);
    }
    
    public function getAbout() {
        return $this->_get('about');
    }
    
    public function setAddress($address) {
        $this->_set('address', $address);
    }
    
    public function getAddress() {
        return $this->_get('address');
    }
    
    public function setPostalCode($postalCode) {
        $this->_set('postal_code', $postalCode);
    }
    
    public function getPostalCode() {
        return $this->_get('postal_code');
    }
    
    public function setPhone($phone) {
        $this->_set('phone', $phone);
    }
    
    public function getPhone() {
        return $this->_get('phone');
    }
    
    public function setCity($city) {
        $this->_set('city', $city);
    }
    
    public function getCity($city) {
        $this->_set('city', $city);
    }
    
    public function setUp() {
        $this->hasOne('Media_Model_Doctrine_Photo as PhotoRoot', array(
            'local' => 'photo_root_id',
            'foreign' => 'id'
        ));
        $this->hasMany('Media_Model_Doctrine_Photo as Photos', array(
            'local' => 'photo_root_id',
            'foreign' => 'root_id'
        ));
        $this->hasOne('Default_Model_Doctrine_Province as Province', array(
            'local' => 'province_id',
            'foreign' => 'id'
        ));
        $this->hasOne('Default_Model_Doctrine_City as City', array(
            'local' => 'city_id',
            'foreign' => 'id'
        ));
        parent::setUp();
    }
}