<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CartRepository")
 */
class Cart
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", mappedBy="cart")
     */
    private $consumer;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CartItem", mappedBy="cart", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConsumer(): ?User
    {
        return $this->consumer;
    }

    public function setConsumer(?User $consumer): self
    {
        $this->consumer = $consumer;

        // set (or unset) the owning side of the relation if necessary
        $newCart = $consumer === null ? null : $this;
        if ($newCart !== $consumer->getCart()) {
            $consumer->setCart($newCart);
        }

        return $this;
    }

    /**
     * @return Collection|CartItem[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(CartItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setCart($this);
        }

        return $this;
    }

    public function removeItem(CartItem $item): self
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            // set the owning side to null (unless already changed)
            if ($item->getCart() === $this) {
                $item->setCart(null);
            }
        }

        return $this;
    }








}
