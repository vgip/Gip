<?php

declare(strict_types = 1);

namespace Vgip\Gip\Db\Mysql;

use Vgip\Gip\Exception\DomainException;

trait SetterTrait
{
    public function setInsertId(string $name, array $entityData, string $entityAutoincrementValue = 'id')
    {
        $this->insertId[$name] = (isset($entityData[$entityAutoincrementValue])) ? $entityData[$entityAutoincrementValue] : $this->db->insertId();
    }
    
    public function getInsertIdByKey(string $key) : ?int
    {
        if (!array_key_exists($key, $this->insertId)) {
            throw new DomainException('Insert id with key "$key" not set');
        } else if (!is_int($this->insertId[$key])) {
            throw new DomainException('Insert id is not int');
        } else if ($this->insertId[$key] < 1) {
            throw new DomainException('Insert id is less one');
        }
        
        return $this->insertId[$key];
    }
    
    public function setAffectedRows(string $name)
    {
        $this->affectedRows[$name] = $this->db->affectedRows();
    }
    
    public function getAffectedRowsByKey(string $key) : ?int
    {
        if (!array_key_exists($key, $this->affectedRows)) {
            throw new DomainException('Affected rows with key "$key" not set');
        } else if (!is_int($this->affectedRows[$key])) {
            throw new DomainException('Affected rows id is not int');
        }
        
        return $this->affectedRows[$key];
    }
}
