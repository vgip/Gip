<?php

declare(strict_types = 1);

namespace Vgip\Gip\Curl;

//Stg\Api\Connection\Connection

use Stg\Api\Exception\ConnectionException;
use Stg\Api\Exception\DataException;


class CurlWrapper
{    

    private $ch;
    
    private $verbose;
    
    private $requestMethod       = null;
    
    private $option              = [];
    
    private $getParams           = null;
    
    private $dataInfo            = null;
    
    private $dataHeader          = null;
    
    private $dataContent         = null;
    
    private $dataHttpCode        = null;
    
    private $errorNumber         = null;
    
    private $errorMessage        = null;
    
    
    public function query($url) : bool
    {
        $headers = [];
        $headers['http_code'] = 0;
        
        $urlQuery = (null !== $this->getParams) ? $url.'?'.$this->getParams : $url;
        
        $this->ch = curl_init($urlQuery);

        $optionDefault = [
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => false,
            CURLOPT_HEADERFUNCTION  => 
                function($curl, $header) use (&$headers)
                {
                    $len = strlen($header);
                    $header = explode(':', $header, 2);
                    if (count($header) < 2) { // ignore invalid headers
                        return $len;
                    }
                    
                    $name = strtolower(trim($header[0]));
                    if (!array_key_exists($name, $headers)) {
                        $headers[$name] = [trim($header[1])];
                    } else {
                        $headers[$name][] = trim($header[1]);
                    }

                    return $len;
                }
            ];
            
        $this->option = $this->option + $optionDefault;   
        
        curl_setopt_array($this->ch, $this->option);
        
        $this->dataContent = curl_exec($this->ch);
    
        $errorNum = curl_errno($this->ch);
        if ($errorNum === 0) {
            $this->dataInfo      = curl_getinfo($this->ch);
            $this->dataHeader    = $headers;
            $this->dataHttpCode  = $this->dataInfo['http_code'];
            $result = true;
        } else {
            $this->errorNumber   = $errorNum;
            $this->errorMessage  = curl_error($this->ch);
            $this->dataInfo      = curl_getinfo($this->ch);
            $this->dataContent   = null;
            $result = false;
        }
        curl_close($this->ch);
        //print_r($this->option);
        
        return $result;
    }
    
    public function setUserAgent(string $useragent)
    {
        $this->option[CURLOPT_USERAGENT] = $useragent;
    }
    
    /** 
     * Set verbose option to verify query send data 
     * 
     * View sent headers and extended output data
     * Get data with getDataVerbose();
     * Not work with CURLINFO_HEADER_OUT => true and setHeaderOut()
     */
    public function setVerboseOptions()
    {
        $this->option[CURLOPT_VERBOSE] = true;
        $this->option[CURLOPT_STDERR]  = $this->verbose = fopen('php://temp', 'rw+');
    }
    
    /** 
     * Add out headers to data in out curl_getinfo()in array key [request_header].
     * 
     * View data with getDataHeader()
     */
    public function setHeaderOut()
    {
        $this->option[CURLOPT_HEADER]      = true;
        $this->option[CURLINFO_HEADER_OUT] = true;
    }
    
    public function setGetParams(array $params) : void
    {
        $this->getParams = http_build_query($params);
    }

    public function setPostParams(array $params) : void
    {
        $compatibleMethods = [null, 'get'];
        if (!in_array($this->requestMethod, $compatibleMethods)) {
            throw new DataException('Unable to set request type POST, since previously set request type '.$this->requestMethod.'.');
        }
        
        $this->option[CURLOPT_POST] = true;
        
        $prepareParams = urldecode(http_build_query($params));
        $this->option[CURLOPT_POSTFIELDS] = $prepareParams;
    }

    public function getDataInfo() : ?array
    {
        return $this->dataInfo;
    }

    public function getDataHeader() : ?array
    {
        return $this->dataHeader;
    }

    public function getDataContent() : ?string
    {
        return $this->dataContent;
    }
    
    public function getHttpCode() //: ?int
    {
        return $this->dataHttpCode;
    }
    
    public function getData() : array
    {
        $data['info']                = $this->dataInfo;
        $data['header']              = $this->dataHeader;
        $data['content']             = $this->dataContent;
        
        return $data;
    }
    
    public function getDataVerbose()
    {
        if (!is_resource($this->verbose)) {
            $data = 'Verbose information: OFF. Run setVerboseOptions() previosly.';
        } else {
            !rewind($this->verbose);
            $data = 'Verbose information:'."\n".stream_get_contents($this->verbose);
        }
        
        return $data;
    }

    public function getErrorNumber() : ?int
    {
        return $this->errorNumber;
    }
    
    public function getErrorMessage() : ?string
    {
        return $this->errorMessage;
    }
    
    public function getError() : array
    {
        $error = [];
        $error['number']     = $this->errorNumber;
        $error['message']    = $this->errorMessage;
        
        return $error;
    }
}
