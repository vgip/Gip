<?php

declare(strict_types = 1);

namespace Vgip\Gip\Log\Storage\Db;

use Vgip\Gip\Exception\DomainException;
use Vgip\Gip\Log\Storage;

class Db extends Storage
{
    private $dbHandler;
    
    private $table = '';
    
    private $propertyType = [
        'id'            => 'int',
        'module'        => 'string',
        'action'        => 'string',
        'level'         => 'string',
        'message'       => 'string',
        'context'       => 'string',
        'ip'            => 'string',
        'date_create'   => 'string',
    ];
    
    public function __construct($config)
    {
        $this->dbHandler    = $config->getDbHandler();
        $this->table        = $this->dbHandler->getTableNameFull($config->getTable());
    }

    public function log($level, $message, ?array $context = [])
    {
        $values = $this->prepareDataToStorage($level, $message, $context);
        unset($values['id']);

        foreach ($values AS $key => $val) {
            $vE[$key] = $this->dbHandler->escapeByType($val, $this->propertyType[$key]);
        }
        
        $query = 'INSERT INTO '.$this->table.'
                  (id, module, action, level, message, context, ip, date_create)
                  VALUES (DEFAULT, '.$vE['module'].', '.$vE['action'].', '.$vE['level'].', '.$vE['message'].', '.$vE['context'].', '.$vE['ip'].', '.$vE['date_create'].')';
        $this->dbHandler->query($query);
    }
    
    
    public function getRowLast($rowCount = 100)
    {
        $columnName = implode(',', $this->getColumnNameAll());
        $rowCount = (int)$rowCount;
        
        $query = 'SELECT '.$columnName.'
                  FROM '.$this->table.' 
                  ORDER BY date_create DESC
                  LIMIT '.$rowCount.'
                  ';
        //$dataRaw = $this->dbHandler->getAll($query);

        $data = [];
        foreach ($dataRaw AS $number => $raw) {
            $data[$number] = $this->convertDataFromStorage($raw);
        }

        return $data;
    }
    
    
    public function getRowAll()
    {
        $query = 'SELECT count(id) AS count_id
                  FROM '.$this->table.' 
                  ';
        //$dataRaw = $this->dbHandler->getAll($query);
        $count = $dataRaw[0]['count_id'];
        
        return $count;
    }
    
    
    public function clearAll()
    {
        $query = 'DELETE '.$this->table.' 
                  FROM '.$this->table.' 
                  WHERE 1 = 1';
        //$result = $this->dbHandler->query($query);
        //$count = $this->dbHandler->affectedRows();
        
        return $count;
    }

    
    private function getColumnNameAll()
    {
        $columnNameList = [];
        foreach ($this->propertyType AS $columnName => $type) {
            $columnNameList[] = $columnName;
        }
        
        return $columnNameList;
    }
}
