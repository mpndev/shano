<?php

namespace Drupal\shano_shopping_cart\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\shano_shopping_cart\CustomClasses\Event;
use Drupal\shano_shopping_cart\CustomClasses\ShoppingCart;

class AddTicketToCartForm extends FormBase {

  /**
   * @var integer
   */
  private $form_id = NULL;

  /**
   * @var object Event todo
   */
  private $event = NULL;
  private $shopping_cart = NULL;

  /**
   * AddTicketToCartForm constructor.
   *
   * @param $event_id
   */
  public function __construct($event_id) {
    $this->form_id = 'shano_shopping_cart_form' . $event_id;
    $this->event = new Event($event_id);
    $this->shopping_cart = new ShoppingCart();
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
      '#value' => $this->t('Shut up and take my money!'),
      '#wrapper' => 'message_box' . $this->event->id(),
      '#ajax' => [
        'callback' => '::processTicketAdding',
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
    if ($this->event->haveAvailableTickets()) {
      $this->shopping_cart->addTicketForEvent($this->event);
    }
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function processTicketAdding(array &$form, FormStateInterface $form_state) {
    $elem = [
      '#type' => 'div',
      '#attributes' => [
        'id' => 'message_box' . $this->event->id(),
      ],
    ];

    $dialog_text['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $dialog_text['#markup'] = $this->shopping_cart->state_variables['was_ticket_added'] ? t('Ticket was added to your shopping cart.') : t('No more tickets left.');

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#message_box' . $this->event->id(), \Drupal::service('renderer')->render($elem)));
    $response->addCommand(new OpenModalDialogCommand($this->event->title, $dialog_text, ['width' => '300']));

    return $response;
  }

}
