<?php
/**
 * Operation with array
 */

declare(strict_types = 1);

namespace Vgip\Gip\Common;

use Vgip\Gip\Exception\DomainException;
use Vgip\Gip\Common\Str;

class Arr
{
    private $prefix = null;
    
    private $variableNameConversionMethodWhiteList = [
        'entity',
        'prefix',
        'conformity_array',
    ];
    
    
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
    
    public function updateArrayKeyByArray(array $conformityKey, $new_key ) 
    {

    if( ! array_key_exists( $old_key, $array ) )
        return $array;

    $keys = array_keys( $array );
    $keys[ array_search( $old_key, $keys ) ] = $new_key;

    return array_combine( $keys, $array );
    }

    public function getConformityArray(array $entity, string $variableNameConversionMethod)
    {
        if (!in_array($variableNameConversionMethod, $this->variableNameConversionMethodWhiteList, true)) {
            throw new DomainException('Variable name conversion method "'.$variableNameConversionMethod.'" not exists.');
        }
        
        $funcName = 'convertEntityToVariableNameBy'.Str::convertLowerSnakeCaseToUpperCamelCase($variableNameConversionMethod);
        $conformityArray = $this->$funcName($entity, $variableNameConversionMethod);
        
        return $conformityArray;
    }
    
    public function convertEntityToVariableNameByEntity(array $entity)
    {
        $conformityArray = [];
        
        foreach ($entity AS $keyName) {
            $conformityArray[$keyName] = $keyName;
        }
        
        return $conformityArray;
    }
    
    public function convertEntityToVariableNameByPrefix(array $entity)
    {
        $conformityArray = [];
        
        if (null === $this->prefix) {
            throw new DomainException('Prefix not set');
        }
        
        foreach ($entity AS $keyName) {
            $conformityArray[$keyName] = $this->prefix.$keyName;
        }
        
        return $conformityArray;
    }
    
    public function convertEntityToVariableNameByConformityArray(array $entity)
    {
        $conformityArray = [];
        
        foreach ($entity AS $entityKeyName => $varName) {
            if (!is_string($entityKeyName)) {
                throw new DomainException('Entity name with variable name "'.$varName.'" can be only a string.');
            }
            $conformityArray[$entityKeyName] = $varName;
        }
        
        return $conformityArray;
    }
}
