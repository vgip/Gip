<?php
/**
 * Log config
 */

declare(strict_types = 1);

namespace Vgip\Gip\Log;

use Vgip\Gip\Config\TraitProperty;
use Vgip\Gip\Config\InterfaceConfig;
use Vgip\Gip\Common\Singleton;
//use Vgip\Gip\Exception\DomainException;


class Config implements InterfaceConfig
{
    use Singleton;
    
    use TraitProperty;
    
    private $storageType            = null;
    
    private $storageConfig          = null;
    
    
    public function setStorageType(string $storageType) : void
    {
        $this->storageType = $storageType;
    }
    
    public function getStorageType() : string
    {
        return $this->storageType;
    }
    
    public function setStorageConfig(object $storageConfig) : void
    {
        $this->storageConfig = $storageConfig;
    }
    
    public function getStorageConfig() : object
    {
        return $this->storageConfig;
    }
}
