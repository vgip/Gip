<?php
/**
 * Operation with array
 */

declare(strict_types=1);

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
    
    /**
     * Updating (replacing) an array key without changing the order 
     * of the keys and values and whithout foreach
     * 
     * https://stackoverflow.com/Questions/240660/in-PHP-how-do-you-change-the-key-of-an-array-element
     * 
     * @param array $array
     * @param string $keyOld
     * @param string $keyNew
     * @return array
     */
    public function updateArrayKeyByString(array $array, string $keyOld, string $keyNew): array
    {
        if (!array_key_exists($keyOld, $array)) {
            return $array;
        }

        $keys = array_keys($array);
        $foundKey = array_search($keyOld, $keys);
        $keys[$foundKey] = $keyNew;
        
        $res = array_combine($keys, $array);

        return $res;
    }
}
