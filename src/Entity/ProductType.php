<?php

// Jean Tea-Shirt Basket...

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
     * @ORM\OneToMany(
     *      targetEntity=Product::class, 
     *      mappedBy="productType", 
     *      cascade={"persist"}, 
     *      fetch="EAGER"
     *  )
     */
    private $products;

    /**
     * @ORM\ManyToMany(
     *      targetEntity=SubCategory::class, 
     *      mappedBy="productType"
     *  )
     */
    private $subCategories;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $typeData = [];


    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->subCategories = new ArrayCollection();
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

    public function __toString()
    {
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

    /**
     * @return Collection<int, SubCategory>
     */
    public function getSubCategories(): Collection
    {
        return $this->subCategories;
    }

    public function addSubCategory(SubCategory $subCategory): self
    {
        if (!$this->subCategories->contains($subCategory)) {
            $this->subCategories[] = $subCategory;
            $subCategory->addProductType($this);
        }

        return $this;
    }

    public function removeSubCategory(SubCategory $subCategory): self
    {
        if ($this->subCategories->removeElement($subCategory)) {
            $subCategory->removeProductType($this);
        }

        return $this;
    }

    public function getTypeData(): ?array
    {
        return $this->typeData;
    }

    public function setTypeData(?array $typeData): self
    {
        $this->typeData = $typeData;

        return $this;
    }

    
}
