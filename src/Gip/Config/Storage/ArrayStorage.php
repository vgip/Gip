<?php

declare(strict_types = 1);

namespace Vgip\Gip\Config\Storage;

use Vgip\Gip\Config\Storage\TraitArray;
use Vgip\Gip\Config\Storage\Storage;
use Vgip\Gip\Common\NamingConversion;

class ArrayStorage extends Storage implements InterfaceStorage
{
    use TraitArray;
    
    private $obj;
    
    public function __construct(array $data, bool $serialize = false, string $keyNamingType = NamingConversion::LOWER_SNAKE_CASE)
    {
         $this->obj = $this->getObject($data, $keyNamingType);
    }
    
    public function getConfig()
    {
        return $this->obj;
    }
}
