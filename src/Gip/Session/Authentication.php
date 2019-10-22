<?php

declare(strict_types = 1);

namespace Vgip\Gip\Session;

use Vgip\Gip\Exception\DomainException;

class Authentication
{
    private static $additionalProperty = ['user_id' => true];

    public static function isAuthenicated() : bool
    {
        if (isset($_SESSION['user_id'])) {
            $res = true;
        } else {
            $res = false;
        }
            
    return $res;
    }
    
    public static function setAuthentication(array $data = []) : bool
    {
        $propertyRequired = [];
        foreach (self::$additionalProperty AS $propertyName => $value) {
            if (!isset($data[$propertyName])) {
                $propertyRequired[] = $propertyName;
            } else {
                $_SESSION[$propertyName] = $data[$propertyName];
            }
        }
        
        if (count($keysRequired) > 0) {
            throw new DomainException('Authentication key(s) not set: '.join(', ', $propertyRequired));
        }
        
        return true;
    }
    
    public static function destroyAuthentication() : bool
    {
        foreach (self::$additionalProperty AS $propertyName => $value) {
            if (isset($_SESSION[$propertyName])) {
                unset($_SESSION[$propertyName]);
            }
        }
        
        return true;
    }
    
    public static function setAdditionalProperty(array $additionalProperty) : void
    {
        self::$additionalProperty = array_merge(self::$additionalProperty, $additionalProperty);
    }
}
