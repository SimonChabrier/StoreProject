<?php

namespace App\Entity;

use App\Repository\ProductTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductTypeRepository::class)
 */
class ProductType
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
     * @ORM\ManyToMany(targetEntity=ProductAttribute::class, inversedBy="productTypes", fetch="EAGER")
     */
    private $attributes;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="productType", cascade={"persist"}, fetch="EAGER")
     */
    private $products;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->products = new ArrayCollection();
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
     * @return Collection<int, ProductAttribute>
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function addAttribute(ProductAttribute $attribute): self
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes[] = $attribute;
        }

        return $this;
    }

    public function removeAttribute(ProductAttribute $attribute): self
    {
        $this->attributes->removeElement($attribute);

        return $this;
    }

    public function __toString()
    {
        // retourner le nom de tous les attributs de ce type
        // $attributes = $this->getAttributes();
        // $attributesNames = [];
        // foreach ($attributes as $attribute) {
        //     $attributesNames[] = $attribute->getName();
        // }

        // return $this->getName() . implode(', ', $attributesNames);
        return $this->getName();

    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setProductType($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getProductType() === $this) {
                $product->setProductType(null);
            }
        }

        return $this;
    }
}
