<?php
/**
 * Invalid Argument Exception
 * 
 * Вызываем, когда ожидаемые аргументы в функции/методе некорректно сформированы. 
 * Например, ожидается целое число, а на входе строка или ожидается GET, а пришел POST и т.п.
 * 
 * @link http://php.net/manual/class.invalidargumentexception.php PHP Manual
 * @link http://langtoday.com/?p=354 Source of exception description
 */

declare(strict_types = 1);

namespace Vgip\Gip\Exception;

class InvalidArgumentException extends LogicException
{
    
}
