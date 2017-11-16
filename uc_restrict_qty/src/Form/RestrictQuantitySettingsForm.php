<?php
exit;
namespace Drupal\uc_restrict_qty\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\RoleInterface;


/**
 * Grants roles upon accepted payment of products.
 *
 * The uc_role module will grant specified roles upon purchase of specified
 * products. Granted roles can be set to have a expiration date. Users can also
 * be notified of the roles they are granted and when the roles will
 * expire/need to be renewed/etc.
 */
class RestrictQuantitySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uc_restrict_qty_feature_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
  $form['#description'] = t('This feature is limited in scope to preventing a user from adding different products to the cart.  This does not restrict the quantity of products in the cart if updated after being added, so this feature is best used on sites where all products have a restrict quantity feature on them.');

  $form['uc_restrict_qty_global'] = array(
    '#title' => t('Global limit'),
    '#type' => 'textfield',
    '#size' => 5,
    '#maxlength' => 5,
    '#description' => t('The number of different products that can be added to a cart. Set to 0 for unlimited.'),
    '#default_value' => variable_get('uc_restrict_qty_global', 0),
  );
  $form['uc_restrict_qty_global_replace'] = array(
    '#title' => t('Replace Contents'),
    '#type' => 'checkbox',
    '#description' => t('Enabling this feature will cause the users cart to be emptied if they add more items than the Global Limit set above. This is best used when you offer mutually exclusive content (such as subscription services) where purchasing more then one product is not a valid option.'),
    '#default_value' => variable_get('uc_restrict_qty_global_replace', FALSE),
  );
  $form['defaults'] = array(
    '#title' => t('Defaults'),
    '#type' => 'fieldset',
    '#description' => t('These options will take action, only if product has the "Restrict quantity" feature activated.'),
  );
  $form['defaults']['uc_restrict_qty_default_qty'] = array(
    '#title' => t("Default maximum limit for a product"),
    '#type' => 'textfield',
    '#size' => 5,
    '#maxlength' => 5,
    '#description' => t('The number of products of single type that can be added to a cart. Set to 0 for unlimited.'),
    '#default_value' => variable_get('uc_restrict_qty_default_qty', 0),
  );
  $form['defaults']['uc_restrict_qty_default_lifetime'] = array(
    '#title' => t("Is restriction is the user's lifetime limit"),
    '#type' => 'checkbox',
    '#description' => t("Useful when you want to prevent double ordering of a product."),
    '#default_value' => variable_get('uc_restrict_qty_default_lifetime', FALSE),
  );


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // If the user selected a granularity, let's make sure they
    // also selected a duration.
  if (!is_numeric($form_state['values']['uc_restrict_qty_global']) || $form_state['values']['uc_restrict_qty_global'] < 0) {
    form_set_error('uc_restrict_qty_global', t('You must enter 0 or a positive number value.'));
  }
  if (!is_numeric($form_state['values']['uc_restrict_qty_default_qty']) || $form_state['values']['uc_restrict_qty_default_qty'] < 0) {
    form_set_error('uc_restrict_qty_default_qty', t('You must enter 0 or a positive number value.'));
  }

    return parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
   public function submitForm(array &$form, FormStateInterface $form_state) {
     $roles_config = $this->config('uc_role.settings');
     $roles_config
       ->set('default_role', $form_state->getValue('default_role'))
       ->set('default_role_choices', $form_state->getValue('default_role_choices'))
       ->set('default_end_expiration', $form_state->getValue('default_end_expiration'))
       ->set('default_length', $form_state->getValue('default_length'))
       ->set('default_granularity', $form_state->getValue('default_granularity'))
       ->set('default_end_time', $form_state->getValue('default_end_time')->getTimestamp())
       ->set('default_by_quantity', $form_state->getValue('default_by_quantity'))
       ->set('reminder_length', $form_state->getValue('reminder_length'))
       ->set('reminder_granularity', $form_state->getValue('reminder_granularity'))
       ->set('default_show_expiration', $form_state->getValue('default_show_expiration'))
       ->set('default_expiration_header', $form_state->getValue('default_expiration_header'))
       ->set('default_expiration_title', $form_state->getValue('default_expiration_title'))
       ->set('default_expiration_message', $form_state->getValue('default_expiration_message'))
       ->save();

     parent::submitForm($form, $form_state);
   }
 }
