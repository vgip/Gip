<?php
/**
 * Underflow Exception
 * 
 * Обратная OverflowException ситуация, когда, например, класс-контейнер имеет недостаточно элементов для осуществляния операции. 
 * Например, когда он пуст, а вы пытаетесь удалить элемент.
 * 
 * @link http://php.net/manual/class.underflowexception.php PHP Manual
 * @link http://langtoday.com/?p=354 Source of exception description
 */

declare(strict_types = 1);

namespace Vgip\Gip\Exception;

class UnderflowException extends RuntimeException
{
    
}
