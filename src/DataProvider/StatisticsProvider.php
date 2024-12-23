<?php

// src/DataProvider/StatisticsProvider.php
namespace App\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Statistics;
use App\Repository\OrderRepository;

class StatisticsProvider implements ProviderInterface
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Statistics
    {
        $date = $context['filters']['date'] ?? null;

        if (!$date) {
            throw new \InvalidArgumentException('A "date" filter is required.');
        }

        $dateObject = new \DateTime($date);

        $statsOrders = $this->orderRepository->getDailyTotalOrders($dateObject);
        $totalOrders = $statsOrders[0]['totalOrders'] ?? 0;

        $totalProductsResults = $this->orderRepository->getDailyTotalProducts($dateObject);
        $totalProducts = array_map(fn($totalProductsQueryResult) => [
            'id' => $totalProductsQueryResult['productId'],
            'name' => $totalProductsQueryResult['productName'],
            'count' => $totalProductsQueryResult['productCount'],
        ], $totalProductsResults);


        $totalProductsNotSoldResults = $this->orderRepository->getDailyTotalProductsNotSold($dateObject);
        $totalProductsNotSold = array_map(fn($totalProductsNotSoldResult) => [
            'id' => $totalProductsNotSoldResult['productId'],
            'name' => $totalProductsNotSoldResult['productName'],
            'count' => $totalProductsNotSoldResult['productCount'],
        ], $totalProductsNotSoldResults);

        return new Statistics($dateObject->format('Y-m-d'), $totalOrders, $totalProducts, $totalProductsNotSold);
    }
}
