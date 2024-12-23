<?php

// src/Dto/Statistics.php
namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use App\DataProvider\StatisticsProvider;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/orders/stats',
            provider: StatisticsProvider::class,
        ),
    ],
    output: Statistics::class,
    filters: ['date_filter']
)]
class Statistics
{
    #[ApiProperty(identifier: true)]
    private string $date;

    #[ApiProperty(description: "Total de commande")]
    private int $totalOrders;

    #[ApiProperty(description: "Total de produits")]
    private array $totalProducts;

    #[ApiProperty(description: "Total de produits non vendu")]
    private array $totalProductsNotSold;

    public function __construct(string $date, int $totalOrders, array $totalProducts, array $totalProductsNotSold)
    {
        $this->date = $date;
        $this->totalOrders = $totalOrders;
        $this->totalProducts = $totalProducts;
        $this->totalProductsNotSold = $totalProductsNotSold;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getTotalOrders(): int
    {
        return $this->totalOrders;
    }

    public function getTotalProducts(): array
    {
        return $this->totalProducts;
    }

    public function getTotalProductsNotSold(): array
    {
        return $this->totalProductsNotSold;
    }
}
