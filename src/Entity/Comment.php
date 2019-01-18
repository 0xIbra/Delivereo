<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $content;


    /**
     * @var Menu
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Menu", mappedBy="comments", cascade={"persist"})
     */
    private $targetMenu;


    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     */
    private $commentedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="commented_at", type="datetime")
     */
    private $commentedAt;

    public function __construct()
    {
        $this->targetMenu = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCommentedAt(): ?\DateTimeInterface
    {
        return $this->commentedAt;
    }

    public function setCommentedAt(\DateTimeInterface $commentedAt): self
    {
        $this->commentedAt = $commentedAt;

        return $this;
    }

    /**
     * @return Collection|Menu[]
     */
    public function getTargetMenu(): Collection
    {
        return $this->targetMenu;
    }

    public function addTargetMenu(Menu $targetMenu): self
    {
        if (!$this->targetMenu->contains($targetMenu)) {
            $this->targetMenu[] = $targetMenu;
            $targetMenu->addComment($this);
        }

        return $this;
    }

    public function removeTargetMenu(Menu $targetMenu): self
    {
        if ($this->targetMenu->contains($targetMenu)) {
            $this->targetMenu->removeElement($targetMenu);
            $targetMenu->removeComment($this);
        }

        return $this;
    }

    public function getCommentedBy(): ?User
    {
        return $this->commentedBy;
    }

    public function setCommentedBy(?User $commentedBy): self
    {
        $this->commentedBy = $commentedBy;

        return $this;
    }
}
