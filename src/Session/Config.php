<?php
/**
 * Session config 
 */

declare(strict_types = 1);

namespace Vgip\Gip\Session;

use DateTimeZone;
use Vgip\Gip\Config\TraitProperty;
use Vgip\Gip\Common\Singleton;
use Vgip\Gip\Exception\DomainException;

class Config
{
    use TraitProperty;
    use Singleton;
    
    /**
     * Session lifetime
     * 
     * @var int 
     */
    private $lifetime;
    
    private $regenerateSessionIdTime;
            
    private $sidBitsPerCharacter;
    
    private $sidLength;
    
    private $cookieHttponly;
    
    private $cookieSecure;
    
    private $useStrictMode;
    
    private $gcMaxlifetime;
    
    private $gcProbability;
    
    private $gcDivisor;
    
    private $lazyWrite;
    
    private $cookieLifetime;
    
    private $name;
    
    private $useCookies;
    
    private $useOnlyCookies;
    
    private $serializeHandler;
    
    /**
     * Internal Timezone
     * 
     * @var string 
     */
    private $timezone;
    
    
    public function setLifetime(int $lifetime) : void
    {
        $this->lifetime = $lifetime;
    }
    
    public function getLifetime() : int
    {
        return $this->lifetime;
    }

    public function setRegenerateSessionIdTime(int $regenerateSessionIdTime) : void
    {
        $this->regenerateSessionIdTime = $regenerateSessionIdTime;
    }
    
    public function getRegenerateSessionIdTime() : int
    {
        return $this->regenerateSessionIdTime;
    }

    public function setSidBitsPerCharacter(int $sidBitsPerCharacter) : void
    {
        $sidBitsPerCharacterAvailable = [4, 5, 6];
        if (in_array($sidBitsPerCharacter, $sidBitsPerCharacterAvailable, true)) {
            $this->sidBitsPerCharacter = $sidBitsPerCharacter;
        } else {
            throw new DomainException('Property $sidBitsPerCharacter can contain only valid values: '.join(', ', $sidBitsPerCharacterAvailable).'. See http://php.net/manual/en/session.configuration.php#ini.session.sid-bits-per-character');
        }
    }
    
    public function getSidBitsPerCharacter() : int
    {
        return $this->sidBitsPerCharacter;
    }

    public function setSidLength(int $sidLength) : void
    {
        $this->sidLength = $sidLength;
    }
    
    public function getSidLength() : int
    {
        return $this->sidLength;
    }

    public function setCookieHttponly(bool $cookieHttponly) : void
    {
        $this->cookieHttponly = $cookieHttponly;
    }
    
    public function getCookieHttponly()  : bool
    {
        return $this->cookieHttponly;
    }

    public function setCookieSecure(bool $cookieSecure) : void
    {
        $this->cookieSecure = $cookieSecure;
    }
    
    public function getCookieSecure() : bool
    {
        return $this->cookieSecure;
    }

    public function setUseStrictMode(bool $useStrictMode) : void
    {
        $this->useStrictMode = $useStrictMode;
    }
    
    public function getUseStrictMode() : bool
    {
        return $this->useStrictMode;
    }

    public function setGcMaxlifetime(int $gcMaxlifetime) : void
    {
        $this->gcMaxlifetime = $gcMaxlifetime;
    }
    
    public function getGcMaxlifetime() : int
    {
        return $this->gcMaxlifetime;
    }

    public function setGcProbability(int $gcProbability) : void
    {
        $this->gcProbability = $gcProbability;
    }
    
    public function getGcProbability() : int
    {
        return $this->gcProbability;
    }

    public function setGcDivisor(int $gcDivisor) : void
    {
        $this->gcDivisor = $gcDivisor;
    }
    
    public function getGcDivisor() : int
    {
        return $this->gcDivisor;
    }
    
    public function setLazyWrite(bool $lazyWrite) : void
    {
        $this->lazyWrite = $lazyWrite;
    }

    public function getLazyWrite() : bool
    {
        return $this->lazyWrite;
    }

    public function setCookieLifetime(int $cookieLifetime) : void
    {
        $this->cookieLifetime = $cookieLifetime;
    }
    
    public function getCookieLifetime() : int
    {
        return $this->cookieLifetime;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }
    
    public function getName() : string
    {
        return $this->name;
    }

    public function setUseCookies(bool $useCookies) : void
    {
        $this->useCookies = $useCookies;
    }
    
    public function getUseCookies() : bool
    {
        return $this->useCookies;
    }
    
    public function setUseOnlyCookies(bool $useOnlyCookies) : void
    {
        $this->useOnlyCookies = $useOnlyCookies;
    }

    public function getUseOnlyCookies() : bool
    {
        return $this->useOnlyCookies;
    }
    
    public function setSerializeHandler(string $serializeHandler) : void
    {
        /** http://php.net/manual/en/session.configuration.php#ini.session.serialize-handler */
        $serializeHandlerAvailable = [
            'php_serialize', /** > PHP 5.5.4 */
            'php',
            'php_binary',
            'wddx',
        ];
        
        if (in_array($serializeHandler, $serializeHandlerAvailable, true)) {
            $this->serializeHandler = $serializeHandler;
        } else {
            throw new DomainException('Property $serializeHandler can contain only valid values: '.join(', ', $serializeHandlerAvailable).'. See http://php.net/manual/en/session.configuration.php#ini.session.serialize-handler');
        }
    }

    public function getSerializeHandler() : string
    {
        return $this->serializeHandler;
    }
    
    public function setTimezone(string $timezone) : void
    {
        if (in_array($timezone, DateTimeZone::listIdentifiers())) {
            $this->timezone = $timezone;
        } else {
            throw new DomainException('Property $timezone can contain only valid values: http://php.net/manual/en/timezones.php');
        }
        
    }

    public function getTimezone() : string
    {
        return $this->timezone;
    }
}
