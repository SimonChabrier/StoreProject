<?php

namespace App\Message;


class UpdateFileMessage
{
    private $name;
    private $alt;
    private $id;

    public function __construct(
        string $name,
        string $alt,
        int $id
    ) {
        $this->name = $name;
        $this->alt = $alt;
        $this->id = $id;
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
        return $this->id;
    }


    

}