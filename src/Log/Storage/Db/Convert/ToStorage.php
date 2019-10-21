<?php

declare(strict_types = 1);

namespace Vgip\Gip\Log\Storage\Db\Convert;

use Vgip\Gip\Common\Json;

class ToStorage
{
    public function convertId($id)
    {
        return $id;
    }
    
    
    public function convertModule($module)
    {
        return $module;
    }
    
    
    public function convertAction($action)
    {
        return $action;
    }
    
    
    public function convertLevel($level)
    {
        return $level;
    }
    
    
    public function convertMessage($message)
    {
        return $message;
    }
    
    
    public function convertContext($context)
    {
        $json = new Json();
        $contextConverted = $json->encode($context);
        
        return $contextConverted;
    }
    
    
    public function convertIp($ip)
    {
        $ipA = (empty($ip)) ? '' : inet_pton($ip);
        
        return (string)$ipA;
    }
    
    
    public function convertDateCreate($dateCreate)
    {
        $date = $dateCreate->format('Y-m-d H:i:s');
        
        return $date;
    }
}
