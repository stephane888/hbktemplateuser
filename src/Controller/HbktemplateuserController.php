<?php

namespace Drupal\hbktemplateuser\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Returns responses for hbktemplateuser routes.
 */
class HbktemplateuserController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
    $links = [];
    $links[] = [
      '#type' => 'link',
      '#title' => 'exportation du theme',
      '#url' => Url::fromRoute('export_import_entities.generatesite'),
      '#attributes' => [
        'class' => [
          'toolbar-icon'
        ]
      ]
    ];
    // $this->ExportEntities->getEntites();
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works! ..')
    ];
    $build['links'] = $links;
    return $build;
  }

}