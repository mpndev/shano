<?php

namespace Drupal\shano_shopping_cart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\shano_shopping_cart\CustomClasses\Event;
use Drupal\shano_shopping_cart\CustomClasses\ShanoStripe;
use Drupal\shano_shopping_cart\CustomClasses\ShoppingCart;

class ShanoShoppingCartController extends ControllerBase {

  /**
   * @return array
   * @throws \Stripe\Exception\ApiErrorException
   */
  public function index() {
    $shopping_cart = new ShoppingCart();
    $data = [];
    $stripe_line_items = [];

    if ($shopping_cart->isEmpty()) {
      return ['#markup' => t('Your Shopping Cart is Empty :(')];
    }

    foreach ($shopping_cart->getTickets() as $key => $event_data) {
      $event = (new Event($event_data['event_id']));
      $stripe_line_items[] = [
        'name' => $event->event->getTitle(),
        'description' => strip_tags($event->event->get('field_event_d')->value),
        'amount' => $event->tickets->get('field_price')->value * 100,
        'currency' => 'usd',
        'quantity' => $event_data['tickets_quantity'],
      ];
      $data[$key]['description'] = strip_tags($event->event->get('field_event_d')->value);
      $data[$key]['image_url'] = $event->event->field_event_image->entity->uri->value;
      $data[$key]['image_alt'] = $event->event->field_event_image->alt;
      $data[$key]['ordered_tickets_quantity'] = $event_data['tickets_quantity'];
      $data[$key]['ordered_tickets_quantity_text'] = ($event_data['tickets_quantity'] > 1)
        ? t(' tickets are currently in the cart.')
        : t(' ticket is currently in the cart.');
    }

    $shano_stripe = new ShanoStripe();
    $shano_stripe->createSession($stripe_line_items);

    return [
      '#theme' => 'shopping_cart_index',
      '#events' => $data,
      '#session_id' => $shano_stripe->session->id,
      '#stripe_public_key' => $shano_stripe->public_key,
      '#attached' => [
        'drupalSettings' => [
          'stripe_public_key' => $shano_stripe->public_key,
        ],
      ],
    ];
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
        ->removeAllTickets();
    } catch (\Exception $e) {
      return ['#markup' => 'brd!'];
    }

    $ticket_pass = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(20/strlen($x)) )),1, 20);
    return ['#markup' => 'Your pass for this event/s is: "' . $ticket_pass . '".<br/>Copy this password or make a screen-shot.<br/>This is your ticket for the event/s<br/>Thanks!'];
  }

}
