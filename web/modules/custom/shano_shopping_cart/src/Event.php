<?php

namespace Drupal\shano_shopping_cart;

use Drupal\node\Entity\Node;

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

  /**
   * Event constructor.
   *
   * @param $id
   */
  public function __construct($id) {
    $this->event = Node::load($id);
    $this->tickets = new Tickets($this);
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
    return $this->tickets->getQuantity();
  }

  public function tickets() {
    return $this->tickets->get();
  }

}
