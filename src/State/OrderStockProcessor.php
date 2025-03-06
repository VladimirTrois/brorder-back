<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Order;
use App\Entity\OrderItems;
use App\Entity\Product;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Request;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class OrderStockProcessor implements ProcessorInterface
{
    public function __construct(private ProcessorInterface $innerProcessor, private EntityManagerInterface $entityManager) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $requestContent = $context['request']->getContent();
        $previousData = $context['previous_data'];

        if ($data instanceof Order) {
            if ($operation instanceof Post) {

                //Update new items quantity to stock
                foreach ($data->getItems() as $orderItem) {
                    $product = $orderItem->getProduct();
                    $quantity = $orderItem->getQuantity();

                    // Check stock before reducing
                    if ($product->getStock() >= $quantity) {
                        $product->setStock($product->getStock() - $quantity);
                    } else {
                        throw new \Exception('Not enough stock available');
                    }

                    $this->entityManager->persist($product);
                }
            } elseif ($operation instanceof Patch && (str_contains($requestContent, 'items') || str_contains($requestContent, 'isDeleted'))) {
                if ($data->getIsDeleted()) {
                    //Order is deleted so quantity is back to stock
                    foreach ($data->getItems() as $orderItem) {
                        $product = $orderItem->getProduct();
                        $product->setStock($product->getStock() + $orderItem->getQuantity());
                    }
                } else {

                    //Only revert if previous order was not deleted
                    if (!$previousData->getIsDeleted()) {
                        //Revert previous items quantity to stock
                        $oldOrderItems = $this->entityManager->getRepository(OrderItems::class)->findBy(['order' => $data]);
                        foreach ($oldOrderItems as $oldOrderItem) {
                            $product = $oldOrderItem->getProduct();
                            $product->setStock($product->getStock() + $oldOrderItem->getQuantity());
                        }
                    }

                    //Update new items quantity to stock
                    foreach ($data->getItems() as $orderItem) {
                        $product = $orderItem->getProduct();
                        $newQuantity = $orderItem->getQuantity();

                        // Check stock before update
                        if ($product->getStock() >= $newQuantity) {
                            $product->setStock($product->getStock() - $newQuantity);
                        } else {
                            throw new \Exception('Not enough stock available');
                        }
                        $this->entityManager->persist($product);
                    }
                }
            }

            $this->entityManager->flush();
        }

        $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        return $data;
    }
}
