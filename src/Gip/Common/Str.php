<?php
/**
 * String convertation
 */

declare(strict_types = 1);

namespace Vgip\Gip\Common;

use Vgip\Gip\Exception\DomainException;

class Str
{
    /**
     * List of valid time zones for PHP
     * 
     * @var type 
     */
    private static $timezones = null;
    
    public static function convertLowerSnakeCaseToLowerCamelCase(string $value) : string
    {
        $valueWhitespice = str_replace(['_'], ' ', mb_strtolower($value));
        $valueArr = explode(' ', $valueWhitespice);
        $res = array_shift($valueArr);
        foreach ($valueArr AS $value) {
            $res .= self::mb_ucfirst($value);
        }
        
        return $res;
    }
    
    public static function convertLowerSnakeCaseToUpperCamelCase(string $value) : string
    {
        $valueWhitespice = str_replace(['_', '-'], ' ', strtolower($value));
        $valueUswords = self::mb_ucword($valueWhitespice);
        $res = str_replace(' ', '', $valueUswords);
        
        return $res;
    }
    
    public static function convertUpperCamelCaseToLowerSnakeCase(string $value) : string
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!u', $value, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            //$match = ($match === mb_strtoupper($match)) ? strtolower($match) : lcfirst($match);
            $match = mb_strtolower($match);
            //echo $match.' ';
        }
        $res = implode('_', $ret);
        
        return $res;
    }
    
    public static function convertLowerCamelCaseToLowerSnakeCase(string $value) : string
    {
        return self::convertUpperCamelCaseToLowerSnakeCase($value);
    }
    
    public static function convertLowerCamelCaseToUpperCamelCase(string $value) : string
    {
        return self::mb_ucfirst($value);
    }
    
    /**
     * Determine whether a variable is valid name
     * 
     * @link http://php.net/manual/language.variables.basics.php PHP manual
     * 
     * @param string $name
     * @return bool
     */
    public static function isValidVariableName(string $name) : bool
    {
        $res = false;
        $pattern = '~^[a-zA-Z_\x7f-\xff]{1}[a-zA-Z0-9_\x7f-\xff]{0,128}$~u';
        
        if (preg_match($pattern, $name)) {
            $res = true;
        }
        
        return $res;
    }
    
    /**
     * Determine whether a property is valid name
     * 
     * @link http://php.net/manual/language.variables.basics.php PHP manual
     * 
     * @param string $name
     * @return bool
     */
    public static function isValidPropertyName(string $name) : bool
    {
        $res = self::isValidVariableName($name);
        
        return $res;
    }
    
    /**
     * Determine whether a function is valid name
     * 
     * @link http://php.net/manual/language.variables.basics.php PHP manual
     * 
     * @param string $name
     * @return bool
     */
    public static function isValidFunctionName(string $name) : bool
    {
        $res = self::isValidVariableName($name);
        
        return $res;
    }
    
    /**
     * Determine whether a method is valid name
     * 
     * @link http://php.net/manual/language.variables.basics.php PHP manual
     * 
     * @param string $name
     * @return bool
     */
    public static function isValidMethodName(string $name) : bool
    {
        $res = self::isValidVariableName($name);
        
        return $res;
    }
    
    /**
     * Determine whether a class is valid name
     * 
     * @link http://php.net/manual/language.variables.basics.php PHP manual
     * 
     * @param string $name
     * @return bool
     */
    public static function isValidClassName(string $name) : bool
    {
        $res = self::isValidVariableName($name);
        
        return $res;
    }
    
    /**
     * Determine if a string is a valid PHP time zone
     * 
     * @param string $timezone
     * @return bool
     */
    public static function isValidTimezone(string $timezone) : bool
    {
        $res = false;
        
        if (null === self::$timezones) {
            self::$timezones = timezone_identifiers_list();
        }
        
        if (in_array($timezone, self::$timezones, true)) {
            $res = true;
        }
        
        return $res;
    }
    
    /**
     * Multibyte analog to ucfirst()
     * 
     * @param string $string
     * @return string
     */
    public static function mb_ucfirst(string $string) : string
    {
        $strlen = mb_strlen($string);
        
        $convertedString = '';
        
        if ($strlen >= 1) {
            $convertedString .= mb_strtoupper(mb_substr($string, 0, 1));
            if ($strlen >= 2) {
               $convertedString .= mb_substr($string, 1);
            }
        } 
        

        return $convertedString;
    }
    
    /**
     * Multibyte analog to lcfirst()
     * 
     * @param string $string
     * @return string
     */
    public static function mb_lcfirst(string $string) : string
    {
        $strlen = mb_strlen($string);
        
        $convertedString = '';
        
        if ($strlen >= 1) {
            $convertedString .= mb_strtolower(mb_substr($string, 0, 1));
            if (($strlen >= 2)) {
               $convertedString .= mb_substr($string, 1);
            }
        }

        return $convertedString;
    }
    
    /**
     * Multibyte analog to ucword()
     * 
     * @param string $string
     * @return string
     */
    public static function mb_ucword(string $string) : string
    {
        $res = mb_convert_case($string, MB_CASE_TITLE);
        
        return $res;
    }
       
    /**
     * Converts an UTF-8 string to an array.
     *
     * Example mb_str_split("Hello Friend");
     * returns ['H', 'e', 'l', 'l', 'o', ' ', 'w', 'o', 'r', 'l', 'd']
     *
     * @param string $string The input string.
     * @param int $split_length Maximum length of the chunk. If specified, the returned array will be broken down
     *        into chunks with each being split_length in length, otherwise each chunk will be one character in length.
     * @return array|boolean
     *         -
     *         - If the split_length length exceeds the length of string, the entire string is returned
     *           as the first (and only) array element.
     *         - False is returned if split_length is less than 1.
     */
    public static function mb_str_split(string $string, int $length = 1)
    {
        $resSplit = preg_split('~~u', $string, -1, PREG_SPLIT_NO_EMPTY);
        if ($length > 1) {
            $chunks = array_chunk($resSplit, $length);
            foreach ($chunks as $i => $chunk) {
                $chunks[$i] = join('', (array)$chunk);
            }
            $res = $chunks;
        } else {
            $res = $resSplit;
        }
        
        return $res;
    }
            
}
