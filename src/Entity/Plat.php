<?php

namespace App\Entity;

use App\Repository\PlatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlatRepository::class)]
#[ORM\Table(name: 'plat')]
class Plat
{
    public const TYPE_ENTREE = 'entree';
    public const TYPE_PLAT = 'plat';
    public const TYPE_DESSERT = 'dessert';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $titrePlat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 20, options: ['default' => self::TYPE_PLAT])]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::TYPE_ENTREE, self::TYPE_PLAT, self::TYPE_DESSERT])]
    private ?string $typePlat = self::TYPE_PLAT;

    /**
     * @var Collection<int, Menu>
     */
    #[ORM\ManyToMany(targetEntity: Menu::class, mappedBy: 'plats')]
    private Collection $menus;

    /**
     * @var Collection<int, Allergene>
     */
    #[ORM\ManyToMany(targetEntity: Allergene::class, inversedBy: 'plats')]
    #[ORM\JoinTable(name: 'plat_allergene')]
    private Collection $allergenes;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->allergenes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitrePlat(): ?string
    {
        return $this->titrePlat;
    }

    public function setTitrePlat(string $titrePlat): static
    {
        $this->titrePlat = $titrePlat;

        return $this;
    }

    public function __toString(): string
    {
        return (string) ($this->titrePlat ?? '');
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getTypePlat(): ?string
    {
        return $this->typePlat;
    }

    public function setTypePlat(string $typePlat): static
    {
        $this->typePlat = $typePlat;

        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): static
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): static
    {
        $this->menus->removeElement($menu);

        return $this;
    }

    /**
     * @return Collection<int, Allergene>
     */
    public function getAllergenes(): Collection
    {
        return $this->allergenes;
    }

    public function addAllergene(Allergene $allergene): static
    {
        if (!$this->allergenes->contains($allergene)) {
            $this->allergenes->add($allergene);
            $allergene->addPlat($this);
        }

        return $this;
    }

    public function removeAllergene(Allergene $allergene): static
    {
        if ($this->allergenes->removeElement($allergene)) {
            $allergene->removePlat($this);
        }

        return $this;
    }
}
