<?php

namespace Drupal\shano_shopping_cart;

use Drupal\node\Entity\Node;
use Drupal\Component\Utility\Random;

class PurchasedTickets {

  private static $tickets = [];

  /**
   * @param $tickets
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function save($tickets) {
    $random = new Random();
    foreach ($tickets as $ticket) {
      for ($i = 0; $i < $ticket['tickets_quantity'];$i++) {
        $event = new Event($ticket['event_id']);
        $secure_string = $random->string(20);

        $node = Node::create([
          'type' => 'purchased_ticket',
          'title' => $event->title,
          'event_id' => $event->id(),
          'secure_string' => $secure_string,
        ]);
        $node->save();

        self::$tickets[] = [
          'secure_string' => $secure_string,
          'event' => $event,
        ];
      }
    }
  }

  /**
   * @return string
   */
  public static function forHumans() {
    $response = t('Please save or make a screen shot for the following tickets') . ':</br></br>';
    foreach (self::$tickets as $ticket) {
      $response .= t('Ticket uniq code') . ': "' . $ticket['secure_string'] . '"</br> - ' . t('for') . ' "' . $ticket['event']->title . '"</br></br>';
    }
    $response .= '</br>' . t('Thanks for choosing us!');

    return $response;
  }

}
