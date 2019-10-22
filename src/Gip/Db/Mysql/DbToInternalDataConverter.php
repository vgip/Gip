<?php
/* 
 * Convert mysql data to internal format
 */

namespace Vgip\Gip\Db\Mysql;

use Vgip\Gip\Common\Str;
use Vgip\Gip\Entity\Group\Config AS EntityGroupConfig;
use Vgip\Gip\Db\Mysql\TableConfig;


class DbToInternalDataConverter
{
    /**
     * Table configuration: colum, etc
     * 
     * @var object 
     */
    private $tableConfig;

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


    public function __construct(TableConfig $tableConfig, EntityGroupConfig $entityGroupConfig, object $type, object $typeSpecialConverter)
    {
        $this->tableConfig                      = $tableConfig;
        $this->entityGroupConfig                = $entityGroupConfig;
        $this->typeConverter                    = $type;
        $this->typeSpecialConverter             = $typeSpecialConverter;
    }
    
    public function convertDbRowToInternal($data) : array
    {
        $dataInternal = [];
        
        foreach ($data AS $columnName => $value) {
            $entityName  = $this->tableConfig->getPropertyFromColumn($columnName);
            
            $internalType = $this->entityGroupConfig->getTypeByKey($entityName);
            
            $valueInternal = $this->typeConverter->convertByType($internalType, $value);
            
            $typeSpecial = $this->entityGroupConfig->getTypeSpecialByKey($entityName);
            if (null !== $typeSpecial) {
                $funcName = 'convert'.Str::convertLowerSnakeCaseToUpperCamelCase($typeSpecial);
                $dataInternal[$entityName] = $this->typeSpecialConverter->$funcName($valueInternal);
            } else {
                $dataInternal[$entityName] = $valueInternal;
            }
        }
        
        return $dataInternal;
    }
    
    public function convertDbAllToInternal($data) : array
    {
        $dataInternal = [];
        
        foreach ($data AS $key => $dataRow) {
            $dataInternal[$key] = $this->convertDbRowToInternal($dataRow);
        }
        
        return $dataInternal;
    }
}
