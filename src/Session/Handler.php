<?php

declare(strict_types = 1);

namespace Vgip\Gip\Session;

class Handler
{
    /**
     * Check is valid session id
     * 
     * http://php.net/manual/en/session.configuration.php#ini.session.sid-length
     * 
     * @param string $sessionId
     * @return bool
     */
    public function isValidSessionId(string $sessionId, int $sidLength, ?int $sidBitsPerCharacter = 0) : bool
    {
        /** $sidBitsPerCharacter = 0 - maximum compatibility, see http://php.net/manual/en/session.configuration.php#ini.session.sid-length */
        $patternList = [
            0 => '~^[a-z0-9,-]{22,256}$~ui', 
            4 => '~^[a-f0-9]{'.$sidLength.'}$~u',
            5 => '~^[a-v0-9]{'.$sidLength.'}$~u',
            6 => '~^[a-zA-Z0-9,-]{'.$sidLength.'}$~u',
        ];
        $pattern = isset($patternList[$sidBitsPerCharacter]) ? $patternList[$sidBitsPerCharacter] : $patternList[0];
        
        $resultPregMatch = preg_match($pattern, $sessionId);
        $result = ($resultPregMatch === 1) ? true : false ;

        return $result;
    }
}
