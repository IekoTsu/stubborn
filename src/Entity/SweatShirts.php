<?php

namespace App\Entity;

use App\Repository\SweatShirtsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SweatShirtsRepository::class)]
class SweatShirts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column]
    private ?int $xs_stock = null;

    #[ORM\Column]
    private ?int $s_stock = null;

    #[ORM\Column]
    private ?int $m_stock = null;

    #[ORM\Column]
    private ?int $l_stock = null;

    #[ORM\Column]
    private ?int $xl_stock = null;

    #[ORM\Column(length: 255)]
    private ?string $img = null;

    #[ORM\Column(type: 'boolean')]
    private bool $featured = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price !== null ? (float) $this->price : null;
    }

    public function setPrice(float $price): static
    {
        $this->price = number_format($price, 2, '.', '');
    
        return $this;
    }

    public function getXsStock(): ?int
    {
        return $this->xs_stock;
    }

    public function setXsStock(int $xs_stock): static
    {
        $this->xs_stock = $xs_stock;

        return $this;
    }

    public function getSStock(): ?int
    {
        return $this->s_stock;
    }

    public function setSStock(int $s_stock): static
    {
        $this->s_stock = $s_stock;

        return $this;
    }

    public function getMStock(): ?int
    {
        return $this->m_stock;
    }

    public function setMStock(int $m_stock): static
    {
        $this->m_stock = $m_stock;

        return $this;
    }

    public function getLStock(): ?int
    {
        return $this->l_stock;
    }

    public function setLStock(int $l_stock): static
    {
        $this->l_stock = $l_stock;

        return $this;
    }

    public function getXlStock(): ?int
    {
        return $this->xl_stock;
    }

    public function setXlStock(int $xl_stock): static
    {
        $this->xl_stock = $xl_stock;

        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(string $img): static
    {
        $this->img = $img;

        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;
        return $this;
    }

}
