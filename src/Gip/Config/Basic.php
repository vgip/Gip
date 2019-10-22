<?php
/**
 * Common framework config file 
 */

declare(strict_types = 1);

namespace Vgip\Gip\Config;

use Vgip\Gip\Config\TraitProperty;


class Basic implements InterfaceConfig
{
    use TraitProperty;
    
    private $storagePublic          = null;
    
    private $storagePrivate         = null;
    
    private $storageModule          = null;
    
    private $hostname               = null;
    
    private $url                    = null;
    
    /**
     * Main internal script timezone
     * 
     * Timezone list http://php.net/manual/en/timezones.php or 
     * @var string
     */
    private $timezone               = null;
    

    public function setTimezone(string $timezone) : void
    {
        $this->timezone = $timezone;
    }
    
    public function getTimezone() : string
    {
        return $this->timezone;
    }

    public function setStoragePublic(string $storagePublic) : void
    {
        $this->storagePublic = $storagePublic;
    }

    public function getStoragePublic() : string
    {
        return $this->storagePublic;
    }
    
    public function setStoragePrivate($storagePrivate) : void
    {
        $this->storagePrivate = $storagePrivate;
    }

    public function getStoragePrivate() : string
    {
        return $this->storagePrivate;
    }
    
    public function setStorageModule($storageModule) : void
    {
        $this->storageModule = $storageModule;
    }
    
    public function getStorageModule() : string
    {
        return $this->storageModule;
    }
    
    public function setHostname($hostname) : void
    {
        $this->hostname = $hostname;
    }

    public function getHostname() : string
    {
        return $this->hostname;
    }
    
    public function setUrl($url) : void
    {
        $this->url = $url;
    }

    public function getUrl() : string
    {
        return $this->url;
    }
}
