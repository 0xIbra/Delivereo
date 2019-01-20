<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MenuRepository")
 */
class Menu
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"owner", "customer", "cart"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom du menu est obligatoire.")
     * @Serializer\Groups({"owner", "customer", "cart"})
     */
    private $name;


    /**
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     * @Serializer\Groups({"owner", "customer", "cart"})
     */
    private $description;

    /**
     * @ORM\Column(name="price", type="float")
     * @Assert\NotNull(message="Le prix du menu est obligatoire.")
     * @Serializer\Groups({"owner", "customer", "cart"})
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"owner", "customer", "cart"})
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="menus")
     * @Assert\NotNull()
     * @Serializer\Groups({"owner", "customer", "cart"})
     */
    private $category;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="menus", cascade={"persist"})
     * @Assert\NotNull()
     */
    private $restaurant;


    /**
     * @var Comment
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Comment", inversedBy="targetMenu", cascade={"persist", "remove"})
     * @Serializer\Groups({"admin", "owner", "customer"})
     */
    private $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return Menu
     */
    public function setPrice($price): self {
        $this->price = $price;

        return $this;
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

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): self
    {
        $this->restaurant = $restaurant;

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

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
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
}
