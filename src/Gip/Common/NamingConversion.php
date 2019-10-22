<?php
/**
 * Conversion array keys to properties in "lowerCamelCase" style
 */

declare(strict_types = 1);

namespace Vgip\Gip\Common;

use Vgip\Gip\Exception\DomainException;

class NamingConversion
{
    private $typeFrom = '';
    
    private $typeTo   = '';
    
    const LOWER_SNAKE_CASE = 'LowerSnakeCase';
    
    const LOWER_CAMEL_CASE = 'LowerCamelCase';
    
    const UPPER_CAMEL_CASE = 'UpperCamelCase';

    private $typeValid = [
        1 => 'LowerSnakeCase',
        2 => 'LowerCamelCase',
        3 => 'UpperCamelCase',
    ];
    
    public function __construct(string $typeFrom, string $typeTo)
    {
        $this->typeFrom = $this->validateType($typeFrom);
        $this->typeTo   = $this->validateType($typeTo);
    }
    
    public function convert($value)
    {
        $conversionFunctionName = 'convert'.$this->typeFrom.'To'.$this->typeTo;
        
        $convertedValue = Str::$conversionFunctionName($value);
        
        return $convertedValue;
    }
    
    private function validateType(string $type) : string
    {
        $key = array_search($type, $this->typeValid, true);
        if (false === $key) {
            throw new DomainException('Type "'.$type.'" unknown, use one of this types: '.implode(', ', $this->typeValid));
        } else {
            $type = $this->typeValid[$key];
        }
        
        return $type;
    }
}
