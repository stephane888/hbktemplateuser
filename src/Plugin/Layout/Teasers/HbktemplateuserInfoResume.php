<?php

namespace Drupal\hbktemplateuser\Plugin\Layout\Teasers;

use Drupal\formatage_models\Plugin\Layout\Teasers\FormatageModelsTeasers;
use Drupal\bootstrap_styles\StylesGroup\StylesGroupManager;
use Drupal\formatage_models\FormatageModelsThemes;

/**
 * A very advanced custom layout.
 *
 * @Layout(
 *   id = "hbktemplateuser_info_resume",
 *   label = @Translation(" Info resume "),
 *   category = @Translation("hbktemplateuser"),
 *   path = "layouts/teasers",
 *   template = "hbktemplateuser-info-resume",
 *   library = "hbktemplateuser/hbktemplateuser-info-resume",
 *   default_region = "body",
 *   regions = {
 *     "title" = {
 *       "label" = @Translation("Title"),
 *     },
 *     "icone" = {
 *       "label" = @Translation("icone"),
 *     },
 *     "nombre" = {
 *       "label" = @Translation("Nombre")
 *     },
 *     "link" = {
 *       "label" = @Translation("Link")
 *     }
 *   }
 * )
 */
class HbktemplateuserInfoResume extends FormatageModelsTeasers {
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\formatage_models\Plugin\Layout\FormatageModels::__construct()
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StylesGroupManager $styles_group_manager) {
    // TODO Auto-generated method stub
    parent::__construct($configuration, $plugin_id, $plugin_definition, $styles_group_manager);
    $this->pluginDefinition->set('icon', drupal_get_path('module', 'hbktemplateuser') . "/icones/teasers/formatage-models-blog-call-toaction.png");
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
      'css' => 'd-flex h-100 flex-column justify-content-between',
      'sf' => [
        'builder-form' => true,
        'info' => [
          'title' => 'Contenu',
          'loader' => 'static'
        ],
        'fields' => [
          'title' => [
            'text' => [
              'label' => "Titre",
              'value' => "resalisations"
            ]
          ],
          'icone' => [
            'text_html' => [
              'label' => "icone",
              'value' => '<i class="far fa-folder"></i>',
              'format' => "basic_html"
            ]
          ],
          'nombre' => [
            'text' => [
              'label' => "nombre",
              'value' => "358"
            ]
          ],
          'link' => [
            'url' => [
              'label' => "Call action",
              'value' => [
                'link' => "#"
              ]
            ]
          ]
        ]
      ]
    ] + parent::defaultConfiguration();
  }
  
}