<?php

/**
 * PageStorage - for html-page data
 * 
 * EXPERIMENTAL CLASS - NOT FOR USE IN PROJECT
 * may be deprecated and removed in the future 
 */

declare(strict_types = 1);

namespace Vgip\Gip\Html;

use Vgip\Gip\Common\Singleton;
use Vgip\Gip\Exception\OutOfBoundsException;

class PageStorage
{
    use Singleton;
    
    /**
     * Page language identifier
     * 
     * Values: en, es, fi and etc.
     * 
     * @var string 
     */
    private $htmlLang = 'en';
    
    private $headTitle = '';
    
    private $headCharset = 'utf-8';
    
    private $bodyContent = '';
    
    
    public function setHtmlLang($htmlLang) 
    {
        $this->htmlLang = $htmlLang;
    }
    
    public function getHtmlLang() 
    {
        $htmlLang = '';
        if (!empty($this->htmlLang)) {
            $htmlLang = ' lang="'.$this->htmlLang.'"';
        }
        
        return $htmlLang;
    }

    public function setHeadTitle($headTitle) 
    {
        $this->headTitle = $headTitle;
    }
    
    public function getHeadTitle() 
    {
        $headTitle = '';
        if (!empty($this->headTitle)) {
            $headTitle = '<title>'.$this->headTitle.'</title>';
        }
        
        return $headTitle;
    }

    public function setHeadCharset($headCharset) 
    {
        $this->headCharset = $headCharset;
    }
    
    public function getHeadCharset() 
    {
        $headCharset = '';
        if (!empty($this->headCharset)) {
            $headCharset = '<meta charset="'.$this->headCharset.'">';
        }
        
        return $headCharset;
    }

    public function setBodyContent($bodyContent) 
    {
        $this->bodyContent = $bodyContent;
    }
    
    public function getBodyContent() 
    {
        return $this->bodyContent;
    }
}    
