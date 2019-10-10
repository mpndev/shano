<?php

namespace Drupal\shano_shopping_cart\Form;

use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\OpenModalDialogCommand;

class AddTicketToCartForm extends FormBase {

  private $form_id = NULL;
  private $event = NULL;
  private $tickets = NULL;
  private $shopping_cart = NULL;
  private $have_tickets_for_this_event = FALSE;
  private $wasTicketAdded = FALSE;

  /**
   * AddTicketToCartForm constructor.
   *
   * @param $event_id
   */
  public function __construct($event_id) {
    $this->form_id = 'shano_shopping_cart_form' . $event_id;
    $this->event = Node::load($event_id);
    $this->tickets = Paragraph::load($this->event->get('field_tickets')->getValue()[0]['target_id']);
    $this->shopping_cart = \Drupal::service('tempstore.private')->get('shopping_cart');
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
    if ($this->haveAvailableTickets()) {
      $this->addTicket();
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
    $dialog_text['#markup'] = $this->wasTicketAdded ? t('Ticket was added to your shopping cart.') : t('No more tickets left.');

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#message_box' . $this->event->id(), \Drupal::service('renderer')->render($elem)));
    $response->addCommand(new OpenModalDialogCommand($this->event->get('title')->value, $dialog_text, ['width' => '300']));

    return $response;
  }

  /**
   * Add Ticket to the Shopping Cart
   */
  private function addTicket () {
    $tickets = $this->shopping_cart->get('tickets');
    foreach ($tickets as &$ticket) {
      if ($this->ticketIsFromThisEvent($ticket)) {
        $ticket['tickets_quantity']++;
        $this->have_tickets_for_this_event = TRUE;
        break;
      }
    }
    if ($this->have_tickets_for_this_event === FALSE) {
      $tickets[] = [
        'event_id' => $this->event->id(),
        'tickets_quantity' => 1,
      ];
    }
    $this->shopping_cart->set('tickets', $tickets);
    $this->wasTicketAdded = TRUE;
  }

  /**
   * @return bool
   */
  private function haveAvailableTickets() {
    return $this->tickets->get('field_quantity')->getString() > 0;
  }

  /**
   * @param $ticket
   *
   * @return bool
   */
  private function ticketIsFromThisEvent($ticket) {
    return $ticket['event_id'] === $this->event->id();
  }

}
