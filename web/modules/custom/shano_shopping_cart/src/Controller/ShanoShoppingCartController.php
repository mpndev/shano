<?php

namespace Drupal\shano_shopping_cart\Controller;

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ShanoShoppingCartController extends ControllerBase {

  /**
   * @return array
   */
  public function index() {
    $tickets_data = \Drupal::service('tempstore.private')->get('shopping_cart')->get('tickets');
    $data = [];

    foreach ($tickets_data as $key => $ticket_data) {
      $data[$key]['event'] = Node::load($ticket_data['event_id']);
      $data[$key]['ordered_tickets_quantity'] = $ticket_data['tickets_quantity'];
      $data[$key]['ordered_tickets_quantity_text'] = ($ticket_data['tickets_quantity'] > 1)
        ? t(' tickets are currently in the cart.')
        : t(' ticket is currently in the cart.');
    }

    return [
      '#theme' => 'shopping_cart_index',
      '#events' => $data
    ];
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function store(Request $request) {
    //todo:
  }

}
