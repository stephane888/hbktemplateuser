<?php

namespace Drupal\hbktemplateuser\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

class BaseResumeEntity extends BlockBase {
  
  public function build() {
    return [];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      // on definit ici le style utilisé par le layout.
      'block_load_style_scss_js' => 'hbktemplateuser/hbktemplateuser-info-resume',
      'icone' => ''
    ];
  }
  
  public function buildConfigurationForm($form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $value = $this->configuration['icone'];
    $form['icone'] = [
      '#type' => 'text_format',
      '#title' => 'icone',
      '#format' => (isset($value["format"])) ? $value["format"] : 'full_html',
      '#default_value' => (isset($value["value"])) ? $value["value"] : '<i class="far fa-folder"></i>',
      '#attributes' => []
    ];
    return $form;
  }
  
  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *        One field item.
   *        
   * @return array The textual output generated as a render array.
   */
  protected function viewValue($value, $default = '') {
    if (empty($value))
      $value = $default;
    return [
      '#type' => 'inline_template',
      '#template' => '{{ value|raw }}',
      '#context' => [
        'value' => $value
      ]
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $library = $this->configuration['block_load_style_scss_js'];
    $this->LayoutgenentitystylesServices->addStyleFromModule($library, 'hbktemplateuser_resume_entity', 'layout', 'teasers/');
    // save
    $this->configuration['icone'] = $form_state->getValue('icone');
  }
  
  protected function buildBlockEntityId(array $contents, array &$sections, $baseRouteEntity = 'entity.site_internet_entity.') {
    foreach ($contents as $content) {
      /**
       *
       * @var \Drupal\creation_site_virtuel\Entity\SiteInternetEntity $content
       */
      $titre = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => [
            'd-flex title-manage justify-content-between flex-wrap'
          ]
        ],
        [
          [
            '#type' => 'link',
            '#title' => $this->viewValue($content->label(), 'Page'),
            '#url' => \Drupal\Core\Url::fromRoute($baseRouteEntity . 'edit_form', [
              'site_internet_entity' => $content->id()
            ], [
              'query' => [
                'destination' => $this->Request->getPathInfo()
              ]
            ]),
            '#attributes' => [
              'data-toggle' => 'tooltip',
              'title' => $this->t('Edit')
            ]
          ],
          [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#attributes' => [
              'class' => [
                'items'
              ]
            ],
            [
              [
                '#type' => 'link',
                '#title' => $this->viewValue('<i class="far fa-eye"></i>'),
                '#url' => \Drupal\Core\Url::fromRoute($baseRouteEntity . 'canonical', [
                  'site_internet_entity' => $content->id()
                ]),
                '#attributes' => [
                  'class' => [
                    'item',
                    'see',
                    'text-info'
                  ],
                  'data-toggle' => 'tooltip',
                  'title' => $this->t('See')
                ]
              ],
              [
                '#type' => 'link',
                '#title' => $this->viewValue('<i class="far fa-edit"></i>'),
                '#url' => \Drupal\Core\Url::fromRoute('entity.site_internet_entity.edit_form', [
                  'site_internet_entity' => $content->id()
                ], [
                  'query' => [
                    'destination' => $this->Request->getPathInfo()
                  ]
                ]),
                '#attributes' => [
                  'class' => [
                    'item',
                    'edit',
                    'text-primary'
                  ],
                  'data-toggle' => 'tooltip',
                  'title' => $this->t('Edit')
                ]
              ],
              [
                '#type' => 'link',
                '#title' => $this->viewValue('<i class="fas fa-trash-alt"></i>'),
                '#url' => \Drupal\Core\Url::fromRoute('entity.site_internet_entity.delete_form', [
                  'site_internet_entity' => $content->id()
                ], [
                  'query' => [
                    'destination' => $this->Request->getPathInfo()
                  ]
                ]),
                '#attributes' => [
                  'class' => [
                    'item',
                    'delete',
                    'text-danger'
                  ],
                  'data-toggle' => 'tooltip',
                  'title' => $this->t('Delete')
                ]
              ]
            ]
          ]
        ]
      ];
      $regions = [
        'title' => [
          $titre
        ],
        'icone' => $this->viewValue(!empty($this->configuration['icone']['value']) ? $this->configuration['icone']['value'] : '<i class="far fa-folder"></i>'),
        'nombre' => [
          '#markup' => 1
        ]
      ];
      $sections[] = [
        '#theme' => 'hbktemplateuser_resume_entity',
        '#block' => $this->HbktemplateuserGenerateLayouts->getLayout('hbktemplateuser_info_resume', $regions)
      ];
    }
  }
  
}