<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\Collection;
// add groups for serialization
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{   
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(
     *      targetEntity=OrderItem::class, 
     *      mappedBy="orderRef", 
     *      cascade={"persist", "remove"}, 
     *      orphanRemoval=true
     *  )
     */
    private $items;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status = self::CART_STATUS;
    
    /**
     * An order that is in progress, not placed yet.
     * Une commande en cours, pas encore passée.
     * @var string
     */
    const CART_STATUS = 'new';

    /**
     * @ORM\ManyToOne(
     * targetEntity=User::class, 
     * inversedBy="orders"
     * )
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $userIdentifier;

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
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * On ajoute un item à la commande.
     * Si l'item existe déjà, on met à jour la quantité.
     *
     * @param OrderItem $item
     * @return $this
     */
    public function addItem(OrderItem $item): self
    {   
        foreach ($this->getItems() as $existingItem) {
            if ($existingItem->equals($item)) {
                $existingItem->setQuantity(
                    $existingItem->getQuantity() + $item->getQuantity()
                );
                return $this;
            }
        }

        $this->items[] = $item;
        $item->setOrderRef($this);

        return $this;
    }

    /**
     * On supprime un item de la commande.
     *
     * @param OrderItem $item
     * @return $this
     */
    public function removeItem(OrderItem $item): self
    {   
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getOrderRef() === $this) {
                $item->setOrderRef(null);
            }
        }
        return $this;
    }

    /**
     * Pour supprimer tous les items de la commande en une fois
     *
     * @return $this
     */
    public function removeItems(): self
    {
        foreach ($this->getItems() as $item) {
            $this->removeItem($item);
        }

        return $this;
    }

    /**
     * On calcule le total de la commande en additionnant le total de chaque item.
     *
     * @return array
     */
    public function getTotal(): array
    {
        $total = 0;
        foreach ($this->getItems() as $item) {
            $total += $item->getTotal();
        }

        $totalInCents = $total * 100; // Multiplication avant le formatage

        $formatedTotal = number_format($total, 2 , ',', ' '); // Formatage avec 2 décimales, une virgule et un espace entre les milliers
        $formatedTotalInCents = number_format($totalInCents, 0, '', ''); // Formatage sans décimales et sans séparateur de milliers
        
        return [
            'total' => $formatedTotal,
            'totalInCents' => $formatedTotalInCents
        ];
    }


    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getUserIdentifier(): ?string
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(string $userIdentifier): self
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    
}
