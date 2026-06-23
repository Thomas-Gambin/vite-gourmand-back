<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'commande')]
#[ORM\UniqueConstraint(name: 'UNIQ_commande_numero_commande', fields: ['numeroCommande'])]
#[UniqueEntity(fields: ['numeroCommande'])]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    private ?string $numeroCommande = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?\DateTimeImmutable $dateCommande = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeImmutable $datePrestation = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $heureLivraison = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private ?string $prixMenu = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?int $nombrePersonne = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private ?string $prixLivraison = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $statut = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $pretMateriel = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $restitutionMateriel = false;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $adressePrestation = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $villePrestation = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $codePostalPrestation = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $distanceLivraisonKm = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?User $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Menu $menu = null;

    #[ORM\OneToOne(mappedBy: 'commande', targetEntity: Avis::class)]
    private ?Avis $avis = null;

    /**
     * @var Collection<int, CommandeHistoriqueStatut>
     */
    #[ORM\OneToMany(targetEntity: CommandeHistoriqueStatut::class, mappedBy: 'commande', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['dateModification' => 'ASC'])]
    private Collection $historiqueStatuts;

    public function __construct()
    {
        $this->historiqueStatuts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroCommande(): ?string
    {
        return $this->numeroCommande;
    }

    public function setNumeroCommande(string $numeroCommande): static
    {
        $this->numeroCommande = $numeroCommande;

        return $this;
    }

    public function getDateCommande(): ?\DateTimeImmutable
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeImmutable $dateCommande): static
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    public function getDatePrestation(): ?\DateTimeImmutable
    {
        return $this->datePrestation;
    }

    public function setDatePrestation(\DateTimeImmutable $datePrestation): static
    {
        $this->datePrestation = $datePrestation;

        return $this;
    }

    public function getHeureLivraison(): ?string
    {
        return $this->heureLivraison;
    }

    public function setHeureLivraison(string $heureLivraison): static
    {
        $this->heureLivraison = $heureLivraison;

        return $this;
    }

    public function getPrixMenu(): ?string
    {
        return $this->prixMenu;
    }

    public function setPrixMenu(string $prixMenu): static
    {
        $this->prixMenu = $prixMenu;

        return $this;
    }

    public function getNombrePersonne(): ?int
    {
        return $this->nombrePersonne;
    }

    public function setNombrePersonne(int $nombrePersonne): static
    {
        $this->nombrePersonne = $nombrePersonne;

        return $this;
    }

    public function getPrixLivraison(): ?string
    {
        return $this->prixLivraison;
    }

    public function setPrixLivraison(string $prixLivraison): static
    {
        $this->prixLivraison = $prixLivraison;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function isPretMateriel(): bool
    {
        return $this->pretMateriel;
    }

    public function setPretMateriel(bool $pretMateriel): static
    {
        $this->pretMateriel = $pretMateriel;

        return $this;
    }

    public function isRestitutionMateriel(): bool
    {
        return $this->restitutionMateriel;
    }

    public function setRestitutionMateriel(bool $restitutionMateriel): static
    {
        $this->restitutionMateriel = $restitutionMateriel;

        return $this;
    }

    public function getAdressePrestation(): ?string
    {
        return $this->adressePrestation;
    }

    public function setAdressePrestation(string $adressePrestation): static
    {
        $this->adressePrestation = $adressePrestation;

        return $this;
    }

    public function getVillePrestation(): ?string
    {
        return $this->villePrestation;
    }

    public function setVillePrestation(string $villePrestation): static
    {
        $this->villePrestation = $villePrestation;

        return $this;
    }

    public function getCodePostalPrestation(): ?string
    {
        return $this->codePostalPrestation;
    }

    public function setCodePostalPrestation(?string $codePostalPrestation): static
    {
        $this->codePostalPrestation = $codePostalPrestation;

        return $this;
    }

    public function getDistanceLivraisonKm(): ?string
    {
        return $this->distanceLivraisonKm;
    }

    public function setDistanceLivraisonKm(?string $distanceLivraisonKm): static
    {
        $this->distanceLivraisonKm = $distanceLivraisonKm;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }

    public function getAvis(): ?Avis
    {
        return $this->avis;
    }

    public function setAvis(?Avis $avis): static
    {
        if ($avis !== null && $avis->getCommande() !== $this) {
            $avis->setCommande($this);
        }

        $this->avis = $avis;

        return $this;
    }

    /**
     * @return Collection<int, CommandeHistoriqueStatut>
     */
    public function getHistoriqueStatuts(): Collection
    {
        return $this->historiqueStatuts;
    }

    public function addHistoriqueStatut(CommandeHistoriqueStatut $historiqueStatut): static
    {
        if (!$this->historiqueStatuts->contains($historiqueStatut)) {
            $this->historiqueStatuts->add($historiqueStatut);
            $historiqueStatut->setCommande($this);
        }

        return $this;
    }

    public function removeHistoriqueStatut(CommandeHistoriqueStatut $historiqueStatut): static
    {
        if ($this->historiqueStatuts->removeElement($historiqueStatut)) {
            if ($historiqueStatut->getCommande() === $this) {
                $historiqueStatut->setCommande(null);
            }
        }

        return $this;
    }
}
