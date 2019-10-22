<?php
/**
 * Datetime function
 */

declare(strict_types = 1);

namespace Vgip\Gip\Common;

class DateTime
{
    public static function isValidTimezone(string $timezone) : bool
    {
        return Str::isValidTimezone($timezone);
    }
    
    public static function convertDateByTimezone(string $timezoneSource, string $timezoneDestination, string $date) : string
    {
        $dateObj = self::convertDateAllFormatByTimezone($timezoneSource, $timezoneDestination, $date);
        
        return $dateObj->format('Y-m-d');
    }
    
    public static function convertDateTimeByTimezone(string $timezoneSource, string $timezoneDestination, string $date) : string
    {
        $dateObj = self::convertDateAllFormatByTimezone($timezoneSource, $timezoneDestination, $date);
        
        return $dateObj->format('Y-m-d H:i:s');
    }
    
    public static function convertDateAllFormatByTimezone(string $timezoneSource, string $timezoneDestination, string $date) : \DateTime
    {
        if ($timezoneSource === $timezoneDestination) {
            $timezone = new \DateTimeZone($timezoneSource);
            $date = new \DateTime($date, $timezone);
        } else {
            $timezoneSourceObj         = new \DateTimeZone($timezoneSource);
            $timezoneDestinationObj    = new \DateTimeZone($timezoneDestination);
            $date = new \DateTime($date, $timezoneSourceObj);
            $date->setTimezone($timezoneDestinationObj);
        }
        
        return $date;
    }
}
