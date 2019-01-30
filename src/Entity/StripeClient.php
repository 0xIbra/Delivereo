<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StripeClientRepository")
 */
class StripeClient
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(name="account_id", type="string", length=255, nullable=false, unique=true)
     * @Serializer\Groups({"admin", "owner"})
     */
    private $accountId;

    /**
     * @ORM\Column(name="stripe_publishable_key", type="string", length=255, nullable=false, unique=true)
     */
    private $stripePublishableKey;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Restaurant", mappedBy="stripeClient")
     */
    private $restaurant;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccountId(): ?string
    {
        return $this->accountId;
    }

    public function setAccountId(string $accountId): self
    {
        $this->accountId = $accountId;

        return $this;
    }

    public function getStripePublishableKey(): ?string
    {
        return $this->stripePublishableKey;
    }

    public function setStripePublishableKey(string $stripePublishableKey): self
    {
        $this->stripePublishableKey = $stripePublishableKey;

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): self
    {
        $this->restaurant = $restaurant;

        // set (or unset) the owning side of the relation if necessary
        $newStripeClient = $restaurant === null ? null : $this;
        if ($newStripeClient !== $restaurant->getStripeClient()) {
            $restaurant->setStripeClient($newStripeClient);
        }

        return $this;
    }
}
