<?php

/**
 * PasswordEncoder
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class User_PasswordEncoder {
    
    public function encode($password, $salt = '', $algo = 'md5') {
        return hash($algo, $password . $salt);
    }
}

