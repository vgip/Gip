<?php

declare(strict_types = 1);

namespace Vgip\Gip\Config\Storage;

use Vgip\Gip\Common\Str;
use Vgip\Gip\Common\NamingConversion;
use Vgip\Gip\Exception\OutOfBoundsException;

trait TraitArray
{
    public function getObject(array $data = [], string $keyNamingType = 'LowerSnakeCase') : object
    {
        $arrayKeyConversion = new NamingConversion($keyNamingType, 'LowerCamelCase');
        
        $convertedArray = [];
        $exeptionMessage = [];
        foreach ($data as $key => $value) {
            $keyString = (string)$key;
            if (false === Str::isValidPropertyName($keyString)) {
               $exeptionMessage[] = '"'.$key.'"';
            }
            $convertedKey = $arrayKeyConversion->convert($keyString);
            $convertedArray[$convertedKey] = $value;
        }
        
        if (count($exeptionMessage) > 0) {
            $mess = 'Invalid array keys found that cannot be converted to a method name(s): '.implode(', ', $exeptionMessage).'. Please read http://php.net/manual/language.variables.basics.php and rename this key(s).';
            throw new OutOfBoundsException($mess);
        }
        
        $obj = (object) $convertedArray;
        
        return $obj;
    }
    
//    public function setAll(string $keyNamingType = '')
//    {
//        $arrayKeyConversion = new NamingConversion('LowerCamelCase', $keyNamingType);
//        
//        $properties = get_object_vars($this);
//        
//        $res = [];
//        foreach ($properties AS $property => $value) {
//            $convertedProperty = $arrayKeyConversion->convert($property);
//            $res[$convertedProperty] = $value;
//        }
//        
//        print_r($res);
//        
//        return $properties;
//    }
}

