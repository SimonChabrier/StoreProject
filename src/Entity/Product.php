<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

// add groups for serialization
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"product:read", "product:id"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     * @Groups({"product:read"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(
     *      targetEntity=Category::class, 
     *      inversedBy="products"
     *  )
     * @Groups({"product:read"})
     */
    private $category;

    /**
     * @ORM\ManyToOne(
     *      targetEntity=SubCategory::class, 
     *      inversedBy="products"
     *  )
     * @Groups({"product:read"})
     */
    private $subCategory;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $buyPrice;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"product:read"})
     */
    private $catalogPrice = '0000';

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"product:read"})
     */
    private $sellingPrice;

    /**
     * @ORM\Column(type="boolean")
     */
    private $visibility = 1;

    /**
     * Permet de calculer la marge brute d'un produit
     * n'est pas persisté en base de données
     */
    private $tauxMarque;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"product:read"})
     */
    private $inStock = 1;

    /**
     * @ORM\Column(type="string", length=5)
     * @Groups({"product:read"})
     */
    private $inStockQuantity = 0;

    /**
     * @ORM\ManyToOne(
     *      targetEntity=ProductType::class, 
     *      inversedBy="products"
     *  )
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"product:read"})
     */
    private $productType;

    /**
     * @ORM\OneToMany(
     *      targetEntity=Comment::class, 
     *      mappedBy="product",
     *      cascade={"remove"}
     *  )
     * @Groups({"product:read"})
     */
    private $comments;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"product:read"})
     */
    private $productData = [];

    /**
     * @ORM\ManyToOne(
     * targetEntity=Brand::class, 
     * inversedBy="products",
     * cascade={"persist"}
     * )
     * @Groups({"product:read"})
     */
    private $brand;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Picture::class, 
     * mappedBy="product", 
     * cascade={"remove"})
     * @Groups({"product:read"})
     */
    private $pictures;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $reservedQuantity = 0;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->pictures = new ArrayCollection();
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getSubCategory(): ?SubCategory
    {
        return $this->subCategory;
    }

    public function setSubCategory(?SubCategory $subCategory): self
    {
        $this->subCategory = $subCategory;

        return $this;
    }
    public function __toString()
    {
        return $this->name;
    }

    public function getBuyPrice(): ?string
    {
        return $this->buyPrice;
    }

    public function setBuyPrice(string $buyPrice): self
    {
        $this->buyPrice = $buyPrice;

        return $this;
    }

    public function getCatalogPrice(): ?string
    {
        return $this->catalogPrice;
    }

    public function setCatalogPrice(string $catalogPrice): self
    {
        $this->catalogPrice = $catalogPrice;

        return $this;
    }

    public function getSellingPrice(): ?string
    {
        return $this->sellingPrice;
    }

    public function setSellingPrice(string $sellingPrice): self
    {
        $this->sellingPrice = $sellingPrice;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setProduct($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getProduct() === $this) {
                $comment->setProduct(null);
            }
        }

        return $this;
    }

    public function isVisibility(): ?bool
    {
        return $this->visibility;
    }

    public function setVisibility(bool $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function tauxMarque(): ?string
    {   
        // On calcule la marge commerciale HT pour easyAdmin
        //TODO A paufiner et vérifier la formule de calcul

        // pour EasyAdmin on retourne 0 si le prix d'achat ou de vente est à 0
        // pour ne pas avoir de division par 0 et donc une erreur à la création d'un produit
        if ($this->buyPrice == 0 || $this->sellingPrice == 0) {
            return '0 %';
        }
        
        $achatHt = $this->buyPrice * 0.8;
        $venteHt = $this->sellingPrice * 0.8;

        $margeCommerciale = $venteHt - $achatHt;
        $tauxDeMarge = ($margeCommerciale / $achatHt) * 100;
        $tauxDeMarque = ($margeCommerciale / $venteHt) * 100;

        $result = round($tauxDeMarge , 2) . ' %';
        if ($result <= 25) {
            return '🔴 ' . $this->tauxMarque = $result;
        } elseif ($result > 25 && $result <= 40) {
            return '🟡 ' . $this->tauxMarque = $result;
        } elseif ($result > 40) {
            return '🟢 ' .$this->tauxMarque = $result;
        } else {
            return $this->tauxMarque = 'informations incomplètes';
        }

        //return $this->tauxMarque = round($tauxDeMarque , 2) . ' %';
    }

    public function isInStock(): ?bool
    {
        return $this->inStock;
    }

    public function setInStock(bool $inStock): self
    {
        $this->inStock = $inStock;

        return $this;
    }

    public function getInStockQuantity(): ?string
    {
        return $this->inStockQuantity;
    }

    public function setInStockQuantity(string $inStockQuantity): self
    {
        $this->inStockQuantity = $inStockQuantity;

        return $this;
    }

    public function getProductType(): ?ProductType
    {
        return $this->productType;
    }

    public function setProductType(?ProductType $productType): self
    {
        $this->productType = $productType;

        return $this;
    }

    public function getProductData(): ?array
    {   
        
        return $this->productData;
    }

    public function setProductData(?array $productData): self
    {   

        $this->productData = $productData;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Picture>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setProduct($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getProduct() === $this) {
                $picture->setProduct(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getReservedQuantity(): ?int
    {
        return $this->reservedQuantity;
    }

    public function setReservedQuantity(int $reservedQuantity): self
    {
        $this->reservedQuantity = $reservedQuantity;

        return $this;
    }
}
