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
    private $cacheDuration = 3600;

    /**
     * @ORM\Column(type="boolean")
     */
    private $useCache = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adminMail;

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getcacheDuration(): ?int
    {
        return $this->cacheDuration;
    }

    public function setcacheDuration(int $cacheDuration): self
    {
        $this->cacheDuration = $cacheDuration;

        return $this;
    }

    public function isUseCache(): ?bool
    {
        return $this->useCache;
    }

    public function setUseCache(bool $useCache): self
    {
        $this->useCache = $useCache;

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
