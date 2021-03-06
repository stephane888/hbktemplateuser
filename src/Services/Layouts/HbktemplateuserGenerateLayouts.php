<?php

namespace Drupal\hbktemplateuser\Services\Layouts;

use Drupal\Core\Layout\LayoutPluginManager;
use Drupal\formatage_models\Plugin\Layout\FormatageModels;

class HbktemplateuserGenerateLayouts {
  protected $LayoutPluginManager;
  protected $intences = [];
  
  function __construct(LayoutPluginManager $LayoutPluginManager) {
    $this->LayoutPluginManager = $LayoutPluginManager;
  }
  
  /**
   * Permet de faire le rendu d'un layout.
   */
  function getLayout($plugin_id, array $regions = [], $removeDefaultContent = true) {
    if (empty($this->intences[$plugin_id])) {
      /**
       *
       * @var \Drupal\formatage_models\Plugin\Layout\FormatageModels $layout
       */
      $layout = $this->LayoutPluginManager->createInstance($plugin_id);
      $this->intences[$plugin_id] = $layout;
    }
    else
      $layout = $this->intences[$plugin_id];
    // remove default content
    if ($removeDefaultContent) {
      $config = $layout->getConfiguration();
      foreach ($layout->getConfiguration() as $k => $value) {
        if (is_array($value) && !empty($value['builder-form'])) {
          $config[$k]['builder-form'] = false;
        }
      }
      $layout->setConfiguration($config);
    }
    // dump($layout->getPluginDefinition()->getRegions());
    return $layout->build($regions);
  }
  
  /**
   *
   * @param FormatageModels $layout
   * @return array
   */
  function getRegions(FormatageModels $layout) {
    $regions = [];
    foreach ($layout->getPluginDefinition()->getRegions() as $regin_name => $label) {
      $regions[$regin_name] = [];
    }
    return $regions;
  }
  
}