<?php
/**
 * Range Exception
 * 
 * Вызывается, когда значение выходит за границы некоего диапазона. 
 * Похоже на DomainException, но используется при возврате из функции, а не при входе. 
 * Если мы не можем вернуть легитимное значение, мы выбрасываем это исключение. 
 * То есть, к примеру, функция у вас принимает целочисленный индекс и использует другую функцию, чтоб получить некое значение по этой сущности. 
 * Та функция вернула null, но ваша функция не имеет права возвращать Null. 
 * В таком случае можно применить это исключение. 
 * То есть между ними примерно такая же разница, как между OutOfBoundsException и OutOfRangeException.
 * 
 * @link http://php.net/manual/class.rangeexception.php PHP Manual
 * @link http://langtoday.com/?p=354 Source of exception description
 */

declare(strict_types = 1);

namespace Vgip\Gip\Exception;

class RangeException extends RuntimeException
{
    
}
