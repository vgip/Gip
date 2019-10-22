<?php

/**
 * Unserialize session data
 */

declare(strict_types = 1);

namespace Vgip\Gip\Session;

use Vgip\Gip\Exception\DomainException;

class Serialize
{
    /**
     * Decode raw session data
     * 
     * @param string $data ..... - raw session data
     * @param string $handler .. - session encode handler from ini_get('session.serialize_handler') 
     *                             http://php.net/manual/ru/session.configuration.php#ini.session.serialize-handler
     * @return array
     * @throws DomainException
     */
    public static function decode(string $data, string $handler) : array
    {
        switch ($handler) {
            case 'php':
                return self::decodePhp($data);
            case 'php_binary':
                return self::decodePhpBinary($data);
            case 'php_serialize':
                return self::decodePhpSerialize($data);
            default:
                throw new DomainException('Unsupported session decode handler "'.$handler.'" for decode session data.');
        }
    }
    
    public static function decodePhp(string $data) : array
    {
        $ret = [];
        $offset = 0;
        
        while ($offset < mb_strlen($data)) {
            if (!mb_strstr(mb_substr($data, $offset), "|")) {
                throw new DomainException('Invalid session data in handler "php"');
            }
            $pos = mb_strpos($data, "|", $offset);
            $num = $pos - $offset;
            $varname = mb_substr($data, $offset, $num);
            $offset += $num + 1;
            $dataUnserialize = unserialize(mb_substr($data, $offset), ['allowed_classes' => false]);
            $ret[$varname] = $dataUnserialize;
            $offset += mb_strlen(serialize($dataUnserialize));
        }
        
        return $ret;
    }
    
    public static function decodePhpBinary(string $data) : array
    {
        $ret = [];
        $offset = 0;
        
        while ($offset < mb_strlen($data)) {
            $num = mb_ord($data[$offset]);
            $offset += 1;
            $varname = mb_substr($data, $offset, $num);
            $offset += $num;
            $dataUnserialize = unserialize(mb_substr($data, $offset), ['allowed_classes' => false]);
            $ret[$varname] = $dataUnserialize;
            $offset += mb_strlen(serialize($dataUnserialize));
        }
        
        return $ret;
    }
    
    public static function decodePhpSerialize(string $data) : array
    {
        if (empty($data)) {
            $ret = [];
        } else {
            $ret = @unserialize($data, ['allowed_classes' => false]);
            if (false === $ret) {
                throw new DomainException('Invalid session data in handler "php_serialize"');
            }
        }

        return $ret;
    }
}
