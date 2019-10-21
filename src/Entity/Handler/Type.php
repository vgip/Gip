<?php
/**
 * Convert incoming data type of values (sting) to internal format
 */

declare(strict_types = 1);

namespace Vgip\Gip\Entity\Handler;

use Vgip\Gip\Common\Str;

class Type
{
    public function convertByType(string $type, string $value = null)
    {
        if (null === $value) {
            $res = null;
        } else {
            $convertToTypeFuncName = 'convertTo'.Str::convertLowerSnakeCaseToUpperCamelCase($type);
            $res = $this->$convertToTypeFuncName($value);
        }
        
        return $res;
    }

    public function convertToInt($value) : int
    {
        $val = (null === $value) ? null : (int)$value;
        
        return $val;
    }
    
    public function convertToString($value)
    {
        $val = (null === $value) ? null : (string)$value;
        
        return $val;
    }
    
    public function convertToBool($value)
    {
        $val = (null === $value) ? null : (bool)$value;
        
        return $val;
    }
}
