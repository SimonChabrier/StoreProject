<?php

namespace App\Message;


class UpdateFileMessage
{
    private $file;
    private $pictureObject;
    private $productObject;

    public function __construct(
        array $file, 
        string $pictureObject,
        string $productObject
    ) {
        $this->file = $file;
        $this->pictureObject = $pictureObject;
        $this->productObject = $productObject;
    }

    public function getFile(): array
    {   
        return $this->file;
    }

    public function getPictureObject(): string
    {   
        return $this->pictureObject;
    }

    public function getProductObject(): string
    {   
        return $this->productObject;
    }
    

}