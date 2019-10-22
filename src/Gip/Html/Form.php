<?php

/**
 * Form option generator
 */

declare(strict_types = 1);

namespace Vgip\Gip\Html;

use Vgip\Gip\Exception\OutOfBoundsException;

class Form
{
    private $keyValidCharacterPattern = '~^[a-z0-9_-]+$~u';
    
    
    public function getValueOption(array $data, $idDefault = null, $idSelected = null) : string
    {
        $res = '';
        
        $idSelectedString = (string)$idSelected;
        
        if (null !== $idDefault) {
            if (null === $idSelected) {
                $idSelected = $idDefault;
            } 
        }
        
        $selectedFound = false;
        foreach ($data AS $key => $value) {
            $selected = '';
            
            if ((string)$key === $idSelectedString) {
                $selected = ' selected';
                $selectedFound = true;
            }
            
            $res .= $this->getValueOptionRow($key, $value, $selected);
        }
        
        /** Attempt set default option if $idSelected is invalid or incorrect */
        if ($idDefault !== null AND false === $selectedFound) {
            $idSelectedString = (string)$idDefault;
            foreach ($data AS $key => $value) {
                $selected = '';
            
                if ((string)$key === $idSelectedString) {
                    $selected = ' selected';
                }
            
                $res .= $this->getValueOptionRow($key, $value, $selected);
            }
        }
        
        return $res;
    }
    
    public function getValueOptionRow($key, $value, $selected = '')
    {
        if (!preg_match($this->keyValidCharacterPattern, (string)$key)) {
            throw new OutOfBoundsException('Key contains invalid characters (pattern '.$this->keyValidCharacterPattern.').');
        }
        
        return '<option value="'.$key.'"'.$selected.'>'.htmlspecialchars($value).'</option>';
    }
    
    public function getValueInput(string $value) : string
    {
        return htmlspecialchars($value);
    }
}
