<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\Traits\Timestamps;
use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 * @ORM\Table(name="tab_comments")
 *
 * @ApiResource(
 *     collectionOperations = {"get" = {"normalization_context" = {"groups" = "comment:list"}}},
 *     itemOperations = {"get" = {"normalization_context" = {"groups" = "comment:item"}}},
 *     order = {"createdAt" = "DESC"},
 *     paginationEnabled = false
 * )
 *
 * @ApiFilter(SearchFilter::class, properties = {"conference": "exact"})
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Comment
{
    use Timestamps;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({"comment:list", "comment:item"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     *
     * @Groups({"comment:list", "comment:item"})
     */
    private string $author;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     *
     * @Groups({"comment:list", "comment:item"})
     */
    private string $text;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email()
     *
     * @Groups({"comment:list", "comment:item"})
     */
    private string $email;

    /**
     * @ORM\ManyToOne(targetEntity=Conference::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Conference $conference;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"comment:list", "comment:item"})
     */
    private ?string $photoFilename;

    /**
     * @ORM\Column(type="string", length=255, options={"default": "submitted"})
     */
    private string $state = 'submitted';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    public function setConference(?Conference $conference): self
    {
        $this->conference = $conference;

        return $this;
    }

    public function getPhotoFilename(): ?string
    {
        return $this->photoFilename;
    }

    public function setPhotoFilename(?string $photoFilename): self
    {
        $this->photoFilename = $photoFilename;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getEmail();
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }
}
