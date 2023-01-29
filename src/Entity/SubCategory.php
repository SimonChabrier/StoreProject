<?php

// Vétement Chaussures Accessoires...

namespace App\Entity;

use App\Repository\SubCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubCategoryRepository::class)
 */
class SubCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=4)
     * 
     */
    private $listOrder = '0000';

    /**
     * @ORM\ManyToMany(
     *      targetEntity=Category::class, 
     *      inversedBy="subCategories",
     *      fetch="EXTRA_LAZY",
     *      cascade={"persist"}
     *  )
     */
    private $categories;

    /**
     * @ORM\OneToMany(
     *      targetEntity=Product::class, 
     *      mappedBy="subCategory", 
     *      fetch="EAGER"
     *  )
     */
    private $products;

    /**
     * @ORM\ManyToMany(targetEntity=ProductType::class, inversedBy="subCategories")
     */
    private $productType;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->productType = new ArrayCollection();
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
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getListOrder(): ?string
    {   
        
        return $this->listOrder;
    }

    public function setListOrder(string $listOrder): self
    {
        $this->listOrder = $listOrder;

        return $this;
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
            $product->setSubCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getSubCategory() === $this) {
                $product->setSubCategory(null);
            }
        }

        return $this;
    }

    public function __toString()
    {   
        return $this->name;
    }

    /**
     * @return Collection<int, ProductType>
     */
    public function getProductType(): Collection
    {
        return $this->productType;
    }

    public function addProductType(ProductType $productType): self
    {
        if (!$this->productType->contains($productType)) {
            $this->productType[] = $productType;
        }

        return $this;
    }

    public function removeProductType(ProductType $productType): self
    {
        $this->productType->removeElement($productType);

        return $this;
    }

    // Retourne le nom de la sous catégorie et le nom de la catégorie associée pour l'affichage dans les formulaire
    public function getSubCategoryName(): string
    {
        $categoryName = $this->categories->first()->getName();
        return $categoryName . ' : ' . $this->name;
    }

    // retourner le nom de la catégorie associée à la sous catégorie
    public function getCategoryName(): string
    {
        return $this->categories->first()->getName();
    }

}
