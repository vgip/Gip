<?php
/**
 * Bad Function Call Exception
 * 
 * Подкласс BadFunctionCallException. 
 * Аналогично ему используется для методов, которые не существют или которым передано неверное число параметров. 
 * Всегда используйте внутри __call(), в основном для этого оно и применяется.
 * 
 * @link http://php.net/manual/class.badmethodcallexception.php PHP Manual
 * @link http://langtoday.com/?p=354 Source of exception description
 */

declare(strict_types = 1);

namespace Vgip\Gip\Exception;

class BadMethodCallException extends BadFunctionCallException
{
    
}
