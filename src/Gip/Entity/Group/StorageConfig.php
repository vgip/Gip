<?php
/**
 * Storage config
 * 
 * Get and storage all config in array[$module][$group]
 * 
 */

declare(strict_types = 1);

namespace Vgip\Gip\Entity\Group;

use Vgip\Gip\Common\Singleton;
use Vgip\Gip\Config\Storage\ArrayStorage;
use Vgip\Gip\Exception\DomainException;


class StorageConfig
{
    use Singleton;
    
    private $storage = [];
    
    private $pathModule = [];
   

    public function setPathModule(string $pathModule) 
    {
        $this->pathModule = $pathModule;
    }
    
    public function getConfigByGroup(string $module, string $entityGroupName) : Config
    {
        if (!isset($this->storage[$module][$entityGroupName])) {
            $this->setConfigFromPathToArrayStorageData($module, $entityGroupName);
        }
        
        return $this->storage[$module][$entityGroupName];
    }
    
    public function setConfigByModule($module)
    {
        $configStoragePath = join(DIRECTORY_SEPARATOR, [$this->pathModule, $module, 'config', 'entity', 'group']);
        $table = scandir($configStoragePath);
        foreach ($table AS $tableFile) {
            if ($tableFile === '..' OR $tableFile === '.') {
                continue;
            }
            $entityGroupName = mb_substr($tableFile, 0, -4);
            if (!isset($this->storage[$module][$entityGroupName])) {
                $this->setConfigFromPathToArrayStorageData($module, $entityGroupName);
            }
        }
    }
    
    public function setConfigByArray(string $module, string $entityGroupName, array $data)
    {
        $configStorage  = new ArrayStorage($data);
        $config         = new Config($configStorage);
        $config->setAll($configStorage);
        $this->storage[$module][$entityGroupName] = $config;
    }

    private function setConfigFromPathToArrayStorageData(string $module, string $entityGroupName)
    {
        $configPath = join(DIRECTORY_SEPARATOR, [$this->pathModule, $module, 'config', 'entity', 'group', $entityGroupName.'.php']);
        $configData = require $configPath;
        $configStorage = new ArrayStorage($configData);
        $config = new Config($configStorage);
        $config->setAll($configStorage);
        $this->storage[$module][$entityGroupName] = $config;
    }
}
