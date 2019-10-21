<?php

/**
 * Main session handler
 */

declare(strict_types = 1);

namespace Vgip\Gip\Session;

use Vgip\Gip\Session\Config AS SessionConfig;
use Vgip\Gip\Exception\DomainException;

class Session
{
    private static $config  = null;

    private static $handler = null;
    
    public static function setConfig(SessionConfig $config) : void
    {
        self::$config = $config->getAll();
    }
    
    public static function setHandler($handler) : void
    {
        self::$handler = $handler;
    }

    public static function start()
    {
        if (null === self::$handler OR null === self::$config) {
            throw new DomainException('Need use method '.__CLASS__.'::setConfig() and '.__CLASS__.'::setHandler() first.');
        }
        
        self::setConfigIni();
                
        session_set_save_handler(self::$handler, true);
        session_start();
        
        /** Regenerate session id if received session id is not found in storage */
        if (false === self::$handler->isValidSessionIdRead()) {
            session_regenerate_id(false); /** $delete_old_session set to false in order not to send a request with no validation $session_id */
        }
        
        self::sessionRegenerate();
    }

    /**
     * 
     * 1. Protection against session cookie theft 
     * 2. Protection against setting session identifier by user 
     */
   private static function sessionRegenerate()
    {
        $sessionId = session_id();
        if (!empty($sessionId)) {
            $sessionCreate = self::$handler->getDateCreate();
            $sessionCreateTimestamp = $sessionCreate->getTimestamp();
            $now = time();
            if (($now - $sessionCreateTimestamp) > self::$config['regenerate_session_id_time']) {
                session_regenerate_id();
            }
        }
    }
    
    private static function setConfigIni() : void
    {
        $keysIniConfig = [
            'sid_bits_per_character', 
            'sid_length', 
            'cookie_httponly', 
            'cookie_secure', 
            'use_strict_mode', 
            'gc_maxlifetime', 
            'gc_probability', 
            'gc_divisor', 
            'lazy_write', 
            'cookie_lifetime', 
            'name', 
            'use_cookies',
            'use_only_cookies',
            'serialize_handler',
        ];
        
        foreach ($keysIniConfig AS $number => $keyName) {
            $iniKeyName = 'session.'.$keyName;
            $iniValue   = (string)self::$config[$keyName];
            ini_set($iniKeyName, $iniValue);
            //echo $iniKeyName.' '.ini_get($iniKeyName)."\n";
        }
    }
}
