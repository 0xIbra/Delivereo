<?php
/**
 * Created by PhpStorm.
 * User: IbrahimTchee
 * Date: 26/08/2018
 * Time: 18:51
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User
 * @package App\Entity
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
// * @Serializer\ExclusionPolicy("all")
 */
class User extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose()
     * @Serializer\Type("string")
     */
    protected $id;

    /**
     * @ORM\Column(name="first_name", type="string", nullable=true)
     * @Assert\NotBlank(message="Le prénom ne doit pas être vide.")
     * @Serializer\Expose()
     * @Serializer\Type("string")
     */
    private $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", nullable=true)
     * @Assert\NotBlank(message="Le nom ne doit pas être vide.")
     * @Serializer\Expose()
     */
    private $lastName;


    /**
     * @var
     *
     * @Assert\Length(min="6", minMessage="Le mot de passe est trop court, il doit avoir au minimum 6 caractères.")
     */
    protected $plainPassword;


    /**
     * @var Image
     * @ORM\ManyToOne(targetEntity="App\Entity\Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Expose()
     */
    private $image;

    /**
     * @var Gender
     * @ORM\ManyToOne(targetEntity="App\Entity\Gender", cascade={"persist"})
     * @Serializer\Expose()
     */
    private $gender;

    /**
     * @var Address
     * @ORM\ManyToMany(targetEntity="App\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $addresses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Like", mappedBy="user", cascade={"persist", "remove"})
     */
    private $likes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DisLike", mappedBy="user", cascade={"persist", "remove"})
     */
    private $dislikes;


    /**
     * @var SocialLink
     * @ORM\OneToMany(targetEntity="App\Entity\SocialLink", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Expose()
     */
    private $socialLinks;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Restaurant", inversedBy="owner", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Expose()
     */
    private $restaurant;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="managers")
     */
    private $managedRestaurant;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Cart", inversedBy="consumer", cascade={"persist", "remove"})
     * @Serializer\Expose()
     */
    private $cart;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="consumer")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Expose()
     */
    private $orders;

    /**
     * @var Comment
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="commentedBy", cascade={"persist", "remove"})
     */
    private $comments;


    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     * @Serializer\Expose()
     */
    private $createdAt;

    public function __construct()
    {
        parent::__construct();
        $this->orders = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->addRole('ROLE_CONSUMER');
        $this->addresses = new ArrayCollection();
        $this->socialLinks = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->dislikes = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->cart = new Cart();
    }

    /**
     * @return mixed
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName): void {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void {
        $this->lastName = $lastName;
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $order->setConsumer($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getConsumer() === $this) {
                $order->setConsumer(null);
            }
        }

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
            $comment->setCommentedBy($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getCommentedBy() === $this) {
                $comment->setCommentedBy(null);
            }
        }

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

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return Collection|SocialLink[]
     */
    public function getSocialLinks(): Collection
    {
        return $this->socialLinks;
    }

    public function addSocialLink(SocialLink $socialLink): self
    {
        if (!$this->socialLinks->contains($socialLink)) {
            $this->socialLinks[] = $socialLink;
            $socialLink->setUser($this);
        }

        return $this;
    }

    public function removeSocialLink(SocialLink $socialLink): self
    {
        if ($this->socialLinks->contains($socialLink)) {
            $this->socialLinks->removeElement($socialLink);
            // set the owning side to null (unless already changed)
            if ($socialLink->getUser() === $this) {
                $socialLink->setUser(null);
            }
        }

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
            $like->setUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|Address[]
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
        }

        return $this;
    }

    public function removeLike(Like $like): self
    {
        if ($this->likes->contains($like)) {
            $this->likes->removeElement($like);
            // set the owning side to null (unless already changed)
            if ($like->getUser() === $this) {
                $like->setUser(null);
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
            $dislike->setUser($this);
        }

        return $this;
    }

    public function removeDislike(DisLike $dislike): self
    {
        if ($this->dislikes->contains($dislike)) {
            $this->dislikes->removeElement($dislike);
            // set the owning side to null (unless already changed)
            if ($dislike->getUser() === $this) {
                $dislike->setUser(null);
            }
        }

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): self
    {
        $this->cart = $cart;

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

    public function getManagedRestaurant(): ?Restaurant
    {
        return $this->managedRestaurant;
    }

    public function setManagedRestaurant(?Restaurant $managedRestaurant): self
    {
        $this->managedRestaurant = $managedRestaurant;

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
