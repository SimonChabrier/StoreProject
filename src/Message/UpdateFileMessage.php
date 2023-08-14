<?php

namespace App\Message;


class UpdateFileMessage
{
    private $name;
    private $alt;
    private $realPath;
    private $product;

    public function __construct(
        string $name,
        string $alt,
        string $realPath,
        $product
    ) {
        $this->name = $name;
        $this->alt = $alt;
        $this->realPath = $realPath;
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
    public function getRealPath()
    {
        return $this->realPath;
    }
    public function getProduct()
    {   
        return $this->product;
    }


    

}