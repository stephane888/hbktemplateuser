<?php

namespace Drupal\hbktemplateuser\Plugin\Layout\Sections;

use Drupal\formatage_models\Plugin\Layout\Sections\FormatageModelsSection;
use Drupal\bootstrap_styles\StylesGroup\StylesGroupManager;
use Drupal\formatage_models\FormatageModelsThemes;

/**
 * A very advanced custom layout.
 *
 * @Layout(
 *   id = "hbktem_dashbord_shard",
 *   label = @Translation(" Dashboard shard "),
 *   category = @Translation("hbktemplateuser"),
 *   path = "layouts/sections",
 *   template = "hbktemplateuser-dashbord-shard",
 *   library = "hbktemplateuser/hbktemplateuser-dashbord-shard",
 *   default_region = "contents",
 *   regions = {
 *     "logo" = {
 *       "label" = @Translation("logo"),
 *     },
 *     "branding" = {
 *       "label" = @Translation("branding"),
 *     },
 *     "aside" = {
 *       "label" = @Translation("aside"),
 *     },
 *     "search" = {
 *       "label" = @Translation("search")
 *     },
 *     "infos_user" = {
 *       "label" = @Translation("infos_user")
 *     },
 *     "contents" = {
 *       "label" = @Translation("contents")
 *     },
 *     "footer" = {
 *       "label" = @Translation("footer")
 *     }
 *   }
 * )
 */
class HbktemplateuserDaashbordhard extends FormatageModelsSection {
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\formatage_models\Plugin\Layout\FormatageModels::__construct()
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StylesGroupManager $styles_group_manager) {
    // TODO Auto-generated method stub
    parent::__construct($configuration, $plugin_id, $plugin_definition, $styles_group_manager);
    $this->pluginDefinition->set('icon', drupal_get_path('module', 'hbktemplateuser') . "/icones/sections/hbktem_dashbord_shard.png");
  }
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\formatage_models\Plugin\Layout\FormatageModels:build()
   */
  public function build(array $regions) {
    // TODO Auto-generated method stub
    $build = parent::build($regions);
    FormatageModelsThemes::formatSettingValues($build);
    return $build;
  }
  
  /**
   * -
   */
  public function defaultConfiguration() {
    return [
      'css' => 'h-100'
    ] + parent::defaultConfiguration();
  }
  
}