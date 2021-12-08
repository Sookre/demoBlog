<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Article::class, mappedBy="category", orphanRemoval=true)
     */
    private $yes;

    public function __construct()
    {
        $this->yes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

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

    /**
     * @return Collection|Article[]
     */
    public function getYes(): Collection
    {
        return $this->yes;
    }

    public function addYe(Article $ye): self
    {
        if (!$this->yes->contains($ye)) {
            $this->yes[] = $ye;
            $ye->setCategory($this);
        }

        return $this;
    }

    public function removeYe(Article $ye): self
    {
        if ($this->yes->removeElement($ye)) {
            // set the owning side to null (unless already changed)
            if ($ye->getCategory() === $this) {
                $ye->setCategory(null);
            }
        }

        return $this;
    }
}
