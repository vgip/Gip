<?php
/**
 * Out Of Range Exception
 * 
 * Используется, когда встречаем некорректный индекс, но на этот раз ошибка должна быть обнаружена ещё до прогона кода, например, 
 * если мы пытаемся адресовать элемент массива, который в принципе не поддерживается. 
 * То есть если функция, возвращающая день недели по его индексу от 1 до 7, получает внезапно 9, 
 * то это DomainException — ошибка логики, а если у нас есть массив с днями недели с индексами от 1 до 7, 
 * а мы пытаемся обратиться к элементу с индексом 9, то это уже OutOfRangeException.
 * 
 * @link http://php.net/manual/class.outofrangeexception.php PHP Manual
 * @link http://langtoday.com/?p=354 Source of exception description
 */

declare(strict_types = 1);

namespace Vgip\Gip\Exception;

class OutOfRangeException extends LogicException
{
    
}
