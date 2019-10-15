<?php

namespace Drupal\shano_shopping_cart;

class ShoppingCart {

  /**
   *  $var object  $shopping_cart
   */
  private $shopping_cart = NULL;

  /**
   * @var array $state_variables
   */
  public $state_variables = [
    'have_tickets_for_this_event' => FALSE,
    'was_ticket_added' => FALSE,
  ];

  /**
   * ShoppingCart constructor.
   */
  public function __construct() {
    $this->shopping_cart = \Drupal::service('tempstore.private')->get('shopping_cart');
  }

  /**
   * @return mixed
   */
  public function getTickets() {
    return $this->shopping_cart->get('tickets');
  }

  /**
   * @param \Drupal\shano_shopping_cart\CustomClasses\Event $event
   */
  public function addTicketForEvent(Event $event) {
    $tickets = $this->getTickets();

    foreach ($tickets as &$ticket) {
      if ($this->ticketIsFromThisEvent($ticket, $event)) {
        $ticket['tickets_quantity']++;
        $this->state_variables['have_tickets_for_this_event'] = TRUE;
        break;
      }
    }
    if (!$this->state_variables['have_tickets_for_this_event']) {
      $tickets[] = [
        'event_id' => $event->id(),
        'tickets_quantity' => 1,
      ];
    }
    $this->shopping_cart->set('tickets', $tickets);
    $this->state_variables['was_ticket_added'] = TRUE;
  }

  /**
   * @param $ticket
   * @param \Drupal\shano_shopping_cart\CustomClasses\Event $event
   *
   * @return bool
   */
  public function ticketIsFromThisEvent($ticket, Event $event) {
    return $ticket['event_id'] === $event->id();
  }

  /**
   * @return $this
   */
  public function removeAllTickets() {
    $this->shopping_cart->set('tickets', []);
    return $this;
  }

  /**
   * @return bool
   */
  public function isEmpty() {
    return !(bool) count($this->getTickets());
  }

  /**
   * @return $this
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateTicketsInEvents() {
    foreach ($this->getTickets() as $ticket) {
      $event = new Event($ticket['event_id']);
      if ($ticket['tickets_quantity'] > $event->tickets->get('field_quantity')->value) {
        throw new \Exception("Not Enough Tickets!");
      }
    }

    foreach ($this->getTickets() as $ticket) {
      $event = new Event($ticket['event_id']);

      $shopping_cart_tickets_quantity = $ticket['tickets_quantity'];
      $event_tickets_quantity = $event->tickets->get('field_quantity')->value;
      $remaining_tickets = $event_tickets_quantity - $shopping_cart_tickets_quantity;

      $event->tickets->set('field_quantity', $remaining_tickets);
      $event->tickets->save();
    }

    return $this;
  }

  public function getTotal() {

    $total = 0;
    foreach ($this->getTickets() as $ticket) {
      $price = intval((new Event($ticket['event_id']))->tickets->get('field_price')->value * 100);
      $quantity = $ticket['tickets_quantity'];
      $total += ($price * $quantity);
    }

    return $total / 100;
  }

}
