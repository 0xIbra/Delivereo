<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RestaurantRepository")
 */
class Restaurant
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"front", "owner", "customer"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom du restaurant est obligatoire.")
     * @Serializer\Groups({"front", "owner", "customer"})
     */
    private $name;

    /**
     * @ORM\Column(name="number", type="string", length=20, nullable=true)
     * @Serializer\Groups({"owner", "customer"})
     *
     */
    private $number;

    /**
     * @ORM\Column(type="time")
     * @Assert\NotNull(message="Les horaires sont obligatoires.")
     * @Serializer\Groups({"owner", "customer"})
     */
    private $opensAt;

    /**
     * @ORM\Column(type="time")
     * @Assert\NotNull(message="Les horaires sont obligatoires.")
     * @Serializer\Groups({"owner", "customer"})
     */
    private $closesAt;


    /**
     * @ORM\Column(name="enabled", type="boolean")
     * @Serializer\Groups({"owner", "admin"})
     */
    private $enabled;


    /**
     * @ORM\Column(name="published", type="boolean")
     * @Serializer\Groups({"owner", "admin"})
     */
    private $published;


    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     * @Serializer\Groups({"owner", "admin"})
     */
    private $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="restaurants", cascade={"persist"})
     * @Serializer\Groups({"owner", "customer"})
     */
    private $categories;

    /**
     * @var Image
     * @ORM\ManyToOne(targetEntity="App\Entity\Image", cascade={"persist", "remove"})
     * @Serializer\Groups({"owner", "customer"})
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Like", mappedBy="target", cascade={"persist", "remove"})
     * @Serializer\Groups({"owner", "customer"})
     */
    private $likes;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DisLike", mappedBy="target", cascade={"persist", "remove"})
     * @Serializer\Groups({"owner", "customer"})
     */
    private $dislikes;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", mappedBy="restaurant")
     * @Assert\NotNull()
     * @Serializer\Groups({"owner", "customer"})
     */
    private $owner;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="managedRestaurant")
     * @Serializer\Groups({"owner"})
     */
    private $managers;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="restaurants")
     * @Assert\NotNull(message="Merci de entrer une ville valide.")
     * @Serializer\Groups({"owner", "customer", "front"})
     */
    private $city;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Address", cascade={"persist", "remove"})
     * @Assert\NotNull(message="L'adresse est obligatoire.")
     * @Serializer\Groups({"owner", "customer", "front"})
     */
    private $address;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Menu", mappedBy="restaurant", cascade={"persist", "remove"})
     * @Serializer\Groups({"owner", "customer"})
     */
    private $menus;


    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Order", inversedBy="restaurants")
     * @Serializer\Groups({"owner"})
     */
    private $orders;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\StripeClient", inversedBy="restaurant", cascade={"persist", "remove"})
     * @Serializer\Groups({"owner"})
     */
    private $stripeClient;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->dislikes = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->enabled = false;
        $this->published = false;
        $this->created_at = new \DateTime();
        $this->managers = new ArrayCollection();
        $this->orders = new ArrayCollection();
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

    public function getOpensAt(): ?\DateTimeInterface
    {
        return $this->opensAt;
    }

    public function setOpensAt(?\DateTimeInterface $opensAt): self
    {
        $this->opensAt = $opensAt;

        return $this;
    }

    public function getClosesAt(): ?\DateTimeInterface
    {
        return $this->closesAt;
    }

    public function setClosesAt(?\DateTimeInterface $closesAt): self
    {
        $this->closesAt = $closesAt;

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
            $menu->setRestaurant($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->contains($menu)) {
            $this->menus->removeElement($menu);
            // set the owning side to null (unless already changed)
            if ($menu->getRestaurant() === $this) {
                $menu->setRestaurant(null);
            }
        }

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

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
     * @return Collection|Like[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setTarget($this);
        }

        return $this;
    }

    public function removeLike(Like $like): self
    {
        if ($this->likes->contains($like)) {
            $this->likes->removeElement($like);
            // set the owning side to null (unless already changed)
            if ($like->getTarget() === $this) {
                $like->setTarget(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DisLike[]
     */
    public function getDislikes(): Collection
    {
        return $this->dislikes;
    }

    public function addDislike(DisLike $dislike): self
    {
        if (!$this->dislikes->contains($dislike)) {
            $this->dislikes[] = $dislike;
            $dislike->setTarget($this);
        }

        return $this;
    }

    public function removeDislike(DisLike $dislike): self
    {
        if ($this->dislikes->contains($dislike)) {
            $this->dislikes->removeElement($dislike);
            // set the owning side to null (unless already changed)
            if ($dislike->getTarget() === $this) {
                $dislike->setTarget(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isEnabled()
    {
        if ($this->enabled)
        {
            return true;
        }else
        {
            return false;
        }
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getStripeClient(): ?StripeClient
    {
        return $this->stripeClient;
    }

    public function setStripeClient(?StripeClient $stripeClient): self
    {
        $this->stripeClient = $stripeClient;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        // set (or unset) the owning side of the relation if necessary
        $newRestaurant = $owner === null ? null : $this;
        if ($newRestaurant !== $owner->getRestaurant()) {
            $owner->setRestaurant($newRestaurant);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getManagers(): Collection
    {
        return $this->managers;
    }

    public function addManager(User $manager): self
    {
        if (!$this->managers->contains($manager)) {
            $this->managers[] = $manager;
            $manager->setManagedRestaurant($this);
        }

        return $this;
    }

    public function removeManager(User $manager): self
    {
        if ($this->managers->contains($manager)) {
            $this->managers->removeElement($manager);
            // set the owning side to null (unless already changed)
            if ($manager->getManagedRestaurant() === $this) {
                $manager->setManagedRestaurant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
        }

        return $this;
    }

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }





}
