<?php

namespace Drupal\hbktemplateuser\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for hbktemplateuser routes.
 */
class HbktemplateuserController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
