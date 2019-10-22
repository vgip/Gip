<?php
/**
 * Storage config for tables
 */

declare(strict_types = 1);

namespace Vgip\Gip\Db\Mysql;

use Vgip\Gip\Common\Singleton;
use Vgip\Gip\Config\Storage\ArrayStorage;
use Vgip\Gip\Exception\DomainException;


class StorageTableConfig
{
    use Singleton;
    
    private $storage = [];
    
    private $pathModule = [];
   

    public function setPathModule(string $pathModule) 
    {
        $this->pathModule = $pathModule;
    }
    
    public function getConfigByTable(string $module, string $tableName) : TableConfig
    {
        if (!isset($this->storage[$tableName])) {
            $this->setConfigFromPathToArrayStorageData($module, $tableName);
        }
        
        return $this->storage[$tableName];
    }
    
    public function setConfigByModule($module)
    {
        $configStoragePath = join(DIRECTORY_SEPARATOR, [$this->pathModule, $module, 'config', 'db', 'table']);
        $table = scandir($configStoragePath);
        foreach ($table AS $tableFile) {
            if ($tableFile === '..' OR $tableFile === '.') {
                continue;
            }
            $tableName = mb_substr($tableFile, 0, -4);
            if (!isset($this->storage[$tableName])) {
                $this->setConfigFromPathToArrayStorageData($module, $tableName);
            }
        }
    }

    private function setConfigFromPathToArrayStorageData(string $module, string $tableName)
    {
        $configPath = join(DIRECTORY_SEPARATOR, [$this->pathModule, $module, 'config', 'db', 'table', $tableName.'.php']);
        $configData = require $configPath;
        $configStorage = new ArrayStorage($configData);
        $config = new TableConfig($configStorage);
        $config->setAll($configStorage);
        $this->storage[$tableName] = $config;
    }
}
