<?php

declare(strict_types = 1);

namespace Vgip\Gip\Common;

use Vgip\Gip\Exception\DomainException;


class Json
{
    const ERROR_DECODE_TYPE_EXCEPTION  = 'exception';
    const ERROR_DECODE_TYPE_MESSAGE    = 'message';
    
    private $errorDecodeType      = 'exception';
    
    private $errorDecodeMessage   = '';
    
    /**
     * 
     * @param type $string
     * @return type
     * @throws DomainException
     */
    public function decode($string, $assoc = false, $depth = 512, $options = 0)
    {
        $stringDecode = @json_decode($string, $assoc, $depth, $options);
        $this->checkDecodeError();
        
        return $stringDecode;
    }
    
    public function setErrorDecodeType($flag)
    {
        $this->errorDecodeType = ($flag === self::ERROR_DECODE_TYPE_EXCEPTION) ? ERROR_DECODE_TYPE_EXCEPTION : ERROR_DECODE_TYPE_MESSAGE;
    }

    /**
     * 
     * @param mixed     $value
     * @param int       $options
     * @param int       $depth
     * @return mixed type
     */
    public function encode($value, $options = 0, $depth = 512)
    {
        return json_encode($value, $options, $depth);
    }

     /**
     * Search unknown error in PHP with get_defined_constants() function
     * @param type $jsonLastError
     * @return string
     */
    public function searchDecodeUnknownError($jsonLastError)
    {
        $jsonErrors = $this->getJsonErrorAll();
        
        $res = 'Error with code '.(int)$jsonLastError.' not found in PHP, unknown JSON Error.';
        if (array_key_exists($jsonLastError, $jsonErrors)) {
            $res = 'JSON error constant with code '.(int)$jsonLastError.' found in PHP but not found in this class. Must add a new error constant to the class code. Read documentation: http://php.net/json_last_error.';
        }
        
        return $res;
    }
    
    public function getJsonErrorAll()
    {
        $constants = get_defined_constants(true);
        $jsonErrors = array();
        foreach ($constants['json'] as $name => $value) {
            if (!strncmp($name, "JSON_ERROR_", 11)) {
                $jsonErrors[$value] = $name;
            }
        }

        return $jsonErrors;
    }
    
    public function checkDecodeError()
    {
        $jsonLastError = json_last_error();
        
        switch ($jsonLastError) {
            case JSON_ERROR_NONE:
            break;
            case JSON_ERROR_DEPTH:
                $this->processingDecodeError('maximum stack depth exceeded', 'JSON_ERROR_DEPTH', JSON_ERROR_DEPTH);
            break;
            case JSON_ERROR_STATE_MISMATCH:
                $this->processingDecodeError('underflow or the modes mismatch', 'JSON_ERROR_STATE_MISMATCH', JSON_ERROR_STATE_MISMATCH);
            break;
            case JSON_ERROR_CTRL_CHAR:
                $this->processingDecodeError('unexpected control character found', 'JSON_ERROR_CTRL_CHAR', JSON_ERROR_CTRL_CHAR);
            break;
            case JSON_ERROR_SYNTAX:
                $this->processingDecodeError('syntax error, malformed JSON', 'JSON_ERROR_SYNTAX', JSON_ERROR_SYNTAX);
            break;
            case JSON_ERROR_UTF8:
                $this->processingDecodeError('malformed UTF-8 characters, possibly incorrectly encoded', 'JSON_ERROR_UTF8', JSON_ERROR_UTF8);
            break;
            case JSON_ERROR_RECURSION:
                $this->processingDecodeError('one or more recursive references in the value to be encoded', 'JSON_ERROR_RECURSION', JSON_ERROR_RECURSION);
            break;
            case JSON_ERROR_INF_OR_NAN:
                $this->processingDecodeError('one or more NAN or INF values in the value to be encoded', 'JSON_ERROR_INF_OR_NAN', JSON_ERROR_INF_OR_NAN);
            break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $this->processingDecodeError('a value of a type that cannot be encoded was given', 'JSON_ERROR_UNSUPPORTED_TYPE', JSON_ERROR_UNSUPPORTED_TYPE);
            break;
            case JSON_ERROR_INVALID_PROPERTY_NAME:
                $this->processingDecodeError('a property name that cannot be encoded was given', 'JSON_ERROR_INVALID_PROPERTY_NAME', JSON_ERROR_INVALID_PROPERTY_NAME);
            break;
            case JSON_ERROR_UTF16:
                $this->processingDecodeError('malformed UTF-16 characters, possibly incorrectly encoded', 'JSON_ERROR_UTF16', JSON_ERROR_UTF16);
            break;
            default:
                $errorMsg = $this->searchDecodeUnknownError($jsonLastError);
                $this->processingDecodeError($errorMsg);
            break;
        }
    }
    
    private function processingDecodeError($errorMessage, $constantName = null, $constantValue = null)
    {
        $errorMessageFull = $this->getDecodeErrorMessage($errorMessage, $constantName, $constantValue);
        
        if ($this->errorDecodeType === ERROR_DECODE_TYPE_EXCEPTION) {
            throw new DomainException($errorMessage);
        } else {
            $this->errorDecodeMessage = $errorMessage;
        }
    }

    private function getDecodeErrorMessage($message, $constantName = null, $constantValue = null)
    {
        if ($constantName === null OR $constantValue === null) {
            $addededInfo = '';
        } else {
            $addededInfo = ' (JSON constant '.$constantName.' with value '.$constantValue.')';
        }
        $res = $message.$addededInfo;
        
        return $res;
    }
}
