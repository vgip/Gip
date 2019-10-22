<?php
/**
 * Overflow Exception
 * 
 * Исключение вызываем, когда есть переполнение. 
 * Например, имеется некий класс-контейнер, который может принимать только 5 элементов, 
 * а мы туда пытаемся записать шестой.
 * 
 * @link http://php.net/manual/class.overflowexception.php PHP Manual
 * @link http://langtoday.com/?p=354 Source of exception description
 */

declare(strict_types = 1);

namespace Vgip\Gip\Exception;

class OverflowException extends RuntimeException
{
    
}
