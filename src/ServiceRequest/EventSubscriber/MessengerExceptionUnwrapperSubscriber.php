<?php

declare(strict_types=1);

namespace App\ServiceRequest\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * Subscriber unwraps exceptions from Messenger, allowing API Platform to return correct HTTP status codes.
 */
final class MessengerExceptionUnwrapperSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 200],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HandlerFailedException && $exception->getPrevious()) {
            $event->setThrowable($exception->getPrevious());
        }
    }
}
