<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\DataProvider\ProductsOrderedAllergiesProvider;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[GetCollection(
    normalizationContext: ['groups' => ['product:read'],],
)] //Declared alone so it is public
#[GetCollection(
    provider: ProductsOrderedAllergiesProvider::class,
    uriTemplate: '/products/allergies',
    normalizationContext: ['groups' => ['product:read', 'product:read:allergies'],],
)] //Declared alone so it is public
#[ApiResource(
    paginationItemsPerPage: 10,
    operations: [
        new Get(),
        new Post(),
        new Patch(),
    ],
    normalizationContext: ['groups' => ['product:read'],],
    denormalizationContext: ['groups' => ['product:write']],
    security: "is_granted('ROLE_ADMIN')",
)]
#[ApiFilter(OrderFilter::class, properties: ['rank', 'isAvailable'], arguments: ['orderParameterName' => 'orderBy'])]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read', 'order:read', 'order:collection:read'])]
    private ?int $id;

    #[ORM\Column(length: 255, nullable: false, unique: true)]
    #[Groups(['product:read', 'product:write', 'order:read', 'order:collection:read'])]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 30, maxMessage: 'Name your product with 30 chars or less')]
    private ?string $name;

    #[ORM\Column(nullable: false)]
    #[Groups(['product:read', 'product:write', 'order:read', 'order:collection:read'])]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $price = 0;

    #[ORM\Column(nullable: false)]
    #[Groups(['product:read', 'product:write', 'order:read'])]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $weight;

    #[ORM\Column(nullable: false)]
    #[ApiFilter(BooleanFilter::class)]
    #[Groups(['product:read', 'product:write'])]
    private ?bool $isAvailable = true;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['product:read', 'product:write'])]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    #[ApiFilter(SearchFilter::class)]
    #[Groups(['product:read', 'product:write', 'order:read', 'order:collection:read'])]
    private ?int $stock = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['product:read', 'product:write'])]
    private ?int $rank = null;

    /**
     * @var Collection<int, ProductAllergy>
     */
    #[ORM\OneToMany(targetEntity: ProductAllergy::class, mappedBy: 'product', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['product:read:allergies', 'product:write'])]
    private Collection $allergies;

    public function __construct()
    {
        $this->allergies = new ArrayCollection();
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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getIsAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * @return Collection<int, ProductAllergy>
     */
    public function getAllergies(): Collection
    {
        return $this->allergies;
    }

    public function addAllergy(ProductAllergy $allergy): static
    {
        if (!$this->allergies->contains($allergy)) {
            $this->allergies->add($allergy);
            $allergy->setProduct($this);
        }

        return $this;
    }

    public function removeAllergy(ProductAllergy $allergy): static
    {
        if ($this->allergies->removeElement($allergy)) {
            // set the owning side to null (unless already changed)
            if ($allergy->getProduct() === $this) {
                $allergy->setProduct(null);
            }
        }

        return $this;
    }
}
