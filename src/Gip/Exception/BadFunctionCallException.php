<?php
/**
 * Bad Function Call Exception
 * 
 * Используется, когда ваш код возвращает значение, которое не должен возвращать. 
 * Часто вызывается при разных багах в коде. Потомки этого класса используются в более специализированных ситуациях. 
 * Если ни одна из них не подходит под ваш случай, можно использовать LogicException.
 * 
 * @link http://php.net/manual/class.badfunctioncallexception.php PHP Manual
 * @link http://langtoday.com/?p=354 Source of exception description
 */

declare(strict_types = 1);

namespace Vgip\Gip\Exception;

class BadFunctionCallException extends LogicException
{
    
}
