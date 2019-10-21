<?php
/**
 * Db config
 */

declare(strict_types = 1);

namespace Vgip\Gip\Log\Storage\Db;

use Vgip\Gip\Config\TraitProperty;
use Vgip\Gip\Config\InterfaceConfig;


class Config implements InterfaceConfig
{
    use TraitProperty;
    
    private $dbHandler          = null;
    
    private $table              = null;
    
    
    public function setDbHandler(object $dbHandler) : void
    {
        $this->dbHandler = $dbHandler;
    }
    
    public function getDbHandler() : object
    {
        return $this->dbHandler;
    }
    
    public function setTable(string $table) : void
    {
        $this->table = $table;
    }
    
    public function getTable() : string
    {
        return $this->table;
    }
}
