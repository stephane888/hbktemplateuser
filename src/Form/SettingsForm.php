<?php

namespace Drupal\hbktemplateuser\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure hbktemplateuser settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hbktemplateuser_settings';
  }

  /**
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'hbktemplateuser.settings'
    ];
  }

  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('hbktemplateuser.settings');
    $form['page_user_template'] = [
      '#type' => 'select',
      '#title' => $this->t(" Template pour le dashbord "),
      '#options' => [
        '' => 'Aucun',
        'page_shards' => 'Modele 1 light '
      ],
      '#default_value' => $config->get('page_user_template')
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('hbktemplateuser.settings');
    $config->set('page_user_template', $form_state->getValue('page_user_template'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
