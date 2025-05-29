<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use App\Repository\ProductAllergyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductAllergyRepository::class)]
#[ApiResource(
    operations: [
        new Patch(),
    ],
    normalizationContext: ['groups' => ['product_allergy:read']],
    denormalizationContext: ['groups' => ['product_allergy:write']],
    security: "is_granted('ROLE_ADMIN')",
)]
class ProductAllergy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product_allergy:read', 'product_allergy:write', 'product:read', 'product:write'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'allergies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Groups(['product_allergy:read', 'product_allergy:write', 'product:read', 'product:write'])]
    #[Assert\Type(Allergy::class)]
    private ?Allergy $allergy = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product_allergy:read', 'product_allergy:write', 'product:read', 'product:write'])]
    private ?string $level = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getAllergy(): ?Allergy
    {
        return $this->allergy;
    }

    public function setAllergy(?Allergy $allergy): static
    {
        $this->allergy = $allergy;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

        return $this;
    }
}
