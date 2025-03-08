<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\OrderRepository;
use App\State\OrderPostProcessor;
use App\Validator\UniqueActiveGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ORM\UniqueConstraint(
    name: 'UNIQ_IDENTIFIER_NAME_PITCH_PICKUPDATE',
    fields: ['name', 'pitch', 'pickUpDate']
)]
#[UniqueEntity(
    fields: ['name', 'pitch', 'pickUpDate'],
    message: "The group (Name, Pitch and pickUpdate) are already used"
)]
//Declared alone so it is public
#[
    Post(
        normalizationContext: ['groups' => ['order:collection:read', 'order:read']],
        denormalizationContext: ['groups' => ['order:write']],
    ),
    Patch(
        normalizationContext: ['groups' => ['order:collection:read', 'order:read']],
        denormalizationContext: ['groups' => ['order:write']]
    )
]
#[ApiResource(
    paginationItemsPerPage: 10,
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['order:collection:read']]),
        new Get(normalizationContext: ['groups' => ['order:collection:read', 'order:read']]),
    ],
    denormalizationContext: ['groups' => ['order:write']],
    security: "is_granted('ROLE_ADMIN')"
)]
#[ApiResource(order: ['isTaken' => 'asc'])]
#[ApiFilter(OrderFilter::class, properties: ['isTaken', 'name', 'pitch'], arguments: ['orderParameterName' => 'orderBy'])]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:collection:read'])]
    private ?int $id;

    #[ORM\Column(length: 255)]
    #[Groups(['order:collection:read', 'order:write'])]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max: 30, maxMessage: 'Please use a name with 30 chars or less')]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    private ?string $name;

    #[ORM\Column(length: 255)]
    #[Groups(['order:collection:read', 'order:write'])]
    #[Assert\NotBlank()]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    private ?string $pitch;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['order:collection:read', 'order:write'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[ApiFilter(DateFilter::class, strategy: DateFilter::EXCLUDE_NULL)]
    private \DateTimeInterface $pickUpDate;

    #[ORM\Column(nullable: true)]
    #[Groups(['order:collection:read', 'order:write'])]
    private ?int $total = null;

    #[ORM\Column]
    #[Groups(['order:collection:read', 'order:write'])]
    #[ApiFilter(BooleanFilter::class)]
    private ?bool $isTaken = false;

    #[ORM\Column]
    #[Groups(['order:collection:read', 'order:write'])]
    #[ApiFilter(BooleanFilter::class)]
    private ?bool $isDeleted = false;

    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?\DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, OrderItems>
     */
    #[ORM\OneToMany(targetEntity: OrderItems::class, mappedBy: 'order', orphanRemoval: true, cascade: ['persist'])]
    #[Assert\Count(min: 1, minMessage: "Items cannot be empty")]
    #[Assert\Valid()]
    #[Groups(['order:collection:read', 'order:write'])]
    private Collection $items;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('now');
        $this->pickUpDate = new \DateTime('tomorrow');
        $this->items = new ArrayCollection();
    }

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

    public function getPitch(): ?string
    {
        return $this->pitch;
    }

    public function setPitch(string $pitch): static
    {
        $this->pitch = strtoupper($pitch);

        return $this;
    }

    public function getPickUpDate(): \DateTimeInterface
    {
        return $this->pickUpDate;
    }

    public function setPickUpDate(\DateTimeInterface $pickUpDate): static
    {
        $this->pickUpDate = $pickUpDate;

        return $this;
    }

    public function getIsTaken(): ?bool
    {
        return $this->isTaken;
    }

    public function setIsTaken(bool $isTaken): static
    {
        $this->isTaken = $isTaken;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, OrderItems>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function setItems(Collection $items): static
    {
        foreach ($items as $item) {
            $item->setOrder($this);
        }
        $this->items = $items;
        return $this;
    }

    public function addItem(OrderItems $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeItem(OrderItems $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
        }

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): static
    {
        $this->total = $total;

        return $this;
    }
}
