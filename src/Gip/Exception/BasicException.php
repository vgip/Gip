<?php
/**
 * Basic exceptions
 * 
 * Copy structure from SPL Exception
 * 
 * Аналог базового класса Exception. От этого класса наследуются более узкопрофильные исключения.
 * 
 * @link http://php.net/manual/spl.exceptions.php PHP Manual
 * 
 */

declare(strict_types = 1);

namespace Vgip\Gip\Exception;

class BasicException extends \Exception
{
    use Basic;
}
