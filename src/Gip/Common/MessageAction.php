<?php

declare(strict_types = 1);

namespace Vgip\Gip\Common;


class MessageAction
{
    private $sessionName = 'message_action';
    
    private $message = null;
    
    private $template = '';
    
    private $templateAnchor = 'message_action';
    
    private $templatePattern = '~(<\!\-\-\[\[([A-Z0-9_]{1,50})\]\]\-\->)~u';
    
    
    public function setMessageActionMessage(string $message) : void
    {
        $this->setMessageAction();
        $_SESSION[$this->sessionName]['message'] = $message;
    }
    
    public function unsetMessageAction() : void
    {
        unset($_SESSION[$this->sessionName]);
    }
    
    public function setTemplate(string $template) : void
    {
        $this->template = $template;
    }
    
    public function setTemplateAnchor(string $templateAnchor) : void
    {
        $this->templateAnchor = $templateAnchor;
    }

    /**
     * Main function for get message and delete action message session
     * 
     * @return string|null
     */
    public function moveMessgae() : string
    {
        $messageInTemplate = $this->getMessage();
        
        $this->unsetMessageAction();
        
        return $messageInTemplate;
    }
    
    public function getMessage() : string
    {
        if (isset($_SESSION[$this->sessionName]['message']) AND !empty($_SESSION[$this->sessionName]['message'])) {
            $messageInTemplate = $this->convertTemplateToString($_SESSION[$this->sessionName]['message']);
        } else {
            $messageInTemplate = '';
        }
        
        return $messageInTemplate;
    }
    
    private function convertTemplateToString(string $message) : string
    {
        
        preg_match_all($this->templatePattern, $this->template, $matches);
        $search = $matches[1];
        
        foreach ($matches[2] AS $key => $val) {
            $replacementKey = mb_strtolower($val);
            
            if ($replacementKey === $this->templateAnchor) {
                $replacementVal = $message;
            } else {
                $replacementVal = '';
            }
            
            $string = str_replace($search[$key], $replacementVal, $this->template);
        }
        
        return $string;
    }
    
    private function setMessageAction() : void
    {
        if (!isset($_SESSION[$this->sessionName])) {
            $_SESSION[$this->sessionName] = [];
        }
    }
}
