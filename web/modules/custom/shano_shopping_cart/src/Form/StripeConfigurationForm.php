<?php

namespace Drupal\shano_shopping_cart\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class StripeConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shano_shopping_cart_stripe_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'shano_shopping_cart.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('shano_shopping_cart.settings');
    $form['stripe_public_key_test'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stripe Public Key (Testing Mode)'),
      '#default_value' => $config->get('stripe_public_key_test'),
      '#states' => [
        'visible' => [
          ':input[name="live_or_test"]' => [
            'value' => '1'
          ],
        ],
      ],
    ];

    $form['stripe_secret_key_test'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stripe Secret Key (Testing Mode)'),
      '#default_value' => $config->get('stripe_secret_key_test'),
      '#states' => [
        'visible' => [
          ':input[name="live_or_test"]' => [
            'value' => '1'
          ],
        ],
      ],
    ];

    $form['stripe_public_key_live'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stripe Public Key (Live Mode)'),
      '#default_value' => $config->get('stripe_public_key_live'),
      '#states' => [
        'visible' => [
          ':input[name="live_or_test"]' => [
            'value' => '0'
          ],
        ],
      ],
    ];

    $form['stripe_secret_key_live'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stripe Secret Key (Live Mode)'),
      '#default_value' => $config->get('stripe_secret_key_live'),
      '#states' => [
        'visible' => [
          ':input[name="live_or_test"]' => [
            'value' => '0'
          ],
        ],
      ],
    ];

    $form['live_or_test'] = [
      '#type' => 'radios',
      '#title' => $this->t('Live or Test Mode'),
      '#default_value' => 1,
      '#options' => [
        t('Live'),
        t('Test'),
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $keys = $form_state->getValues();
    $live_or_test = $keys['live_or_test'];
    $stripe_public_key_test = $keys['stripe_public_key_test'];
    $stripe_secret_key_test = $keys['stripe_secret_key_test'];
    $stripe_public_key_live = $keys['stripe_public_key_live'];
    $stripe_secret_key_live = $keys['stripe_secret_key_live'];

    $this->config('shano_shopping_cart.settings')
      ->set('live_or_test', $live_or_test)
      ->set('stripe_public_key_test', $stripe_public_key_test)
      ->set('stripe_secret_key_test', $stripe_secret_key_test)
      ->set('stripe_public_key_live', $stripe_public_key_live)
      ->set('stripe_secret_key_live', $stripe_secret_key_live)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
