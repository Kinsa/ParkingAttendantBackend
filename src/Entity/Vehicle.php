<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $vrm = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $time_in = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVrm(): ?string
    {
        return $this->vrm;
    }

    public function setVrm(string $vrm): static
    {
        $this->vrm = $vrm;

        return $this;
    }

    public function getTimeIn(): ?\DateTimeImmutable
    {
        return $this->time_in;
    }

    public function setTimeIn(\DateTimeImmutable $time_in): static
    {
        $this->time_in = $time_in;

        return $this;
    }
}
