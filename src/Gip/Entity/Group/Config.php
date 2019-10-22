<?php
/**
 * Basic config for table
 */

declare(strict_types = 1);

namespace Vgip\Gip\Entity\Group;

use Vgip\Gip\Config\TraitProperty;
use Vgip\Gip\Config\InterfaceConfig;
use Vgip\Gip\Exception\DomainException;


class Config implements InterfaceConfig
{
    use TraitProperty;
    
    /**
     * Internal types for every entity
     * 
     * String, int, bool, etc 
     * 
     * @var object 
     */
    private $type;

    /**
     * Special types 
     * 
     * Date, datetime, etc
     * 
     * @var type 
     */
    private $typeSpecial;
    
    
    public function setType(array $type) : void
    {
        $this->type = $type;
    }
    
    public function getType() : array
    {
        return $this->type;
    }
    
    public function getTypeByKey(string $key) : string
    {
        if (array_key_exists($key, $this->type)) {
            $res = $this->type[$key];
        } else {
            throw new DomainException('Type for key "'.$key.'" not defined.');
        }
        
        return $res;
    }
    
    public function setTypeSpecial(array $typeSpecial)  : void
    {
        $this->typeSpecial = $typeSpecial;
    }

    public function getTypeSpecial() : array
    {
        return $this->typeSpecial;
    }
    
    public function getTypeSpecialByKey(string $key) : ?string
    {
        if (array_key_exists($key, $this->typeSpecial)) {
            $res = $this->typeSpecial[$key];
        } else {
            $res = null;
        }
        
        return $res;
    }
}
