<?php

namespace Drupal\shano_shopping_cart\Controller;

use Drupal\shano_shopping_cart\Event;
use Drupal\shano_shopping_cart\Order;
use Drupal\Core\Controller\ControllerBase;
use Drupal\shano_shopping_cart\ShanoStripe;
use Drupal\shano_shopping_cart\ShoppingCart;
use Symfony\Component\HttpFoundation\Request;
use Drupal\shano_shopping_cart\Form\ReduceTicketForm;

class ShanoShoppingCartController extends ControllerBase {
  /**
   * @return array
   * @throws \Stripe\Exception\ApiErrorException
   */
  public function index() {
    $shopping_cart = new ShoppingCart();
    $shano_stripe = new ShanoStripe();
    $order = new Order();

    if ($shopping_cart->isEmpty()) {
      return ['#markup' => t('Your Shopping Cart is Empty :(')];
    }

    $order->prepare($shopping_cart);
    $shano_stripe->prepare($order->get());

    $response = [
      '#theme' => 'shopping_cart_index',
      '#events' => $this->getTwigData(),
      '#total' => $shopping_cart->getTotal(),
      '#attached' => [
        'drupalSettings' => [
          'stripe_session_id' => $shano_stripe->session->id,
          'stripe_public_key' => $shano_stripe->public_key,
        ],
      ],
    ];
    $response['#attached']['library'][] = 'shano_shopping_cart/stripe';
    $response['#attached']['library'][] = 'shano_shopping_cart/shano-stripe';
    return $response;
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array
   */
  public function makePayment(Request $request) {
    $shano_stripe = new ShanoStripe();
    try {
      $shano_stripe->validateSession($request->get('session_id'));
      $shopping_cart = new ShoppingCart();
      $shopping_cart->updateTicketsInEvents()
        ->produceSecureTickets()
        ->removeAllTickets();
    } catch (\Exception $e) {
      return ['#markup' => 'brd!'];
    }

    return ['#markup' => $shopping_cart->getSecureTicketsForHumans()];
  }

  /**
   * @return array
   */
  private function getTwigData() {
    $twig_data = [];
    $shopping_cart = new ShoppingCart();

    foreach ($shopping_cart->getTickets() as $key => $event_data) {
      $event = new Event($event_data['event_id']);
      $twig_data[$key]['id'] = $event->event->id();
      $twig_data[$key]['description'] = strip_tags($event->event->get('field_event_d')->value);
      $twig_data[$key]['image_url'] = $event->event->field_event_image->entity->uri->value;
      $twig_data[$key]['image_alt'] = $event->event->field_event_image->alt;
      $twig_data[$key]['ordered_tickets_quantity'] = $event_data['tickets_quantity'];
      $twig_data[$key]['ordered_tickets_quantity_text'] = ($event_data['tickets_quantity'] > 1)
        ? t(' tickets are currently in the cart.')
        : t(' ticket is currently in the cart.');

      $twig_data[$key]['reduce_ticket_form'] = \Drupal::formBuilder()->getForm(new ReduceTicketForm($event->event->id()));
    }

    return $twig_data;
  }

}
