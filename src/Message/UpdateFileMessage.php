<?php

namespace App\Message;


class UpdateFileMessage
{
    private $name = 'nom par défaut';
    private $alt = 'alt par défaut';
    private $product;

    public function __construct(
        string $name,
        string $alt,
        $product
    ) {
        $this->name = $name;
        $this->alt = $alt;
        $this->product = $product;
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
}