<?php

declare(strict_types=1);

namespace App\Product\Domain\Model;

use App\Product\Infrastructure\Persistence\Doctrine\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Price in the smallest currency unit (e.g. kopecks / cents).
     */
    #[ORM\Column]
    private int $price;

    #[ORM\Column]
    private int $weight;

    #[ORM\Column(length: 255)]
    private string $category;

    public function __construct(
        string $name,
        int $price,
        int $weight,
        string $category,
        ?string $description = null,
    ) {
        $this->name = $name;
        $this->price = $price;
        $this->weight = $weight;
        $this->category = $category;
        $this->description = $description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function changeDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function changePrice(int $price): void
    {
        $this->price = $price;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function changeWeight(int $weight): void
    {
        $this->weight = $weight;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function changeCategory(string $category): void
    {
        $this->category = $category;
    }
}
