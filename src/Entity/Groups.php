<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupsRepository")
 */
class Groups
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subgroups;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tag;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $createdby;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $accessibility;

    /**
     * @ORM\Column(type="integer")
     */
    private $subscriber;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subscribersName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSubgroups(): ?string
    {
        return $this->subgroups;
    }

    public function setSubgroups(string $subgroups): self
    {
        $this->subgroups = $subgroups;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getCreatedby(): ?string
    {
        return $this->createdby;
    }

    public function setCreatedby(string $createdby): self
    {
        $this->createdby = $createdby;

        return $this;
    }

    public function getAccessibility(): ?string
    {
        return $this->accessibility;
    }

    public function setAccessibility(string $accessibility): self
    {
        $this->accessibility = $accessibility;

        return $this;
    }

    public function getSubscriber(): ?int
    {
        return $this->subscriber;
    }

    public function setSubscriber(int $subscriber): self
    {
        $this->subscriber = $subscriber;

        return $this;
    }

    public function getSubscribersName(): ?string
    {
        return $this->subscribersName;
    }

    public function setSubscribersName(string $subscribersName): self
    {
        $this->subscribersName = $subscribersName;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
