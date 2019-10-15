<?php

namespace Drupal\shano_shopping_cart;

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

class Event {

  /**
   * The event instance.
   *
   * @var object $event
   */
  public $event = NULL;

  /**
   * The event tickets instance
   *
   * @var $tickets
   */
  public $tickets = [];

  public function __construct($id) {
    $this->event = Node::load($id);
    $this->tickets = Paragraph::load($this->event->get('field_tickets')->getValue()[0]['target_id']);
  }

  /**
   * @return mixed
   */
  public function id() {
    return $this->event->id();
  }

  /**
   * @param $field
   *
   * @return mixed
   */
  public function __get($field) {
    return $this->event->get($field)->value;
  }

  /**
   * @return bool
   */
  public function haveAvailableTickets() {
    return $this->tickets->get('field_quantity')->getString() > 0;
  }

}
