<?php
/**
 * Error handler - setting and error storage for user and developer
 * 
 * Error levels - PSR standard.
 * 
 * Error for user and developer may contain various data.
 * For example, display an error for the user that the service is temporarily unavailable, 
 * and for the developer to send extended information about the occurrence of the error.
 * 
 * If for one error it is necessary to set different texts and labels for the user and developer, 
 * then at the end of the error key with text and labels for the developer add "_developer".
 * Example user error key: "login", key for developer: "login_developer"
 * 
 * If the error is set only for the developer, the user will be shown the 
 * default error (__construct(..., $messageTemplateUserDefault)). 
 * 
 * If you want to get only original errors, use getError().
 * If you want to get all errors for user or developer, use getErrorByDestination().
 * 
 * Created 2019
 */

declare(strict_types = 1);

namespace Vgip\Gip\Common;

use Vgip\Gip\Exception\DomainException;
use Vgip\Gip\Common\Str;

class Error
{
    const DEST_USER = 'user';
    
    const DEST_DEVELOPER = 'developer';

    /**
     * Error message directory (error list)
     * 
     * @var array 
     */
    private $messageDirectory;
    
    /**
     * Label Type Directory
     * 
     * In any error message may be present a label(s).
     * A label is some non-constant value that needs to be displayed in a message, example: 
     * "You have 3 login errors", where 3 is some non-constant label value. 
     * In $messageDirectory this error will be stored as "You have [[lable_counter]] login errors".
     * In this property $labelTypeDirectory, you can assign each tag its type: 
     * string, int, etc (see property $labelTypeDirectoryWhiteList) 
     * 
     * @var array 
     */
    private $labelTypeDirectory;
    
    private $labelTypeDirectoryWhiteList = [
        'string',
        'int',
    ];
    
    private $labelTypeDirectoryDefault = 'string';


    /**
     * For whom the error is intended: developer or user
     * 
     * @var array 
     */
    private $destinationWhiteList = [
        'developer',
        'user',
    ];
    
    private $levelWhiteList = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
    ];
    
    private $messageStorage = null;
    
    private $messageTemplateUserDefault = 'Unknown error';

    /**
     * 
     * @param array $messageDirectory ............ - array with error messges, example:
     *                                               ['default_user_error' => 'service temporarily not available',
     *                                                'login' => 'login [[login]] incorrect',
     *                                                'login_developer' => 'attempt to enter containing forbidden characters',
     *                                                'email' => 'email already exists',
     *                                                ...
     *                                               ]
     * @param array $labelTypeDirectory ........... - type for label data, example ['login' => 'string']
     * @param type $messageTemplateUserDefault..... - key from $messageDirectory for default user messge
     *                                                if the user's message with the key is not set - the default message will be set
     */
    public function __construct(array $messageDirectory, array $labelTypeDirectory = [], $messageTemplateUserDefault = null) 
    {
        $this->messageDirectory             = $messageDirectory;
        $this->labelTypeDirectory           = $labelTypeDirectory;
        $this->messageTemplateUserDefault   = (null === $messageTemplateUserDefault) ? $this->messageTemplateUserDefault : $messageTemplateUserDefault;
    }
    
    /**
     * Error setter
     * 
     * @param string $errorMessageKey ...... - main key for every error.
     * @param string|null $errorGroupKey ... - is used for error separation by groups. 
     *                                         Convenient to separation errors the html form fields by groups, example: 
     *                                         for all errors e-mail field use group "email" and for all errors subject field use group "subj".
     * @param string|flag $destination ..... - (user|developer) $this->DEST_USER|$this->DEST_DEVELOPER - to whom the error is addressed: user or developer.
     * @param array $values ................ - values for error message.
     * @param type $level .................. - error level from $this->$levelWhiteList. Used error levels from PSR Standart (https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
     * 
     * @return void
     * @throws DomainException
     */
    public function setError(string $errorMessageKey, string $errorGroupKey = '', string $destination = 'user', array $values = null, string $level = 'error') : void
    {
        if (!isset($this->messageDirectory[$errorMessageKey])) {
            throw new DomainException('Error with key '.$errorMessageKey.' does not exist.');
        }
        
        if (!in_array($destination, $this->destinationWhiteList, true)) {
            throw new DomainException('Destination value "'.$destination.'" not available, select form: "'.join(', ', $this->destinationWhiteList).'".');
        }
        
        if (!in_array($level, $this->levelWhiteList, true)) {
            throw new DomainException('Level value "'.$level.'" not available, select form: "'.join(', ', $this->levelWhiteList).'".');
        }
        
        if (empty($errorGroupKey)) {
            $errorGroupKey = 'basic';
        }
        
        $messageTemplate = $this->messageDirectory[$errorMessageKey];
        $messageTemplateDeveloper = null;
        $message = $this->replaceLabelWithValue($messageTemplate, $values);
        /** Special developer messages if present */
        if ('developer' === $destination) {
            $errorMessageKeyDev = $errorMessageKey.'_developer';
            if (isset($this->messageDirectory[$errorMessageKeyDev])) {
                $messageTemplateDeveloper = $this->messageDirectory[$errorMessageKeyDev];
                
                $userMessage = (isset($this->messageStorage[$errorGroupKey][$errorMessageKey]['user']['message'])) ? $this->messageStorage[$errorGroupKey][$errorMessageKey]['user']['message'] : $message;
                $message = $this->replaceLabelWithValue($messageTemplateDeveloper, $values).'. User message: "'.$userMessage.'"';
            }
        }
        
        //$message = $this->replaceLabelWithValue($messageTemplate, $values);
        
        if (!isset($this->messageStorage[$errorGroupKey][$errorMessageKey][$destination]['message'])) {
            $this->messageStorage[$errorGroupKey][$errorMessageKey][$destination]['message']    = $message;
        }
        if (!isset($this->messageStorage[$errorGroupKey][$errorMessageKey]['level'])) {
            $this->messageStorage[$errorGroupKey][$errorMessageKey]['level']                    = $level;
        }
        
        if (is_array($values)) {
            $vt = $values;
        } else {
            $vt = [];
        }
        $this->messageStorage[$errorGroupKey][$errorMessageKey]['values'] = $vt;
        
        $this->messageStorage[$errorGroupKey][$errorMessageKey]['template'] = $messageTemplate;
        $this->messageStorage[$errorGroupKey][$errorMessageKey]['template_developer'] = (isset($messageTemplateDeveloper)) ? $messageTemplateDeveloper : null;
    }
    
    public function getError() : ?array
    {
        return $this->messageStorage;
    }

    public function getErrorByDestination(string $destination = 'user')
    {
        if (!in_array($destination, $this->destinationWhiteList, true)) {
            throw new DomainException('Destination value "'.$destination.'" not available, select form: "'.join(', ', $this->destinationWhiteList).'".');
        }
        
        $error = [];
        
        if (null !== $this->messageStorage) {
            foreach ($this->messageStorage AS $errorGroupKey => $dataGroup) {
                $messagesAll = [];
                foreach ($dataGroup AS $errorMessageKey => $errorMessage) {
                    $messagesAll[] = $this->getErrorMessageByDestionation($errorMessage, $destination);
                }
                $message = join('; ', $messagesAll);
                if ('user' === $destination) {
                    $error[$errorGroupKey] = $message;
                } else {
                    $error[$errorGroupKey]['message'] = $message;
                    $error[$errorGroupKey]['level']   = $errorMessage['level'];
                }
            }
        }

        return $error;
    }
    
    private function replaceLabelWithValue(string $messageTemplate, array $values = null) : string
    {
        $valuesArr = (null === $values) ? [] : $values;
        
        preg_match_all('~(?:\[\[([a-z0-9_]{1,128})\]\])~u', $messageTemplate, $matches);
        $errorLabelsTemplatesFound = (isset($matches[0])) ? $matches[0] : []; /** [[key]]*/
        $errorLabelsKeysFound      = (isset($matches[1])) ? $matches[1] : []; /* key */
        $searchKeys     = [];
        $replaceValue   = [];
        
        foreach ($errorLabelsTemplatesFound AS $numberKey => $errorLabelTemplate) {
            $keyForValue         = $errorLabelsKeysFound[$numberKey];
            $searchKeys[]        = $errorLabelTemplate;
            $value               = (array_key_exists($keyForValue, $valuesArr)) ? $valuesArr[$keyForValue] : '';
            
            $replaceValue[]      = $this->convertValueByLabelType($keyForValue, $value);
            //$replaceValue[]      = htmlspecialchars($value);
            
        }
        $message = str_replace($searchKeys, $replaceValue, $messageTemplate);
        
        return $message;
    }
    
    private function convertValueByLabelType($key, $value)
    {
        if (array_key_exists($key, $this->labelTypeDirectory)) {
            if (!in_array($this->labelTypeDirectory[$key], $this->labelTypeDirectoryWhiteList)) {
                throw new DomainException('Error label ('.$key.') type '.$this->labelTypeDirectory[$key].' incorrect.');
            } else {
                $type = $this->labelTypeDirectory[$key];
            }
        } else {
            $type = $this->labelTypeDirectoryDefault;
        }
        
        if ('int' === $type) {
            $convertedVal = (int)$value;
        } else {
            $convertedVal = htmlspecialchars((string)$value);
        }
        
        return $convertedVal;
    }
    
    private function getErrorMessageByDestionation(array $row, string $destination)
    {
        $funcName = 'getErrorByDestination'.Str::convertLowerSnakeCaseToUpperCamelCase($destination);
        $message = $this->$funcName($row);
        
        return $message;
    }
    
    private function getErrorByDestinationUser(array $row) : string
    {
        if (isset($row['user'])) {
            $res = $row['user']['message'];
        } else {
            $res = $this->messageTemplateUserDefault;
        }
        
        return $res;
    }
    
    private function getErrorByDestinationDeveloper(array $row) : string
    {
        if (isset($row['developer'])) {
            $res = $row['developer']['message'];
        } else {
            $res = $row['user']['message'];
        }
        
        return $res;
    }
}
