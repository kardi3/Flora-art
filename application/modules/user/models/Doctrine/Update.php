<?php

/**
 * User_Model_Doctrine_Update
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Admi
 * @subpackage User
 * @author     Andrzej Wilczyński <and.wilczynski@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class User_Model_Doctrine_Update extends User_Model_Doctrine_BaseUpdate
{
    const TYPE_EMAIL = 'email';
    const TYPE_PASSWORD = 'password';
    
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
    
    public function setToken($token) {
        $this->_set('token', $token);
    }
    
    public function getToken() {
        return $this->_get('token');
    }
    
    public function setType($type) {
        $this->_set('type', $type);
    }
    
    public function getType() {
        return $this->_get('type');
    }
    
    public function setValue($value) {
        $this->_set('value', $value);
    }
    
    public function getValue() {
        return $this->_get('value');
    }
}