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
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @Serializer\ExclusionPolicy("all")
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
     */
    private $image;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Restaurant", mappedBy="owner", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $restaurants;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="consumer")
     * @ORM\JoinColumn(nullable=true)
     */
    private $orders;

    /**
     * @var Comment
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="commentedBy", cascade={"persist", "remove"})
     */
    private $comments;

    public function __construct()
    {
        parent::__construct();
        $this->restaurants = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->addRole('ROLE_CONSUMER');
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
            $restaurant->setOwner($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): self
    {
        if ($this->restaurants->contains($restaurant)) {
            $this->restaurants->removeElement($restaurant);
            // set the owning side to null (unless already changed)
            if ($restaurant->getOwner() === $this) {
                $restaurant->setOwner(null);
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

}