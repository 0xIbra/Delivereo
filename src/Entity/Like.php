<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LikeRepository")
 * @ORM\Table(name="liked")
 * @UniqueEntity(message="Vous avez déjà effectuée cette action.", fields={"target", "user"})
 */
class Like
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"customer", "owner"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="likes")
     * @Assert\NotNull()
     * @Serializer\Groups({"customer", "owner"})
     */
    private $target;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="likes")
     * @Assert\NotNull()
     */
    private $user;


    /**
     * @ORM\Column(name="issued_at", type="datetime", nullable=true)
     * @Serializer\Groups({"customer", "owner"})
     */
    private $liked_at;


    public function __construct()
    {
        $this->liked_at = new \DateTime();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLikedAt(): ?\DateTimeInterface
    {
        return $this->liked_at;
    }

    public function setLikedAt(?\DateTimeInterface $liked_at): self
    {
        $this->liked_at = $liked_at;

        return $this;
    }

    public function getTarget(): ?Restaurant
    {
        return $this->target;
    }

    public function setTarget(?Restaurant $target): self
    {
        $this->target = $target;

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


}
