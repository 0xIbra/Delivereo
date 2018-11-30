<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\Table(name="orders")
 * @ORM\HasLifecycleCallbacks()
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
     * @ORM\Column(name="order_number", type="string", length=255, nullable=true, unique=true)
     */
    private $orderNumber;

    /**
     * @ORM\Column(name="ordered_at", type="datetime")
     */
    private $orderedAt;

    /**
     * @ORM\Column(name="total_price", type="float")
     */
    private $totalPrice;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PaymentMethod")
     */
    private $paymentMethod;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Address")
     */
    private $deliveryAddress;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(nullable=true)
     */
    private $consumer;


    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Restaurant", mappedBy="orders")
     */
    private $restaurants;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderMenu", mappedBy="order", cascade={"persist", "remove"})
     */
    private $items;


    /**
     * @ORM\Column(name="order_fulfilled", type="boolean", nullable=false)
     */
    private $orderFulfilled;

    public function __construct()
    {
        $this->restaurants = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->orderedAt = new \DateTime();
        $this->orderFulfilled = false;
    }

    /**
     * @ORM\PostPersist()
     */
    public function initOrderNumber()
    {
        $this->orderNumber = md5($this->id);
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

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethod $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

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

    /**
     * @return Collection|OrderMenu[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderMenu $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeItem(OrderMenu $item): self
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            // set the owning side to null (unless already changed)
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
        }

        return $this;
    }

    public function getDeliveryAddress(): ?Address
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?Address $deliveryAddress): self
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * @return Collection|Restaurant[]
     */
    public function getRestaurants(): Collection
    {
        return $this->restaurants;
    }

    public function addRestaurant(Restaurant $restaurant): self
    {
        if (!$this->restaurants->contains($restaurant)) {
            $this->restaurants[] = $restaurant;
            $restaurant->addOrder($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): self
    {
        if ($this->restaurants->contains($restaurant)) {
            $this->restaurants->removeElement($restaurant);
            $restaurant->removeOrder($this);
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getOrderFulfilled(): ?bool
    {
        return $this->orderFulfilled;
    }

    public function setOrderFulfilled(bool $orderFulfilled): self
    {
        $this->orderFulfilled = $orderFulfilled;

        return $this;
    }




}
