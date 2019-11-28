<?php

namespace App\DTO;

class YandexFileDto
{
    protected $displayName;
    protected $contentLength;
    protected $creationDate;
    
    public function __construct($displayName,$contentLength,$creationDate)
    {
    
        $this->displayName = $displayName;
        $this->contentLength = round($contentLength/1024,2);
        $this->creationDate = date(
            'Y-m-d в H:i:s',
            strtotime($creationDate)
        );
    }
    
    /**
     * @return mixed
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }
    
    /**
     * @return mixed
     */
    public function getContentLength()
    {
        return $this->contentLength;
    }
    
    /**
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }
}