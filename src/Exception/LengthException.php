<?php
/**
 * Lenght Exception
 * 
 * Вызывается, если длина чего-то слишком велика или мала. 
 * Например, имя файла слишком короткое или длина массива слишком большая.
 * 
 * @link http://php.net/manual/class.lengthexception.php PHP Manual
 * @link http://langtoday.com/?p=354 Source of exception description
 */

declare(strict_types = 1);

namespace Vgip\Gip\Exception;

class LenghtException extends LogicException
{
    
}
