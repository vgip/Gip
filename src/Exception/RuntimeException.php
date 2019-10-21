<?php
/**
 * Runtime Exception
 * 
 * Исключения времени выполнения нужно вызывать, когда код самостоятельно не может справиться с некой ситуацией во время своего выполнения. 
 * Подклассы этого класса сужают область применения, но, если ни один из них не подходит для вашей ситуации, смело пользуйтесь этим классом. 
  * 
 * @link http://php.net/manual/class.runtimeexception.php PHP Manual
 * @link http://langtoday.com/?p=354 Source of exception description
 */

declare(strict_types = 1);

namespace Vgip\Gip\Exception;

class RuntimeException extends BasicException
{
    
}
