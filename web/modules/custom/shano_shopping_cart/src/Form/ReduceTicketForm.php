<?php

namespace Drupal\shano_shopping_cart\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\SettingsCommand;
use Drupal\shano_shopping_cart\Event;
use Drupal\Core\Form\FormStateInterface;
use Drupal\shano_shopping_cart\ShanoStripe;
use Drupal\shano_shopping_cart\ShoppingCart;
use Drupal\Core\Ajax\OpenModalDialogCommand;

class ReduceTicketForm extends FormBase {

  /**
   * @var integer
   */
  private $form_id = NULL;

  /**
   * @var \Drupal\shano_shopping_cart\Event
   */
  public $event;

  /**
   * AddTicketToCartForm constructor.
   *
   * @param $event_id
   */
  public function __construct($event_id) {
    $this->form_id = 'shano_shopping_cart_reduce_ticket_form' . $event_id;
    $this->event = new Event($event_id);
  }

  /**
   * @return string|null
   */
  public function getFormId() {
    return $this->form_id;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove 1 ticket'),
      '#ajax' => [
        'callback' => '::processTicketReducing',
        'disable-refocus' => FALSE,
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Verifying entry...'),
        ],
      ]
    ];

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function processTicketReducing(array &$form, FormStateInterface $form_state) {
    $shopping_cart = new ShoppingCart();
    $shopping_cart->removeTicketForEvent($this->event);
    $quantity = $shopping_cart->getTicketsQuantityForEvent($this->event);
    $quantity_span = "<span id='tickets-quantity-" . $this->event->id() . "'>" . $quantity . "</span>";

    $dialog_text['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $dialog_text['#markup'] = t('Ticket was removed from your Shopping Cart!');

    $response = new AjaxResponse();
    if ($quantity) {
      $response->addCommand(new ReplaceCommand('#tickets-quantity-' . $this->event->id(), $quantity_span));
    } else {
      $response->addCommand(new ReplaceCommand('#main-block-' . $this->event->id(), ''));
      if ($shopping_cart->isEmpty()) {
        $response->addCommand(new RedirectCommand("http://shano.local/shopping-cart"));
        return $response;
      }
    }

    //$response->addCommand(new SettingsCommand ($data));
    $response->addCommand(new HtmlCommand('#stripe-button', t('Buy the tickets for:') . ' ' . $shopping_cart->getTotal() . '$'));
    $response->addCommand(new OpenModalDialogCommand($this->event->title, $dialog_text, ['width' => '300']));

    return $response;
  }

}
