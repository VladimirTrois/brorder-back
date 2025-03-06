<?php

// src/EventSubscriber/OrderExceptionSubscriber.php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use ApiPlatform\Validator\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Order;
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

    /**
     * Returns an array of events to subscribe to.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', EventPriorities::POST_RESPOND],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Check for the ValidationException
        if ($exception instanceof ValidationException) {
            // Get the violations (errors)
            $violations = $exception->getConstraintViolationList();

            // Loop through violations to identify unique constraint violations
            foreach ($violations as $violation) {
                // Check if this violation is related to the unique constraint
                if ($violation->getMessage() === "The group (Name, Pitch and pickUpdate) are already used") {
                    // Retrieve the existing order with the same name, pitch, and pickup date
                    $cause = $violation->getCause();
                    $order = $this->entityManager->getRepository(Order::class)->findOneBy([
                        'id' => $cause[0]->getId(),
                    ]);

                    // Check if the order exists
                    if ($order) {
                        // Serialize the order using the proper normalization context, including items
                        $orderData = $this->serializer->serialize($cause[0], 'jsonld', ['groups' => ['order:read', 'order:collection:read']]);


                        // Return a custom response with the serialized order data
                        $response = new JsonResponse(
                            [
                                'status' => $exception->getStatus(),
                                'type' => $exception->getType(),
                                'title' => $exception->getTitle(),
                                'message' => 'name: The group (Name, Pitch and pickUpdate) are already used',
                                'cause' => json_decode($orderData) // Decode the JSON string back to an array
                            ]
                        );

                        $event->setResponse($response); // Stop the exception from bubbling further
                        return;
                    }
                }
            }
        }
    }
}
