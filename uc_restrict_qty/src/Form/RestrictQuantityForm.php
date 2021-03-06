<?php

namespace Drupal\uc_restrict_qty\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * Creates or edits a role feature for a product.
 */
class RestrictQuantityForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uc_restrict_qty_feature_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = NULL, $feature = NULL) {

  //Get node id  
  $node = \Drupal::routeMatch()->getParameter('node');
  if ($node instanceof \Drupal\node\NodeInterface) {
    // You can get nid and anything else you need from the node object.
    $nid = $node->id();
  }

  $models = uc_product_get_models($node->id());
  if (!empty($feature)) {
    $product_qty = db_query("SELECT * FROM {uc_restrict_qty_products} WHERE pfid = :pfid", array(':pfid' => $feature['pfid']))->fetchObject();

    $default_qty = $product_qty->qty;
    $default_model = $product_qty->model;
    $default_lifetime = $product_qty->lifetime;

    $form['pfid'] = array(
      '#type' => 'value',
      '#value' => $feature['pfid'],
    );
    $form['rqpid'] = array(
      '#type' => 'value',
      '#value' => $product_qty->rqpid,
    );
  }

  $form['nid'] = array(
    '#type' => 'value',
    '#value' => $nid,
  );
  $form['model'] = array(
    '#type' => 'select',
    '#title' => t('SKU'),
    '#default_value' => isset($default_model) ? $default_model : 0,
    '#description' => t('This is the SKU of the product that will be restricted to this quantity.'),
    '#options' => $models,
  );
  $form['quantity'] = array(
    '#title' => t('Quantity limit'),
    '#type' => 'textfield',
    '#size' => 5,
    '#maxlength' => 5,
    '#description' => t('The number of times this product can be added to a cart. Set to 0 for unlimited.'),
    //'#default_value' => isset($default_qty) ? $default_qty : variable_get('uc_restrict_qty_default_qty', 0),
  );
  $form['lifetime'] = array(
    '#title' => t('Is it a lifetime restriction?'),
    '#type' => 'checkbox',
    '#description' => t("If checked, user's ordering history will be taken into account too. Useful when you want to prevent double ordering of a product."),
    //'#default_value' => isset($default_lifetime) ? $default_lifetime : variable_get('uc_restrict_qty_default_lifetime', FALSE),
  );
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => $this->t('Save feature'),
  );

  return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
      // Check for invalid quantity.
  if (!is_numeric($form_state->getValue('quantity')) || $form_state->getValue('quantity') < 0) {
   // form_set_error('uc_restrict_qty_product_qty', t('You must enter 0 or a positive integer value.'));
    $form_state->setErrorByName('uc_restrict_qty_product_qty', t('You must enter 0 or a positive integer value.'));
  }

  // Check if this feature is already set on this SKU.
  // $product_roles = db_query("SELECT * FROM {uc_restrict_qty_products} WHERE nid = :nid AND model = :model", array(
  //   ':nid' => $form_state['values']['nid'],
  //   ':model' => $form_state['values']['model'],
  // ))->fetchObject();
     $product_roles = db_select('uc_restrict_qty_products', 'urqp')
    ->fields('urqp', array('nid'))
    ->condition('nid', $nid,'=')
    ->execute()
    ->fetchAssoc();

  if ($product_roles && $form_state->getValue('pfid') == 0) {
    //form_set_error('uc_roles_model', t('A quantity restriction has already been set up for this SKU'));
    $form_state->setErrorByName('uc_roles_model', t('A quantity restriction has already been set up for this SKU.'));
  }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  $product_qty = array(
    'rqpid'       => $form_state->getValue('rqpid'),
    'pfid'        => $form_state->getValue('pfid'),
    'nid'         => $form_state->getValue('nid'),
    'model'       => $form_state->getValue('model'),
    'qty'         => $form_state->getValue('quantity'),
    'lifetime'    => $form_state->getValue('lifetime'),
  );

  $description = '<strong>' . t('SKU') . ':</strong>' . (empty($product_qty['model']) ? t('Any') : $product_qty['model']) . '<br/>';
  $description .= '<strong>' . t('Quantity restriction') . ':</strong>' . $product_qty['qty'] . '<br/>';
  $description .= '<strong>' . t('Type') . ':</strong>' . ($product_qty['lifetime'] ? t('Lifetime') : t('Cart max.')) . '<br/>';

  $data = array(
    'nid' => $form_state->getValue('nid'),
    'fid' => 'restrict_qty',
    'description' => $description,
  );

  if (isset($product_qty['pfid'])) {
    $data['pfid'] = $product_qty['pfid'];
  }

 // $form_state['redirect'] = uc_product_feature_save($data);
  $form_state->setRedirect = uc_product_feature_save($data);

  $key = array();
  if ($product_qty['rqpid']) {
    $key[] = 'rqpid';
  }

  // Insert or update uc_file_product table
  if (empty($product_qty['pfid'])) {
    $product_qty['pfid'] = $data['pfid'];
  }

$insert_restrict_qty_products = db_insert('uc_restrict_qty_products')
->fields(
array(
'rqpid' => $form_state->getValue('rqpid'),
'pfid' => $product_qty['pfid'],
'nid' => $form_state->getValue('nid'),
'model' => $form_state->getValue('model'),
'qty' => $form_state->getValue('quantity'),
'lifetime' => $form_state->getValue('lifetime'),
)
)->execute();

//  drupal_write_record('uc_restrict_qty_products', $product_qty, $key);
  }

}
