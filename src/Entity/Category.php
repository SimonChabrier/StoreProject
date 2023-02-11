<?php

// Homme Femme Enfant...

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

// add groups for serialization
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * 
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"product:read"})
     */
    private $name;

    /**
     * @ORM\ManyToMany(
     *      targetEntity=SubCategory::class, 
     *      mappedBy="categories", 
     *      fetch="EXTRA_LAZY"
     *  )
     * 
     */
    private $subCategories;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $listOrder = '0000';

    /**
     * @ORM\OneToMany(
     *      targetEntity=Product::class, 
     *      mappedBy="category", 
     *      fetch="EAGER"
     *  )
     * 
     */
    private $products;

    /**
     * @ORM\Column(type="boolean")
     */
    private $showOnHome = true;

    public function __construct()
    {
        $this->subCategories = new ArrayCollection();
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
            $subCategory->addCategory($this);
        }

        return $this;
    }

    public function removeSubCategory(SubCategory $subCategory): self
    {
        if ($this->subCategories->removeElement($subCategory)) {
            $subCategory->removeCategory($this);
        }

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
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function isShowOnHome(): ?bool
    {
        return $this->showOnHome;
    }

    public function setShowOnHome(bool $showOnHome): self
    {
        $this->showOnHome = $showOnHome;

        return $this;
    } 
}
