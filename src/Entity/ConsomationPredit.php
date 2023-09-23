<?php

namespace App\Entity;

use App\Repository\ConsomationPreditRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsomationPreditRepository::class)]
class ConsomationPredit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $consomation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConsomation(): ?float
    {
        return $this->consomation;
    }

    public function setConsomation(float $consomation): static
    {
        $this->consomation = $consomation;

        return $this;
    }
}
