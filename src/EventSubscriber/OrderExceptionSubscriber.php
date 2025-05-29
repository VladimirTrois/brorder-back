<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use ApiPlatform\Validator\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Order;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class OrderExceptionSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', EventPriorities::POST_RESPOND],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ValidationException) {

            $violations = $exception->getConstraintViolationList();

            // Handle Order already exist violation
            foreach ($violations as $violation) {
                if ($violation->getMessage() === "The group (Name, Pitch and pickUpdate) are already used") {

                    // Retrieve original order
                    $cause = $violation->getCause();
                    $originalOrder = $this->entityManager->getRepository(Order::class)->findOneBy([
                        'id' => $cause[0]->getId(),
                    ]);

                    if ($originalOrder) {
                        if ($originalOrder->getIsDeleted()) {

                            //Handle when original order isDeleted
                            $newOrder = $violation->getRoot();
                            $this->handlePostOnIsDeletedOrder($event, $originalOrder, $newOrder);
                            return;
                        } else {

                            //Handle custom response if original order is valid
                            $orderData = $this->serializer->serialize($cause[0], 'jsonld', ['groups' => ['order:read', 'order:collection:read']]);
                            $response = new JsonResponse(
                                [
                                    'status' => $exception->getStatus(),
                                    'type' => $exception->getType(),
                                    'title' => $exception->getTitle(),
                                    'message' => 'name: The group (Name, Pitch and pickUpdate) are already used',
                                    'cause' => json_decode($orderData)
                                ]
                            );

                            $event->setResponse($response); // Stop the exception
                            return;
                        }
                    }
                }
            }
        }
    }

    //Undelete order and replace items from newOrder
    function handlePostOnIsDeletedOrder(ExceptionEvent $event, Order $order, Order $newOrder): void
    {
        if ($newOrder instanceof Order) {
            //Set old order to active
            $order->setIsDeleted(false);

            //Remove old items
            $oldItems = $order->getItems();
            foreach ($oldItems as $oldItem) {
                $order->removeItem($oldItem);
            }

            //Add new items and manage stock
            $newItems = $newOrder->getItems();
            foreach ($newItems as $newItem) {
                $product = $newItem->getProduct();
                $newQuantity = $newItem->getQuantity();

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

            $order->setItems($newOrder->getItems());
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $orderData = $this->serializer->serialize($order, 'jsonld', ['groups' => ['order:read', 'order:collection:read']]);
            $response = new JsonResponse(json_decode($orderData));
            $response->setStatusCode(201);

            $event->setResponse($response); // Stop the exception
        }
    }
}
