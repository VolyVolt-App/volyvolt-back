<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $clientId = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'clientId', targetEntity: Consomation::class)]
    private Collection $consomations;

    public function __construct()
    {
        $this->consomations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Consomation>
     */
    public function getConsomations(): Collection
    {
        return $this->consomations;
    }

    public function addConsomation(Consomation $consomation): static
    {
        if (!$this->consomations->contains($consomation)) {
            $this->consomations->add($consomation);
            $consomation->setClientId($this);
        }

        return $this;
    }

    public function removeConsomation(Consomation $consomation): static
    {
        if ($this->consomations->removeElement($consomation)) {
            // set the owning side to null (unless already changed)
            if ($consomation->getClientId() === $this) {
                $consomation->setClientId(null);
            }
        }

        return $this;
    }
}
