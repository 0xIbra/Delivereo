<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SocialLinkRepository")
 */
class SocialLink
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Social
     * @ORM\ManyToOne(targetEntity="App\Entity\Social")
     * @Assert\NotNull()
     */
    private $type;

    /**
     * @var string
     * @ORM\Column(name="url", type="string", length=255)
     * @Assert\NotBlank(message="L'url ne doit pas être vide.")
     * @Assert\Url(message="Merci d'entrer une URL valide.")
     */
    private $url;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="socialLinks")
     * @Assert\NotNull()
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getType(): ?Social
    {
        return $this->type;
    }

    public function setType(?Social $type): self
    {
        $this->type = $type;

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
