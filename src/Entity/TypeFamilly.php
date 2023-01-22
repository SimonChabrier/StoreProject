<?php

namespace App\Entity;

use App\Repository\TypeFamillyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TypeFamillyRepository::class)
 */
class TypeFamilly
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=ProductType::class, mappedBy="typeFamilly")
     */
    private $types;

    public function __construct()
    {
        $this->types = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, ProductType>
     */
    public function getTypes(): Collection
    {
        return $this->types;
    }

    public function addType(ProductType $type): self
    {
        if (!$this->types->contains($type)) {
            $this->types[] = $type;
            $type->setTypeFamilly($this);
        }

        return $this;
    }

    public function removeType(ProductType $type): self
    {
        if ($this->types->removeElement($type)) {
            // set the owning side to null (unless already changed)
            if ($type->getTypeFamilly() === $this) {
                $type->setTypeFamilly(null);
            }
        }

        return $this;
    }
}
