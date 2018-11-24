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
     * @ORM\ManyToMany(targetEntity="App\Entity\Menu")
     */
    private $menus;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->consumer = new ArrayCollection();
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
     * @return Collection|Menu[]
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus[] = $menu;
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->contains($menu)) {
            $this->menus->removeElement($menu);
        }

        return $this;
    }




}
