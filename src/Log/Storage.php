<?php

declare(strict_types = 1);

namespace Vgip\Gip\Log;

use Vgip\Gip\Common\NamingConversion;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Vgip\Gip\Log\Storage\Db\Convert\FromStorage;
use Vgip\Gip\Log\Storage\Db\Convert\ToStorage;

/**
 * Class Route
 */
abstract class Storage extends AbstractLogger implements LoggerInterface
{
    public function prepareDataToStorage($level, $message, ?array $context = [])
    {
        $context = (null === $context) ? [] : $context;
        
        $contextOther = $context;
        unset($contextOther['main']);
        
        $rawData = [
            'module'        => $context['main']['module'], 
            'action'        => $context['main']['action'],
            'level'         => $level, 
            'message'       => $message, 
            'context'       => $contextOther, 
            'ip'            => $context['main']['ip'], 
            'date_create'   => $context['main']['date'], 
        ];
        $logData = $this->convertDataToStorage($rawData);
        
        return $logData;
    }
        
    public function convertDataFromStorage($data)
    {
        $convertedData = [];
        
        $namingConversion = new NamingConversion('LowerSnakeCase', 'UpperCamelCase');
        $fromStorage = new FromStorage();
        foreach ($data AS $key => $value) {
            $funcName = 'convert'.$namingConversion->convert($key);
            $convertedData[$key] = $fromStorage->$funcName($value);
        }
        
        return $convertedData;
    }
        
    public function convertDataToStorage($data)
    {
        $convertedData = [];

        $namingConversion = new NamingConversion('LowerSnakeCase', 'UpperCamelCase');
        $fromStorage = new ToStorage();
        foreach ($data AS $key => $value) {
            $funcName = 'convert'.$namingConversion->convert($key);
            $convertedData[$key] = $fromStorage->$funcName($value);
        }
        
        return $convertedData;
    }
}
