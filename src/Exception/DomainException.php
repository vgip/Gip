<?php
/**
 * Domain Exception
 * 
 * Если в коде подразумеваются некие ограничения для значений, то это исключение можно вызывать, когда значение выходит за эти ограничения. 
 * Например, у вас дни недели обозначаются числами от 1 до 7, а ваш метод получает внезапно на вход 0 или 9, или, скажем, 
 * вы ожидаете число, обозначающее количество зрителей в зале, а получаете отрицательное значени. 
 * Вот в таких случаях и вызывается DomainException. 
 * Также можно использовать для разных проверок параметров, когда параметры нужных типов, 
 * но при этом не проходят проверку на значение.
 * 
 * @link http://php.net/manual/class.domainexception.php PHP Manual
 * @link http://langtoday.com/?p=354 Source of exception description
 */

declare(strict_types = 1);

namespace Vgip\Gip\Exception;

class DomainException extends LogicException
{
    
}
