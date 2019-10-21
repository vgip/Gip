<?php
namespace GIP\Main\Log\Convert;

use GIP\Main\GipMethods;

class ToHtml
{
    
    public function convertId($id)
    {
        return $id;
    }
    
    
    public function convertModule($module)
    {
        return htmlspecialchars($module);
    }
    
    
    public function convertAction($action)
    {
        return htmlspecialchars($action);
    }
    
    
    public function convertLevel($level)
    {
        return htmlspecialchars($level);
    }
    
    
    public function convertMessage($message)
    {
        return htmlspecialchars($message);
    }
    
    
    public function convertContext($context)
    {
        $contextConverted = count($context);
        
        return $contextConverted;
    }
    
    
    public function convertIp($ip)
    {
        return htmlspecialchars($ip);
    }
    
    
    public function convertDateCreate($dateCreate)
    {
        $date = htmlspecialchars($dateCreate->format('Y-m-d H:i:s'));
        
        return $date;
    }
    
    
    public function convertToHtml($data)
    {
        foreach ($data AS $key => $value) {
            $funcName = 'convert'.GipMethods::convertVarToClasname($key);
            $convertedData[$key] = $this->$funcName($value);
        }
        
        return $convertedData;
    }
}
