<?php

namespace App\Message;


class UpdateFileMessage
{
    private $name = 'nom par défaut';
    private $alt = 'alt par défaut';
    private $product;
    private $tempFileName;

    public function __construct(
        string $name,
        string $alt,
        $product,
        $tempFileName
    ) {
        $this->name = $name;
        $this->alt = $alt;
        $this->product = $product;
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
    public function getProduct()
    {   
        return $this->product;
    }
    public function getTempFileName()
    {   
        return $this->tempFileName;
    }
}