<?php

namespace Drupal\shano_shopping_cart;

use Drupal\paragraphs\Entity\Paragraph;

class Tickets {

  /**
   * @var \Drupal\Core\Entity\EntityInterface|null
   */
  public $tickets;

  /**
   * Tickets constructor.
   *
   * @param \Drupal\shano_shopping_cart\Event $event
   */
  public function __construct(Event $event) {
    $this->tickets = Paragraph::load($event->event->get('field_tickets')->getValue()[0]['target_id']);
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function get() {
    return $this->tickets;
  }

  /**
   * @return bool
   */
  public function getQuantity() {
    return $this->tickets->get('field_quantity')->getString() > 0;
  }

}
