<?php

namespace App\Entity;

use App\Repository\TweetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TweetRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Tweet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(
        max: 500,
        maxMessage: 'El mensaje no debe sobrepasar los {{ limit }} caracteres',
    )]
    private ?string $content = null;

    private ?string $formattedContent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $likes = null;

    #[ORM\ManyToOne(inversedBy: 'tweets', fetch:"EAGER")]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'tweet', fetch:"LAZY", targetEntity: Like::class)]
    private Collection $likesEntity;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    public function __construct()
    {
        $this->likesEntity = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        //$this->formatContent();
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getFormattedContent(): ?string
    {
        //Este campo es calculado en el servicio FormattedContentService
        return $this->formattedContent;
    }

    public function setFormattedContent(string $formattedContent): static
    {
        $this->formattedContent = $formattedContent;

        return $this;
    }
    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): static
    {
        $this->likes = $likes;

        return $this;
    }
    public function addLike(): static
    {
        $this->likes++;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikesEntity(): Collection
    {
        return $this->likesEntity;
    }

    public function addLikesEntity(Like $likesEntity): static
    {
        if (!$this->likesEntity->contains($likesEntity)) {
            $this->likesEntity->add($likesEntity);
            $likesEntity->setTweet($this);
        }

        return $this;
    }

    public function removeLikesEntity(Like $likesEntity): static
    {
        if ($this->likesEntity->removeElement($likesEntity)) {
            // set the owning side to null (unless already changed)
            if ($likesEntity->getTweet() === $this) {
                $likesEntity->setTweet(null);
            }
        }

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
