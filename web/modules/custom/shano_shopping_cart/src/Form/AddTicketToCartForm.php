<?php

namespace Drupal\shano_shopping_cart\Form;

use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\OpenModalDialogCommand;

class AddTicketToCartForm extends FormBase {

  protected $form_id = NULL;
  protected $node_id = NULL;

  public function __construct($node_id) {
    $this->form_id = 'shano_shopping_cart_form' . $node_id;
    $this->node_id = $node_id;
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return $this->form_id;
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['node_id'] = [
      '#type' => 'hidden',
      '#value' => $this->node_id,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Shut up and take my money!'),
      '#wrapper' => 'message_box' . $this->node_id,
      '#ajax' => [
        'callback' => '::processTicketAdding',
        'disable-refocus' => FALSE,
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Verifying entry...'),
        ],
      ]
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $event = Node::load($this->node_id);
    $tickets_id = $event->get('field_tickets')->getValue()[0]['target_id'];
    $tickets = Paragraph::load($tickets_id);
    $quantity = $tickets->get('field_quantity')->getString();

    if ($quantity > 0) {
      $order_already_exist = TRUE;
      $shopping_cart = \Drupal::service('tempstore.private')->get('shopping_cart');
      $shopping_cart_tickets = $shopping_cart->get('tickets');

      foreach ($shopping_cart_tickets as &$shopping_cart_ticket) {
        if ($shopping_cart_ticket['event_id'] === $this->node_id) {
          $shopping_cart_ticket['tickets_quantity']++;
          $order_already_exist = FALSE;
        }
      }
      if ($order_already_exist === TRUE) {
        $shopping_cart_tickets[] = [
          'event_id' => $this->node_id,
          'tickets_quantity' => 1,
        ];
      }
      $shopping_cart->set('tickets', $shopping_cart_tickets);
      $form['was_ticket_reserved'] = TRUE;
    } else {
      $form['was_ticket_reserved'] = FALSE;
    }
  }

  public function processTicketAdding(array &$form, FormStateInterface $form_state) {
    $markup = $form['was_ticket_reserved'] ? 'Ticket was added to your shopping cart.' : 'No more tickets left.';
    $elem = [
      '#type' => 'textfield',
      '#size' => '60',
      '#disabled' => TRUE,
      '#value' => "I am a new textfield: $selectedText!",
      '#attributes' => [
        'id' => ['edit-output'],
      ],
    ];
    $renderer = \Drupal::service('renderer');
    $renderedField = $renderer->render($elem);

    $dialogText['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $dialogText['#markup'] = $markup;

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#message_box' . $this->node_id, $renderedField));
    $response->addCommand(new OpenModalDialogCommand('Info About Your Request...', $dialogText, ['width' => '300']));

    // Finally return the AjaxResponse object.
    return $response;
  }

}
