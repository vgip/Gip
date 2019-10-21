<?php
/* 
 * Convert data by type
 */
declare(strict_types = 1);

namespace Vgip\Gip\Entity\Handler;

use Vgip\Gip\Common\Str;
use Vgip\Gip\Entity\Group\Config AS EntityGroupConfig;


class DataConverter
{
    /**
     * Entity group config
     * 
     * @var object 
     */
    private $entityGroupConfig;
    
    
    /**
     * Converter standart type to internal
     * 
     * @var object 
     */
    private $typeConverter;


    /**
     * Converter special types: datetieme timezone and etc.
     * 
     * @var object 
     */
    private $typeSpecialConverter;


    public function __construct(EntityGroupConfig $entityGroupConfig, object $type, object $typeSpecialConverter)
    {
        $this->entityGroupConfig                = $entityGroupConfig;
        $this->typeConverter                    = $type;
        $this->typeSpecialConverter             = $typeSpecialConverter;
    }
    
    public function convertEntityData($data) : array
    {
        $dataInternal = [];
        
        foreach ($data AS $entityName => $value) {
            $valueInternal = $this->convertEntityValue($entityName, $value);
            
            $dataInternal[$entityName] = $valueInternal;
        }
        
        return $dataInternal;
    }
    
    public function convertEntityValue(string $entityName, ?string $value)
    {
        $internalType = $this->entityGroupConfig->getTypeByKey($entityName);
            
        $valueInternal = $this->typeConverter->convertByType($internalType, $value);
            
        $typeSpecial = $this->entityGroupConfig->getTypeSpecialByKey($entityName);
        if (null !== $typeSpecial) {
            $funcName = 'convert'.Str::convertLowerSnakeCaseToUpperCamelCase($typeSpecial);
            $valueInternal = $this->typeSpecialConverter->$funcName($valueInternal);
        } 

        return $valueInternal;
    }
}
