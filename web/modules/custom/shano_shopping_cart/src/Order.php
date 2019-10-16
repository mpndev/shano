<?php

namespace Drupal\shano_shopping_cart;

class Order {

  /**
   * @var array
   */
  public $order = [];

  /**
   * @param \Drupal\shano_shopping_cart\ShoppingCart $shopping_cart
   *
   * @return $this
   */
  public function prepare(ShoppingCart $shopping_cart) {
    foreach ($shopping_cart->getTickets() as $event_data) {
      $event = new Event($event_data['event_id']);
      $this->order[] = [
        'name' => $event->title,
        'description' => strip_tags($event->event->get('field_event_d')->value),
        'amount' => $event->tickets()->get('field_price')->value * 100,
        'currency' => 'usd',
        'quantity' => $event_data['tickets_quantity'],
      ];
    }

    return $this;
  }

  /**
   * @return array
   * @throws \Exception
   */
  public function get() {
    if ($this->isEmpty()) {
      throw new \Exception("Order is empty! Maybe you forget to call prepare method on Order class.");
    }
    return $this->order;
  }

  /**
   * @return bool
   */
  public function isEmpty() {
    return count($this->order) == 0;
  }

}
