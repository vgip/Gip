<?php
/**
 * Common framework config file 
 */

declare(strict_types = 1);

namespace Vgip\Gip\Db\Mysql;

use Vgip\Gip\Config\TraitProperty;
use Vgip\Gip\Config\InterfaceConfig;
use Vgip\Gip\Exception\DomainException;


class Config implements InterfaceConfig
{
    use TraitProperty;
    
    private $host                   = null;
    
    private $user                   = null;
    
    private $pass                   = null;
    
    /** 
     * DB Name
     * 
     * @var string 
     */
    private $db                     = null;
    
    private $port                   = null;
    
    private $socket                 = null;
    
    private $pconnect               = null;
    
    private $charset                = null;
    
    private $exception              = null;
    
    private $errmode                = null;
    
    private $tablePrefix            = null;
    
    /**
     * Db timezone
     * 
     * Timezone list http://php.net/manual/en/timezones.php or 
     * @var string
     */
    private $timezone               = null;

    
    public function setHost(string $host = null) : void
    {
        if (null === $host) {
            $host = 'localhost';
        }
        $this->host = $host;
    }
    
    public function getHost() : string
    {
        return $this->host;
    }
    
    public function setUser($user) : void
    {
        if (null === $user) {
            $user = 'root';
        }
        $this->user = $user;
    }

    public function getUser() : string
    {
        return $this->user;
    }
    
    public function setPass($pass) : void
    {
        if (null === $pass) {
            $pass = '';
        }
        $this->pass = $pass;
    }

    public function getPass() : string
    {
        return $this->pass;
    }

    public function setDb($db) : void
    {
        if (null === $db) {
            $db = '';
        }
        $this->db = $db;
    }
    
    public function getDb() : string
    {
        return $this->db;
    }
    
    public function setPort($port) : void
    {
        $this->port = $port;
    }

    public function getPort() : ?int
    {
        return $this->port;
    }
    
    public function setSocket($socket) : void
    {
        $this->socket = $socket;
    }

    public function getSocket() : ?string
    {
        return $this->socket;
    }
    
    public function setPconnect($pconnect) : void
    {
        if (null === $pconnect) {
            $pconnect = false;
        }
        $this->pconnect = $pconnect;
    }

    public function getPconnect() : bool
    {
        return $this->pconnect;
    }
    
    public function setCharset($charset) : void
    {
        if (null === $charset) {
            $charset = 'utf8';
        }
        $this->charset = $charset;
    }

    public function getCharset() : string
    {
        return $this->charset;
    }
    
    public function setErrmode($errmode) : void
    {
        $available = [0 => 'exception', 1 => 'error'];
        
        if (null === $errmode) {
            $errmode = $available[0];
        } else if (!in_array($errmode, $available, true)) {
            throw new DomainException('Error mode $errmode is incorrect, set a valid value from these: '.join(', ', $available).'.');
        }
        
        $this->errmode = $errmode;
    }

    public function getErrmode() : ?string
    {
        return $this->errmode;
    }
    
    public function setException($exception) : void
    {
        if (null === $exception) {
            $exception = '\\Exception';
        }
        $this->exception = $exception;
    }

    public function getException() : string
    {
        return $this->exception;
    }

    public function setTablePrefix($tablePrefix) : void
    {
        if (null === $tablePrefix) {
            $tablePrefix = '';
        }
        $this->tablePrefix = $tablePrefix;
    }

    public function getTablePrefix() : string
    {
        return $this->tablePrefix;
    }
    
    public function setTimezone(string $timezone) : void
    {
        if (null === $timezone) {
            $timezone = 'UTC';
        }
        $this->timezone = $timezone;
    }
    
    public function getTimezone() : string
    {
        return $this->timezone;
    }
}
