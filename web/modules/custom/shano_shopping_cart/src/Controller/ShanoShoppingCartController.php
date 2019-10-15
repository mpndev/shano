<?php

namespace Drupal\shano_shopping_cart\Controller;

use Stripe\Stripe;
use Drupal\Core\Controller\ControllerBase;
use Stripe\Checkout\Session as StripeSession;
use Symfony\Component\HttpFoundation\Request;
use Drupal\shano_shopping_cart\CustomClasses\Event;
use Drupal\shano_shopping_cart\CustomClasses\ShoppingCart;

class ShanoShoppingCartController extends ControllerBase {

  public function __construct() {
    define('STRIPE_SECRET_KEY', 'sk_test_p0UckJeEg99jiVQWZs1iaa1h00LQlWSLh0');
    define('STRIPE_PUBLIC_KEY', 'pk_test_1p8Xr5GnL2BGdQTC73zF6Gl000TMrSf4yF');
    Stripe::setApiKey(STRIPE_SECRET_KEY);
  }

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

    Stripe::setApiKey(STRIPE_SECRET_KEY);

    $session = StripeSession::create([
      'payment_method_types' => ['card'],
      'line_items' => $stripe_line_items,
      'success_url' => 'http://shano.local/shopping-cart/make-payment?session_id={CHECKOUT_SESSION_ID}',
      'cancel_url' => 'http://shano.local/shopping-cart/paymant-fail',
    ]);

    return [
      '#theme' => 'shopping_cart_index',
      '#events' => $data,
      '#session_id' => $session->id,
      '#stripe_public_key' => STRIPE_PUBLIC_KEY,
      '#attached' => [
        'drupalSettings' => [
          'myObj' => ''
        ]
      ]
    ];
  }

  public function makePayment(Request $request) {
    try {
      StripeSession::retrieve($request->get('session_id'));
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
