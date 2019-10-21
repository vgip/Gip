<?php

declare(strict_types = 1);

namespace Vgip\Gip\Request;

use Vgip\Gip\Exception\DomainException;
use Vgip\Gip\Common\Str;

class InputGetter
{
    private $sourceWhiteList = [
        'post',
        'get',
    ];
    

    public function getDataFromSourceByEntity(string $source, array $entity, array $conformityArray = null)
    {
        $data = [];
        
        if (!in_array($source, $this->sourceWhiteList, true)) {
            throw new DomainException('Source "'.$source.'" not exists.');
        }

        if (null === $conformityArray) {
            foreach ($entity AS $entityName) {
                $data[$entityName] = $this->getValueByKey($source, $entityName);
            }
        } else {
            foreach ($conformityArray AS $entityName => $variableInputName) {
                $data[$entityName] = $this->getValueByKey($source, $variableInputName);
            }
        }
        
        
        return $data;
    }
    
    public function getValueByKey(string $source, string $key) : ?string
    {
        $typeConformity = [
            'post'  => INPUT_POST,
            'get'   => INPUT_GET,
        ];
        
        $value = filter_input($typeConformity[$source], $key);
        
        return $value;
    }
}
