<?php

namespace App\Entity;

use App\Repository\HoraireRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HoraireRepository::class)]
#[ORM\Table(name: 'horaire')]
#[UniqueEntity(fields: ['jour'], message: 'Un horaire existe déjà pour ce jour.')]
class Horaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $jour = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $heureOuverture = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $heureFermeture = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour(): ?string
    {
        return $this->jour;
    }

    public function setJour(string $jour): static
    {
        $this->jour = $jour;

        return $this;
    }

    public function getHeureOuverture(): ?string
    {
        return $this->heureOuverture;
    }

    public function setHeureOuverture(string $heureOuverture): static
    {
        $this->heureOuverture = $heureOuverture;

        return $this;
    }

    public function getHeureFermeture(): ?string
    {
        return $this->heureFermeture;
    }

    public function setHeureFermeture(string $heureFermeture): static
    {
        $this->heureFermeture = $heureFermeture;

        return $this;
    }
}
