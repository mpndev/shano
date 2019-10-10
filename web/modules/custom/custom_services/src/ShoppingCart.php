<?php

namespace Drupal\custom_services;

use Drupal\custom_services\Event;
class ShoppingCart {

  private $shopping_cart = NULL;
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
   * @param \Drupal\custom_services\Event $event
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
    if ($this->state_variables['have_tickets_for_this_event'] === FALSE) {
      $tickets[] = [
        'event_id' => $this->event->id(),
        'tickets_quantity' => 1,
      ];
    }
    $this->shopping_cart->set('tickets', $tickets);
    $this->state_variables['was_ticket_added'] = TRUE;
  }

  /**
   * @param $ticket
   * @param $event
   *
   * @return bool
   */
  public function ticketIsFromThisEvent($ticket, $event) {
    return $ticket['event_id'] === $event->id();
  }


}
