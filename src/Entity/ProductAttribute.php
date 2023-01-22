<?php

namespace App\Entity;

use App\Repository\ProductAttributeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductAttributeRepository::class)
 */
class ProductAttribute
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
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $type;

    /**
     * @ORM\ManyToMany(targetEntity=ProductType::class, mappedBy="attributes")
     */
    private $productTypes;

    /**
     * @ORM\OneToMany(targetEntity=AttributeValue::class, mappedBy="productAttribute", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private $attributeValues;

    public function __construct()
    {
        $this->productTypes = new ArrayCollection();
        $this->attributeValues = new ArrayCollection();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, ProductType>
     */
    public function getProductTypes(): Collection
    {
        return $this->productTypes;
    }

    public function addProductType(ProductType $productType): self
    {
        if (!$this->productTypes->contains($productType)) {
            $this->productTypes[] = $productType;
            $productType->addAttribute($this);
        }

        return $this;
    }

    public function removeProductType(ProductType $productType): self
    {
        if ($this->productTypes->removeElement($productType)) {
            $productType->removeAttribute($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection<int, AttributeValue>
     */
    public function getAttributeValues(): Collection
    {
        return $this->attributeValues;
    }

    public function addAttributeValue(AttributeValue $attributeValue): self
    {
        if (!$this->attributeValues->contains($attributeValue)) {
            $this->attributeValues[] = $attributeValue;
            $attributeValue->setProductAttribute($this);
        }

        return $this;
    }

    public function removeAttributeValue(AttributeValue $attributeValue): self
    {
        if ($this->attributeValues->removeElement($attributeValue)) {
            // set the owning side to null (unless already changed)
            if ($attributeValue->getProductAttribute() === $this) {
                $attributeValue->setProductAttribute(null);
            }
        }

        return $this;
    }
}
