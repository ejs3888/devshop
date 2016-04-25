<?php

/**
 * Form function for the softlayer options form.
 * @return array
 */
function devshop_softlayer_options_form() {
  $form = array();

  $username = variable_get('devshop_cloud_softlayer_api_username', array());
  $key = variable_get('devshop_cloud_softlayer_api_key', array());

  if (empty($username) || empty($key)) {
    $form['warning'] = array(
      '#prefix' => '<div class="alert alert-danger">',
      '#suffix' => '</div>',
      '#value' => t('You must enter your softlayer username and API key before you can use this form.  See !link', array('!link' => l(t('Cloud Settings'), 'admin/hosting/devshop/cloud'))),
      '#weight' => 10,
    );
  }
  else {
    $options = variable_get('devshop_cloud_softlayer_options', array());

    $form['info'] = array(
      '#type' => 'item',
      '#title' => t('SoftLayer Options'),
      '#markup' => empty($options)? t('No options available. Click "Refresh SoftLayer Options".'): 'SoftLayer options are saved.',
    );

    $keys = variable_get('devshop_cloud_softlayer_ssh_keys', array());
    $key_count = count($keys);
    $form['keys'] = array(
      '#type' => 'item',
      '#title' => t('SoftLayer SSH Keys'),
      '#markup' => format_plural($key_count, t('One key available'), t('!num keys available', array(
      '!num' => $key_count
    ))),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Refresh SoftLayer Options'),
    );
  }

  return $form;
}

/**
 *
 */
function devshop_softlayer_options_form_submit() {

  require_once dirname(__FILE__) . '/softlayer-api-php-client/SoftLayer/SoapClient.class.php';

  $apiUsername = variable_get('devshop_cloud_softlayer_api_username', array());
  $apiKey = variable_get('devshop_cloud_softlayer_api_key', array());

  // Get Create options
  try {
    $client = SoftLayer_SoapClient::getClient('SoftLayer_Virtual_Guest', null, $apiUsername, $apiKey);
    $options['options'] = $client->getCreateObjectOptions();
    variable_set('devshop_cloud_softlayer_options', $options['options']);

    $ssh_key_client  = SoftLayer_SoapClient::getClient('SoftLayer_Account', null, $apiUsername, $apiKey);

    $ssh_keys = $ssh_key_client->getSshKeys();
    foreach ($ssh_keys as $key) {
      $key_options[$key->id] = $key->label;
    }
    dsm($key_options);
    variable_set('devshop_cloud_softlayer_ssh_keys', $key_options);
    drupal_set_message(t('SoftLayer options have been saved.'));

  } catch (Exception $e) {
    drupal_set_message($e->getMessage(), 'error');
  }
}