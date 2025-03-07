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
    private $visibility = true;

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
     * @ORM\OrderBy({"createdAt" = "DESC"})
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
    
    // est ce que le produit est en stock ou pas
    /**
     * @ORM\Column(type="boolean")
     * @Groups({"product:read"})
     */
    private $isInStock = true;
    
    // la quantité réélle en stock 
    /**
     * @ORM\Column(type="string", length=5)
     * @Groups({"product:read"})
     */
    private $inStockQuantity = 0;

    // la quantité en commande fournisseur
    /**
     * @ORM\Column(type="integer")
     */
    private $inSupplierOrderQuantity = 0;
    
    // la quantité réservée pour les commandes en cours
    /**
     * @ORM\Column(type="integer")
     */
    private $onOrderQuantity = 0;

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
    public function __toString() :string
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
        //return number_format($this->catalogPrice, 2, ',', ' ');
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

    /**
     * Get the value of visibility
     */ 
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set the value of visibility
     *
     * @return  self
     */ 
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }


    /**
     * Get the value of isInStock
     */ 
    public function getIsInStock()
    {
        return $this->isInStock;
    }

    /**
     * Set the value of isInStock
     *
     * @return  self
     */ 
    public function setIsInStock($isInStock)
    {
        $this->isInStock = $isInStock;

        return $this;
    }

    public function margeBrute(): ?string
    {   
        // formule de calcul de la marge brute
        // Taux de Marge Brute (%) = [(Prix de Vente - Coût d'Achat) / Prix de Vente] * 100

        // Pour EasyAdmin, on retourne 0 si le prix d'achat ou de vente est à 0
        // pour ne pas avoir de division par 0 et donc une erreur à la création d'un produit
        if ($this->buyPrice == 0 || $this->sellingPrice == 0) {
            return '0 %';
        }
        
        $achatHt = $this->buyPrice;
        $venteTtc = $this->sellingPrice;

        $margeBrute = $venteTtc - $achatHt;
        $tauxDeMargeBrute = ($margeBrute / $venteTtc) * 100;

       // $result = round($tauxDeMargeBrute, 2);
        $result = number_format($tauxDeMargeBrute, 2, ',', ' ');
    
        if ($result <= 25) {
            return '🔴 ' . $result . ' %';
        } elseif ($result <= 40) {
            return '🟡 ' . $result . ' %';
        } else {
            return '🟢 ' . $result . ' %';
        }
    }

    public function margeNette(): ?string
    {   
        // formule de calcul de la marge nette
        // Marge Nette (%) = [(Prix de Vente - Coût d'Achat) / Prix de Vente] * (1 - Taux de TVA) * 100

        // Pour EasyAdmin, on retourne 0 si le prix d'achat ou de vente est à 0
        // pour ne pas avoir de division par 0 et donc une erreur à la création d'un produit
        if ($this->buyPrice == 0 || $this->sellingPrice == 0) {
            return '0 %';
        }
        
        $achatHt = $this->buyPrice;
        $venteTtc = $this->sellingPrice;

        // Taux de TVA en décimal
        $tauxTVA = 0.2;

        $margeBrute = $venteTtc - $achatHt;
        $margeNette = $margeBrute * (1 - $tauxTVA);

        $tauxDeMargeNette = ($margeNette / $venteTtc) * 100;

        //$result = round($tauxDeMargeNette, 2);
        $result = number_format($tauxDeMargeNette, 2, ',', ' ');
        
        if ($result <= 25) {
            return '🔴 ' . $result . ' %';
        } elseif ($result <= 40) {
            return '🟡 ' . $result . ' %';
        } else {
            return '🟢 ' . $result . ' %';
        }
    }

    public function coefficientMarge(): ?float
    {   
        // Pour EasyAdmin, on retourne null si le prix de vente est à 0
        // pour éviter toute division par 0 et une erreur à la création d'un produit
        if ($this->buyPrice == 0 || $this->sellingPrice == 0) {
            return null;
        }
        $achatHt = $this->buyPrice;
        $venteHt = $this->sellingPrice * 0.8;
        // Calcul du coefficient de marge
        $coefficientMarge = $venteHt / $achatHt;
        
        return round($coefficientMarge, 2);
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

    public function getOnOrderQuantity(): ?int
    {
        return $this->onOrderQuantity;
    }

    public function setOnOrderQuantity(int $onOrderQuantity): self
    {
        $this->onOrderQuantity = $onOrderQuantity;

        return $this;
    }

    public function getInSupplierOrderQuantity(): ?int
    {
        return $this->inSupplierOrderQuantity;
    }

    public function setInSupplierOrderQuantity(int $inSupplierOrderQuantity): self
    {
        $this->inSupplierOrderQuantity = $inSupplierOrderQuantity;

        return $this;
    }
    
    // méthode qui vérifie si le produit est en stock ou pas
    public function checkIsInStock(): self
    {
        if ($this->inStockQuantity <= 0) {
            $this->isInStock = false;
        }
        return $this;
    }

}
