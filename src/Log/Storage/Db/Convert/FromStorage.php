<?php

declare(strict_types = 1);

namespace Vgip\Gip\Log\Storage\Db\Convert;

use DateTime;
use Vgip\Gip\Common\Json;

class FromStorage
{
    public function convertId($id)
    {
        return (int)$id;
    }
    
    
    public function convertModule($module)
    {
        return (string)$module;
    }
    
    
    public function convertAction($action)
    {
        return (string)$action;
    }
    
    
    public function convertLevel($level)
    {
        return (string)$level;
    }
    
    
    public function convertMessage($message)
    {
        return (string)$message;
    }
    
    
    public function convertContext($context)
    {
        $json = new Json();
        $contextConverted = $json->decode($context);
        
        return $contextConverted;
    }
    
    
    public function convertIp($ip)
    {
        $ipA = (empty($ip)) ? '' : inet_ntop($ip);
        return (string)$ipA;
    }
    
    
    public function convertDateCreate($dateCreate)
    {
        $date = new DateTime($dateCreate);
        
        return $date;
    }
}
