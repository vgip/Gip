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
    public function setAll(object $storage)
    {
        $arrayKeyConversion = new NamingConversion('LowerCamelCase', 'UpperCamelCase');
        
        if ($storage instanceof stdClass) {
            $config = $storage;
        } else if ($storage instanceof Storage) {
            $config = $storage->getConfig();
        } else {
            throw new InvalidArgumentException('Argument 1 passed to setAll() must be an instance of available classes (stdClass or Storage)');
        }
        $properties = get_class_vars(static::class);
        
        $exception = [];

        foreach ($config as $property => $value) {
            if (array_key_exists($property, $properties)) {
                $funcName = $arrayKeyConversion->convert($property);
                $setter = 'set'.ucfirst($funcName);
                $this->$setter($value);
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
}
