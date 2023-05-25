<?php

namespace Drupal\grants_handler\EventSubscriber;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Grants Handler event subscriber.
 */
class GrantsExceptionSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory|\Drupal\Core\Logger\LoggerChannelInterface|\Drupal\Core\Logger\LoggerChannel
   */
  protected LoggerChannelFactory|LoggerChannelInterface|LoggerChannel $logger;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerFactory
   *   Logger.
   */
  public function __construct(
    MessengerInterface $messenger,
    LoggerChannelFactory $loggerFactory,
  ) {
    $this->messenger = $messenger;
    $this->logger = $loggerFactory->get('grants_handler');
  }

  /**
   * Kernel response event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
   *   Response event.
   */
  public function onException(ExceptionEvent $event) {
    $ex = $event->getThrowable();
    $previousException = $ex->getPrevious();

    if ($previousException) {
      $exceptionClass = get_class($previousException);
      if (str_contains($exceptionClass, 'grants_handler\GrantsException')) {
        $this->messenger->addError($this->t('Your request was not fulfilled due to unrecognized error.'));
        $this->logger->error($previousException->getMessage());

        // Redirect back to same page because could cause infinite loop.
        $url = Url::fromRoute('<front>');
        $response = new RedirectResponse($url->toString());
        $event->setResponse($response);
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::EXCEPTION => ['onException'],
    ];
  }

}
