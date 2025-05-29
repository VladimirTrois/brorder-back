<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\AllergyRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AllergyRepository::class)]
#[GetCollection(
    order: ['name' => 'ASC']
)] //Declared alone so it is public
#[ApiResource(
    operations: [
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['allergy:write', 'product:write'],],
    denormalizationContext: ['groups' => ['allergy:read', 'product:read']],
    order: ['name' => 'ASC'],
    security: "is_granted('ROLE_ADMIN')",
)]
class Allergy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false, unique: true)]
    #[Groups(['allergy:read', 'allergy:write', 'product:read', 'product:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 30, maxMessage: 'Name your product with 30 chars or less')]
    private ?string $name = null;

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
}
