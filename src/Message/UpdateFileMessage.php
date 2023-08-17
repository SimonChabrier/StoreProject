<?php

namespace App\Message;


class UpdateFileMessage
{
    private $name;
    private $alt;
    private $productId;
    private $tempFileName;

    public function __construct(
        string $name,
        string $alt,
        int $productId,
        $tempFileName
    ) {
        $this->name = $name;
        $this->alt = $alt;
        $this->productId = $productId;
        $this->tempFileName = $tempFileName;
    }

    public function getName(): string
    {   
        return $this->name;
    }
    public function getAlt(): string
    {   
        return $this->alt;
    }
    public function getProductId()
    {   
        return $this->productId;
    }
    public function getTempFileName()
    {   
        return $this->tempFileName;
    }
}