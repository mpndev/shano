<?php


namespace Drupal\shano_shopping_cart\CustomClasses;

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class ShanoStripe {

  public $public_key;
  private $secret_key;
  public $session;

  /**
   * ShanoStripe constructor.
   */
  public function __construct() {
    $stripe_config = \Drupal::config('shano_shopping_cart.settings');
    $is_test_mode = $stripe_config->get('live_or_test');

    $this->secret_key = $is_test_mode ?
      $stripe_config->get('stripe_secret_key_test'):
      $stripe_config->get('stripe_secret_key_live');

    $this->public_key = $is_test_mode ?
      $stripe_config->get('stripe_public_key_test'):
      $stripe_config->get('stripe_public_key_live');

    Stripe::setApiKey($this->secret_key);
  }

  /**
   * @param array $stripe_line_items
   *
   * @return $this
   * @throws \Stripe\Exception\ApiErrorException
   */
  public function createSession(array $stripe_line_items) {
    $this->session = StripeSession::create([
      'payment_method_types' => ['card'],
      'line_items' => $stripe_line_items,
      'success_url' => 'http://shano.local/shopping-cart/make-payment?session_id={CHECKOUT_SESSION_ID}',
      'cancel_url' => 'http://shano.local/shopping-cart/paymant-fail',
    ]);

    return $this;
  }

  /**
   * @param $session_id
   *
   * @return $this
   * @throws \Exception
   */
  public function validateSession($session_id) {
    try {
      StripeSession::retrieve($session_id);
    } catch (\Exception $e) {
      throw new \Exception("Invalid Session!");
    }
    return $this;
  }

}
