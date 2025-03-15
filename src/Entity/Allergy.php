<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\AllergyRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AllergyRepository::class)]
#[GetCollection()] //Declared alone so it is public
#[ApiResource(
    operations: [
        new Post(),
        new Patch(),
    ],
    normalizationContext: ['groups' => ['allergy:write', 'product:write'],],
    denormalizationContext: ['groups' => ['allergy:read', 'product:read']],
    security: "is_granted('ROLE_ADMIN')"
)]
class Allergy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['allergy:read', 'allergy:write', 'product:read', 'product:write'])]
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
