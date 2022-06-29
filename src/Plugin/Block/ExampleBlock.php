<?php

namespace Drupal\hbktemplateuser\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "hbktemplateuser_example",
 *   admin_label = @Translation("Example"),
 *   category = @Translation("hbktemplateuser")
 * )
 */
class ExampleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('It works!'),
    ];
    return $build;
  }

}
