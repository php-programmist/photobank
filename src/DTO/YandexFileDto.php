<?php

namespace App\DTO;

class YandexFileDto
{
    protected $displayName;
    protected $contentLength;
    protected $creationDate;
    protected $imageData;
    protected $imageType;
    
    public function __construct($displayName,$contentLength,$creationDate,$imageData,$imageType)
    {
    
        $this->displayName = $displayName;
        $this->contentLength = round($contentLength/1024,2);
        $this->creationDate = date(
            'Y-m-d в H:i:s',
            strtotime($creationDate)
        );
        $this->imageData = base64_encode($imageData);
        $this->imageType = $imageType;
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
    
    /**
     * @return string
     */
    public function getImageData(): string
    {
        return $this->imageData;
    }
    
    /**
     * @return mixed
     */
    public function getImageType()
    {
        return $this->imageType;
    }
}