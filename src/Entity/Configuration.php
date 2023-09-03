<?php

namespace App\Entity;

use App\Repository\ConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConfigurationRepository::class)
 */
class Configuration
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $cacheTtl = 3600;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adminMail;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCacheTtl(): ?int
    {
        return $this->cacheTtl;
    }

    public function setCacheTtl(int $cacheTtl): self
    {
        $this->cacheTtl = $cacheTtl;

        return $this;
    }

    public function getAdminMail(): ?string
    {
        return $this->adminMail;
    }

    public function setAdminMail(?string $adminMail): self
    {
        $this->adminMail = $adminMail;

        return $this;
    }
}
