<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DisLikeRepository")
 */
class DisLike
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="dislikes")
     */
    private $target;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="dislikes")
     */
    private $user;


    /**
     * @ORM\Column(name="disliked_at", type="datetime", nullable=true)
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
