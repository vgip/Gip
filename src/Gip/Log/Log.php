<?php
/**
 * Main log 
 * !!! Refactoring required !!!
 * !!! Refactoring only log() and logError() functions !!!
 * 
 * Based in PSR 3 format
 * @url https://www.php-fig.org/psr/psr-3/
 * @url https://github.com/php-fig/log
 * @url https://en.wikipedia.org/wiki/Syslog#Severity_level - description log level
 */

declare(strict_types = 1);

namespace Vgip\Gip\Log;

use DateTime;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

use Vgip\Gip\Exception\DomainException;
use Vgip\Gip\Common\Error AS ErrorHandler;


/**
 * Class Logger
 * 
 * Psr\Log\LogLevel - list log level
 *
 */
class Log extends AbstractLogger implements LoggerInterface
{
    /**
     * Log config
     * 
     * @var object 
     */
    private $config;
    
    private $storageClassPath = 'Vgip\\Gip\\Log\\Storage\\';
    
    private $configClassPathDefault = 'Vgip\\Gip\\Log\\Config';
    
    /**
     *
     * @var array Allowed log level 
     */
    private $levelsAvailable = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'debug',
        'info',
    ];

    /**
     *
     * @var array Storage config
     */    
    private $storage = null;
    
    private $module = 'undefined';
    
    private $action = 'undefined';
    
    private $ip     = null;
    

    public function __construct(string $configPath = null)
    {
        //$this->setConfig();
        $this->initConfig($configPath);
    }

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }
    
    public function setIp($ip)
    {
        $this->ip = $ip;
    }
    
    public function setModuleActionIp($module, $action, $ip)
    {
        $this->module    = $module;
        $this->action    = $action;
        $this->ip        = $ip;
    }

    public function setStorage($storage)
    {
        $this->storage = storage;
    }
    
    public function log($level, $message, ?array $context = [])
    {
        if (in_array($level, $this->levelsAvailable, true)) {
            
            $contextMain['main'] = $this->setMainContext();
            
            $context = array_merge($context, $contextMain);
            
            $storage = $this->getStorage();
            $storage->log($level, $message, $context);
        }
    }
    
    public function logError(ErrorHandler $error, string $module, string $action, string $ip = null, ?array $context = [])
    {
        $errorMessages = $error->getErrorByDestination('developer');
        foreach ($errorMessages AS $key => $errorData) {
            $level   = $errorData['level'];
            $message = $errorData['message'];
            
            $this->module    = $module;
            $this->action    = $action;
            $this->ip        = $ip;
            
            $contextMain['main'] = $this->setMainContext();
            $context = array_merge($context, $contextMain);
                        
            $this->log($level, $message, $context);
        }
    }

    public function getRowLast($rowCount = 100)
    {
        $storage = $this->getStorage();
        $rowCount = (int)$rowCount;
        
        $data = $storage->getRowLast($rowCount);
        
        return $data;
    }
    
    public function getRowAll()
    {
        $storage = $this->getStorage();
        $data = $storage->getRowAll();
        
        return $data;
    }
    
    public function clearAll()
    {
        $storage = $this->getStorage();
        $data = $storage->clearAll();
        
        return $data;
    }

    
    private function getStorage()
    {
        $className = ucfirst($this->config->getStorageType());
        $storageName = $this->storageClassPath.$className.'\\'.$className;
        $storage = new $storageName($this->config->getStorageConfig());
        
        return $storage;
    }
    
    
    private function setMainContext()
    {
        $dateObj = new DateTime();
        
        $context['ip']      = $this->ip;
        $context['module']  = $this->module;
        $context['action']  = $this->action;
        $context['date']    = $dateObj;        
        
        return $context;
    }

    // OLD
    private function setConfig()
    {
        /**
         * Get config from Main config
         */
        if ($this->levelsAvailable === null OR $this->storage === null) {
            $configObj = Config::getInstance();
            $configAll = $configObj->getConfigAll();
            $confLog = $configAll['log'];
        
            if ($this->levelsAvailable === null) {
                $this->levelsAvailable = $confLog['levels'];
            }
        
            if ($this->storage === null) {
                $this->storage = $confLog['storage'];
            }
        }
    }
    
    private function initConfig(string $configPath = null) : void
    {
        $configPathSelected = (null === $configPath) ? $this->configClassPathDefault : $configPath;
        $this->config = $configPathSelected::getInstance();
    }
}
