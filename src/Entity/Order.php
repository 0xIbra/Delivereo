<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 */
class Order
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="ordered_at", type="datetime")
     */
    private $orderedAt;

    /**
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(name="total_price", type="float")
     */
    private $totalPrice;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orders")
     */
    private $consumer;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Menu")
     */
    private $menu;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderedAt(): ?\DateTimeInterface
    {
        return $this->orderedAt;
    }

    public function setOrderedAt(\DateTimeInterface $orderedAt): self
    {
        $this->orderedAt = $orderedAt;

        return $this;
    }

    public function getConsumer(): ?User
    {
        return $this->consumer;
    }

    public function setConsumer(?User $consumer): self
    {
        $this->consumer = $consumer;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }
}
