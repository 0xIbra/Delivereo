<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LikeRepository")
 */
class Like
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="likes")
     */
    private $target;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="likes")
     */
    private $user;


    /**
     * @ORM\Column(name="liked_at", type="datetime", nullable=true)
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
