<?php
/* 
 * Convert internal data to safe (escaped) mysql data
 */

namespace Vgip\Gip\Db\Mysql;

use Vgip\Gip\Common\Str;
use Vgip\Gip\Entity\Group\Config AS EntityGroupConfig;
use Vgip\Gip\Db\Mysql\TableConfig;
use Vgip\Gip\Exception\DomainException;


class InternalToDbDataConverter
{
    /**
     * MysqlPlaceholder object
     * 
     * @var object 
     */
    private $db;
    
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
     * Converter special internal types: datetieme timezone and etc.
     * 
     * @var object 
     */
    private $typeSpecialConverter;

    
    public function __construct(MysqlPlaceholder $db, TableConfig $tableConfig, EntityGroupConfig $entityGroupConfig, object $typeSpecialConverter)
    {
        $this->db                               = $db;
        $this->tableConfig                      = $tableConfig;
        $this->entityGroupConfig                = $entityGroupConfig;
        $this->typeSpecialConverter             = $typeSpecialConverter;
    }
    
    /**
     * Convert intarnal data to safe for DB
     * 
     * If $setMissingAsDefault === true, set all default value for missing property in $data
     * Else - convert only data from $data
     * 
     * @param array $data
     * @param bool $setMissingAsDefault
     * @return array
     */
    public function convert(array $data, bool $setMissingAsDefault = true) : array
    {
        $dataDb = [];
        
        foreach ($this->tableConfig->getValueDefault() AS $key => $value) {
            $dataExists = false;
            /** $columnName = entity name, if not - get column name for current entity from dir */
            $columnName = $this->tableConfig->getColumnFromProperty($key); 
            if (array_key_exists($key, $data)) {
                $dataDb[$columnName] = $data[$key];
                $dataExists = true;
            } else if (true === $setMissingAsDefault) {
                $dataDb[$columnName] = $value;
                $dataExists = true;
            }
            if (true === $dataExists) {
                $typeSpecial = $this->entityGroupConfig->getTypeSpecialByKey($key);
                if (null !== $typeSpecial) {
                    $funcName = 'convert'.Str::convertLowerSnakeCaseToUpperCamelCase($typeSpecial);
                    $dataDb[$columnName] = $this->typeSpecialConverter->$funcName($dataDb[$columnName]);
                }
                
                $type = $this->entityGroupConfig->getTypeByKey($key);
                $dataDb[$columnName] = $this->db->escapeByType($dataDb[$columnName], $type);
            }
        }
        
        return $dataDb;
    }
    
    /**
     * Convert safe data for sql set (insert, update)
     * 
     * Example: UPDATE table SET $res
     * 
     * @param array $data
     * @return string
     */
    public function convertArrayToSqlSet(array $data) : string
    {
        foreach ($data AS $key => $value) {
            $setBlock[] = '`'.$key.'` = '.$value;
        }
        $setBlockSql = join(", \n", $setBlock);
        
        $res = $setBlockSql;
        
        return $res;
    }
    
    /**
     * Convert safe data for sql (insert)
     * 
     * Example: INSERT INTO table (columnName1, columnName2, ...) VALUES (val1, val2, ...)
     *          INSERT INTO table $res
     * 
     * @param array $data
     * @return string
     */
    public function convertArrayToSqlValue(array $data) : string
    {
        $values = join(', ', $data);
        
        foreach ($data AS $key => $value) {
            $columnName[] = '`'.$key.'`';
        }
        $columnNames = join(', ', $columnName);
        
        $res = '('.$columnNames.') VALUES ('.$values.')';
        
        return $res;
    }
    
    /**
     * Generate "WHERE" condition from safe sql values
     * 
     * @param type $key - column name
     * @param type $value - value
     * @param type $condition - condiion
     * @return string
     * @throws DomainException
     */
    public function convertPropertyToSqlWhere($key, $value, $condition = '=')
    {
        if ('=' === $condition) {
            if ($value === 'NULL') {
                $string = '`'.$key.'` IS NULL';
            } else {
                $string = '`'.$key.'` '.$condition.' '.$value;
            }
        } else if ('!=' === $condition) {
            if ($value === 'NULL') {
                $string = '`'.$key.'` IS NOT NULL';
            } else {
                $string = '`'.$key.'` '.$condition.' '.$value;
            }
        } else {
            if ($value === 'NULL') {
                throw new DomainException('Unacceptable value "null" for "'.$condition.'" in sql "where"');
            } else {
                $string = '`'.$key.'` '.$condition.' '.$value;
            }
        }
        
        return $string;
    }
}
