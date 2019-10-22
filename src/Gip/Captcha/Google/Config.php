<?php
/**
 * Google captcha config
 */

declare(strict_types = 1);

namespace Vgip\Gip\Captcha\Google;

use Vgip\Gip\Config\TraitProperty;
use Vgip\Gip\Config\InterfaceConfig;


class Config implements InterfaceConfig
{
    //use Singleton;
    
    use TraitProperty;
    
    
    private $tokenPublic;
    
    private $tokenPrivate;
    
    private $urlVerify;
    
    private $hostname;
    
    
    public function setTokenPublic(string $tokenPublic) : void
    {
        $this->tokenPublic = $tokenPublic;
    }
    
    public function getTokenPublic() : string
    {
        return $this->tokenPublic;
    }
    
    public function setTokenPrivate(string $tokenPrivate) : void
    {
        $this->tokenPrivate = $tokenPrivate;
    }
    
    public function getTokenPrivate() : string
    {
        return $this->tokenPrivate;
    }
    
    public function setUrlVerify(string $urlVerify) : void
    {
        $this->urlVerify = $urlVerify;
    }

    public function getUrlVerify() : string
    {
        return $this->urlVerify;
    }
    
    public function setHostname(string $hostname) : void
    {
        $this->hostname = $hostname;
    }
    
    public function getHostname() : string
    {
        return $this->hostname;
    }
}
