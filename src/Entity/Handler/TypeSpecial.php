<?php
/**
 * Convert incoming data type of values (sting) to internal format
 */

declare(strict_types = 1);

namespace Vgip\Gip\Entity\Handler;

use Vgip\Gip\Common\DateTime AS CommonDateTime;

class TypeSpecial
{
    private $timezoneSource;
    
    private $timezoneDestination;
    
    public function setTimezoneSource(string $timezoneSource) : void
    {
        $this->timezoneSource = $timezoneSource;
    }
    
    public function setTimezoneDestination(string $timezoneDestination) : void
    {
        $this->timezoneDestination = $timezoneDestination;
    }
    
    public function convertDate(string $value = null) : ?string
    {
        if (empty($value)) {
            $res = null;
        } else {
            $res = CommonDateTime::convertDateByTimezone($this->timezoneSource, $this->timezoneDestination, $value);
        }
        
        return $res;
    }
    
    public function convertDatetime(string $value = null) : ?string
    {
        if (empty($value)) {
            $res = null;
        } else {
            $res = CommonDateTime::convertDateTimeByTimezone($this->timezoneSource, $this->timezoneDestination, $value);
        }
        
        return $res;
    }
}
