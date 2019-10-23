<?php

declare(strict_types = 1);

namespace Vgip\Gip\Config;

use Vgip\Gip\Common\NamingConversion;
use Vgip\Gip\Config\Storage\Storage;
use Vgip\Gip\Exception\InvalidArgumentException;
use Vgip\Gip\Exception\OutOfBoundsException;
use stdClass;

trait TraitProperty
{
    public function setAll($storage, string $keyType = '')
    {
        $config                     = $this->getConfigToSetterAndValidateStorageType($storage);
        $arrayKeyConversionObject   = $this->getConfigKeyConversionObjectToSetterMethod($storage, $keyType);
        $exception                  = [];

        foreach ($config as $property => $value) {
            $propertySetterMethodName       = $arrayKeyConversionObject->convert($property);
            $propertySetterMethodNameFull   = 'set'.$propertySetterMethodName;
            if (method_exists($this, $propertySetterMethodNameFull)) {
                $this->$propertySetterMethodNameFull($value);
            } else {
                $exception[] = $property;
            }
        }
        if (count($exception) > 0) {
            throw new OutOfBoundsException('Config key(s): '. implode(', ', $exception).' are not the keys of this configuration');
        }
    }
    
    public function getAllAsObject() : stdClass
    {
        $properties = (object)get_object_vars($this);
        
        return $properties;
    }
    
    public function getAll(string $keyNamingType = 'LowerSnakeCase') : array
    {
        $res = [];
        
        $properties = get_object_vars($this);
        
        $arrayKeyConversion = new NamingConversion('LowerCamelCase', $keyNamingType);
        
        foreach ($properties AS $key => $value) {
            $convertedKey = $arrayKeyConversion->convert($key);
            $ret[$convertedKey] = $value;
        }
        
        return $ret;
    }
    
    private function getConfigToSetterAndValidateStorageType($storage)
    {
        $config = [];
        if ($storage instanceof stdClass) {
            $config = $storage;
        } else if ($storage instanceof Storage) {
            $config = $storage->getConfig();
        } elseif (is_array($storage)) {
            $config = $storage;
        } else {
            throw new InvalidArgumentException('Argument 1 passed to setAll() must be an instance of available classes (stdClass or Storage) or associative array (key => value)');
        }
        
        return $config;
    }
    
    private function getConfigKeyConversionObjectToSetterMethod($storage, $keyType)
    {
        $keyTypeDetected = 'LowerCamelCase';
        
        if ($storage instanceof stdClass) {
            $keyTypeDetected = (empty($keyType)) ? 'LowerCamelCase' : $keyType;
        } else if ($storage instanceof Storage) {
            $keyTypeDetected = 'LowerCamelCase';
        } elseif (is_array($storage)) {
            $keyTypeDetected = (empty($keyType)) ? 'LowerSnakeCase' : $keyType;
        } else {
            throw new InvalidArgumentException('Argument 1 passed to setAll() must be an instance of available classes (stdClass or Storage) or associative array (key => value)');
        }
        
        try {
            $configKeyConversionObject = new NamingConversion($keyTypeDetected, 'UpperCamelCase');
        } catch (\Throwable $e) {
            throw new InvalidArgumentException('Property names conversion error. '.$e);
        }
        
        
        return $configKeyConversionObject;
    }
}
