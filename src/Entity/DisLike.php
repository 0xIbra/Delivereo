<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DisLikeRepository")
 * @UniqueEntity(message="Vous avez déjà effectuée cette action.", fields={"target", "user"})
 */
class DisLike
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"customer", "owner"})
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="dislikes")
     * @Assert\NotNull()
     */
    private $target;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="dislikes")
     * @Assert\NotNull()
     * @Serializer\Groups({"owner", "admin"})
     */
    private $user;


    /**
     * @ORM\Column(name="disliked_at", type="datetime", nullable=true)
     * @Serializer\Groups({"customer", "owner"})
     */
    private $disliked_at;


    public function __construct()
    {
        $this->disliked_at = new \DateTime();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDislikedAt(): ?\DateTimeInterface
    {
        return $this->disliked_at;
    }

    public function setDislikedAt(?\DateTimeInterface $disliked_at): self
    {
        $this->disliked_at = $disliked_at;

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
