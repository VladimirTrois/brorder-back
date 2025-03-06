<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Order;
use App\Entity\OrderItems;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class OrderStockProcessor implements ProcessorInterface
{
    public function __construct(private ProcessorInterface $innerProcessor, private EntityManagerInterface $entityManager) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Order && ($operation instanceof Post || $operation instanceof Patch)) {
            $requestContent = $context['request']->getContent();
            if ((str_contains($requestContent, 'items') || str_contains($requestContent, 'isDeleted'))) {
                //If Order is being deleted 
                if ($data->getIsDeleted()) {
                    //Quantity is back to stock
                    foreach ($data->getItems() as $orderItem) {
                        $product = $orderItem->getProduct();
                        if ($product->getStock() > -1) {
                            $product->setStock($product->getStock() + $orderItem->getQuantity());
                        }
                        $this->entityManager->persist($product);
                    }
                } else {

                    $previousData = $context['previous_data'];
                    //If previous order exist then revert stock 
                    if ($previousData && !$previousData->getIsDeleted()) {
                        $oldOrderItems = $this->entityManager->getRepository(OrderItems::class)->findBy(['order' => $data]);
                        foreach ($oldOrderItems as $oldOrderItem) {
                            $product = $oldOrderItem->getProduct();
                            if ($product->getStock() > -1) {
                                $product->setStock($product->getStock() + $oldOrderItem->getQuantity());
                            }
                        }
                        $this->entityManager->persist($product);
                    }

                    //Update stock with new items quantity
                    foreach ($data->getItems() as $orderItem) {
                        $product = $orderItem->getProduct();
                        $newQuantity = $orderItem->getQuantity();

                        // Check stock before update

                        if ($product->getStock() > -1) {
                            if ($product->getStock() >= $newQuantity) {
                                $product->setStock($product->getStock() - $newQuantity);
                            } else {
                                throw new HttpException(409, 'Not enough stock available');
                            }
                        }
                        $this->entityManager->persist($product);
                    }
                }

                $this->entityManager->flush();
            }
        }

        $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        return $data;
    }
}
