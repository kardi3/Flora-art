<?php

/**
 * Default_Model_LanguageInterface
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
interface Default_Model_LanguageInterface {
    
    public function setId($id);
    
    public function getId();
    
    public function setName($name);
    
    public function getName();
    
    public function setActive($active = true);
    
    public function isActive();
    
    public function setDefault($default = true);
    
    public function isDefault();
    
    public function setAdmin($admin = true);
    
    public function isAdmin();
}

