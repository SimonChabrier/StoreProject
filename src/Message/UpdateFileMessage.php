<?php

namespace App\Message;


class UpdateFileMessage
{
    private $name;
    private $alt;
    private $productId;
    private $originalName;
    private $binaryContent;

    public function __construct(
        string $name,
        string $alt,
        int $productId,
        string $originalName,
        string $binaryContent
    ) {
        $this->name = $name;
        $this->alt = $alt;
        $this->productId = $productId;
        $this->originalName = $originalName;
        $this->binaryContent = $binaryContent;
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
    public function getOriginalName()
    {   
        return $this->originalName;
    }
    public function getBinaryContent()
    {
        return $this->binaryContent;
    }
}