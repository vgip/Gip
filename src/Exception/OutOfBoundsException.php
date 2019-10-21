<?php
/**
 * Out Of Bounds Exception
 * 
 * Вызываем, когда обнаружили попытку использования неправильного ключа, например, 
 * в ассоциативном массиве или при реализации ArrayAccess. 
 * Используется тогда, когда ошибка не может быть обнаружена до прогона кода. 
 * То есть, например, когда то, какие именно ключи будут легитимными, определяется динамически уже во время выполнения.
 * 
 * @link http://php.net/manual/class.outofboundsexception.php PHP Manual
 * @link http://langtoday.com/?p=354 Source of exception description
 */

declare(strict_types = 1);

namespace Vgip\Gip\Exception;

class OutOfBoundsException extends RuntimeException
{
    
}
