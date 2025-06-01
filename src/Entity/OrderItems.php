<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\OrderItemsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderItemsRepository::class)]
#[ApiResource(
    operations: []
)]
class OrderItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:collection:read', 'order:write'])]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Order $order;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order:collection:read', 'order:write'])]
    #[Assert\NotBlank]
    #[Assert\Type(Product::class)]
    private ?Product $product;

    #[ORM\Column]
    #[Groups(['order:collection:read', 'order:write'])]
    #[Assert\Positive()]
    private ?int $quantity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?order
    {
        return $this->order;
    }

    public function setOrder(?order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getProduct(): ?product
    {
        return $this->product;
    }

    public function setProduct(?product $Product): static
    {
        $this->product = $Product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }
}
