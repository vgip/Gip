<?php
/**
 * Basic config for table
 */

declare(strict_types = 1);

namespace Vgip\Gip\Db\Mysql;

use Vgip\Gip\Config\TraitProperty;
use Vgip\Gip\Config\InterfaceConfig;
use Vgip\Gip\Exception\DomainException;


class TableConfig implements InterfaceConfig
{
    use TraitProperty;
    
    
    /**
     * Name internal entity for load config
     * 
     * @var string
     */
    private $entityGroupName          = '';
    
    /**
     * 
     * 
     * @var string 
     */
    private $propertyToTableConformity     = null;
    
    /**
     * List all table columns and default value for every column
     * 
     * Array propertyName => defalulValue
     * 
     * @var array 
     */
    private $valueDefault                   = [];
    
    /**
     * Conformity list if the column name does not match the property name
     * 
     * Array propertyName => columnName
     * 
     * @var array 
     */
    private $propertyToColumnConformity     = [];
    
    
    public function setEntityGroupName($entityGroupName) : void
    {
        $this->entityGroupName = $entityGroupName;
    }
    
    public function getEntityGroupName() : string
    {
        return $this->entityGroupName;
    }
    
    public function setPropertyToTableConformity($propertyToTableConformity) : void
    {
        $this->propertyToTableConformity = $propertyToTableConformity;
    }
    
    public function getPropertyToTableConformity() : string
    {
        return $this->propertyToTableConformity;
    }
    
    public function setValueDefault(array $valueDefault) : void
    {
        $this->valueDefault = $valueDefault;
    }
    
    public function getValueDefault() : array
    {
        return $this->valueDefault;
    }
    
    public function getValueDefaultByKey(string $key) : string
    {
        return $this->valueDefault[$key];
    }

    public function setPropertyToColumnConformity($propertyToColumnConformity) : void
    {
        $this->propertyToColumnConformity = $propertyToColumnConformity;
    }
    
    public function getPropertyToColumnConformity() : array
    {
        return $this->propertyToColumnConformity;
    }
    
    public function getColumnFromProperty(string $key) : string
    {
        if (array_key_exists($key, $this->propertyToColumnConformity)) {
            $res = $this->propertyToColumnConformity[$key];
        } else {
            $res = $key;
        }
        
        if (!array_key_exists($key, $this->valueDefault)) {
            throw new DomainException('Undefined property key "'.$key.'"');
        }
        
        return $res;
    }
    
    public function getPropertyFromColumn(string $key) : string
    {
        $search = array_search($key, $this->propertyToColumnConformity);
        if (false === $search) {
            $res = $key;
        } else {
            $res = $search;
        }
        
        if (!array_key_exists($res, $this->valueDefault)) {
            throw new DomainException('Undefined property key "'.$res.'"');
        }
        
        return $res;
    }
    
    public function getColumnAllAsArray() : array
    {
        $columns = [];
        foreach ($this->valueDefault AS $entityName => $val) {
            $columns[] = $this->getColumnFromProperty($entityName);
        }
        
        return $columns;
    }
    
    public function getColumnAllAsString() : string
    {
        $columns = $this->getColumnAllAsArray();
        
        $columnsStr = join(',', $columns);
        
        return $columnsStr;
    }
}
