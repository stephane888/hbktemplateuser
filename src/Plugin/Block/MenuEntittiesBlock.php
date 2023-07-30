<?php

namespace Drupal\hbktemplateuser\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\Core\Http\RequestStack;
use Drupal\domain\DomainNegotiator;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "hbktemplateuser_menu_entities",
 *   admin_label = @Translation("Menu entities block"),
 *   category = @Translation("hbktemplateuser")
 * )
 */
class MenuEntittiesBlock extends BaseResumeEntity implements ContainerFactoryPluginInterface {
  
  /**
   * The entity type manager.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  protected $Request;
  /**
   *
   * @var DomainNegotiator
   */
  protected $DomainNegotiator;
  
  function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, RequestStack $RequestStack, DomainNegotiator $DomainNegotiator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->Request = $RequestStack->getCurrentRequest();
    $this->DomainNegotiator = $DomainNegotiator;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity_type.manager'), $container->get('request_stack'), $container->get('domain.negotiator'));
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function build() {
    if (!$this->userIsAdministratorOfDomaine()) {
      return [];
    }
    $build['content'] = [
      '#theme' => 'nav_shards_dashbord',
      '#items' => !empty($this->configuration['override_menus']) ? $this->formatValues($this->configuration['entities']) : $this->buildDefaultEntities()
    ];
    return $build;
  }
  
  public function defaultConfiguration() {
    return [
      'override_menus' => false,
      'entities' => [],
      'group' => [
        '' => 'Aucun',
        'content' => 'Gestion de contenus',
        'ecommerce' => 'E-commerce',
        'liens_utilie' => 'Liens utile',
        'config' => 'Configuration'
      ]
    ] + parent::defaultConfiguration();
  }
  
  public function buildConfigurationForm($form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['override_menus'] = [
      "#type" => 'checkbox',
      "#title" => 'override_menus',
      "#default_value" => isset($this->configuration['override_menus']) ? $this->configuration['override_menus'] : false
    ];
    $form['entities'] = [
      '#type' => 'details',
      '#title' => 'entities',
      '#open' => true
    ];
    $entities = $this->buildDefaultEntities();
    
    foreach ($entities as $id => $entity) {
      $group = isset($this->configuration['entities'][$id]['group']) ? $this->configuration['entities'][$id]['group'] : '';
      $form['entities'][$id] = [
        '#type' => 'details',
        '#title' => $id . ' (' . $group . ')',
        '#open' => false,
        "#weight" => !empty($this->configuration['entities'][$id]['active']) ? -10 : 10
      ];
      $form['entities'][$id]['active'] = [
        "#type" => 'checkbox',
        "#title" => 'active',
        "#default_value" => isset($this->configuration['entities'][$id]['active']) ? $this->configuration['entities'][$id]['active'] : $entity['active']
      ];
      $form['entities'][$id]['label'] = [
        "#type" => 'textfield',
        "#title" => 'Surcharger le label',
        "#default_value" => isset($this->configuration['entities'][$id]['label']) ? $this->configuration['entities'][$id]['label'] : $entity['label']
      ];
      $form['entities'][$id]['url'] = [
        "#type" => 'textfield',
        "#title" => "Surcharger l'url",
        "#default_value" => '/manage-' . $id . '/'
        // "#default_value" =>
        // isset($this->configuration['entities'][$id]['url']) ?
        // $this->configuration['entities'][$id]['url'] : $entity['url']
      ];
      $form['entities'][$id]['group'] = [
        "#type" => 'select',
        "#title" => "Selectionner un group",
        "#options" => $this->configuration['group'],
        "#default_value" => $group
      ];
      $form['entities'][$id]['icone'] = [
        "#type" => 'textarea',
        "#title" => "Surcharger l'icone ",
        "#default_value" => isset($this->configuration['entities'][$id]['icone']) ? $this->configuration['entities'][$id]['icone'] : $entity['icone']
      ];
      if (!empty($entities[$id]['childrens'])) {
        $form['entities'][$id]['childrens'] = [
          '#type' => 'details',
          '#title' => 'Bunbles',
          '#open' => false
        ];
        foreach ($entities[$id]['childrens'] as $sub_id => $children) {
          $form['entities'][$id]['childrens'][$sub_id]['active'] = [
            "#type" => 'checkbox',
            "#title" => 'active',
            "#default_value" => isset($this->configuration['entities'][$id]['childrens'][$sub_id]['active']) ? $this->configuration['entities'][$id]['childrens'][$sub_id]['active'] : $children['active']
          ];
          $form['entities'][$id]['childrens'][$sub_id]['label'] = [
            "#type" => 'textfield',
            "#title" => 'Surcharger le label',
            "#default_value" => isset($this->configuration['entities'][$id]['childrens'][$sub_id]['label']) ? $this->configuration['entities'][$id]['childrens'][$sub_id]['label'] : $children['label']
          ];
          $form['entities'][$id]['childrens'][$sub_id]['url'] = [
            "#type" => 'textfield',
            "#title" => "Surcharger l'url",
            "#default_value" => '/manage-' . $id . '/' . $sub_id
            // "#default_value" =>
            // isset($this->configuration['entities'][$id]['childrens'][$sub_id]['url'])
            // ?
            // $this->configuration['entities'][$id]['childrens'][$sub_id]['url']
            // : $children['url']
          ];
          $form['entities'][$id]['childrens'][$sub_id]['icone'] = [
            "#type" => 'textarea',
            "#title" => "Surcharger l'icone ",
            "#default_value" => isset($this->configuration['entities'][$id]['childrens'][$sub_id]['icone']) ? $this->configuration['entities'][$id]['childrens'][$sub_id]['icone'] : $children['icone']
          ];
        }
      }
    }
    return $form;
  }
  
  protected function formatValues($entities) {
    $key_default = 'default';
    $items = [];
    foreach ($this->configuration['group'] as $key => $label) {
      if ($key) {
        $items[$key] = [
          'title_group' => $label,
          'items' => []
        ];
      }
      else
        $items[$key_default] = [
          'title_group' => '',
          'items' => $this->CustomMenus()
        ];
    }
    foreach ($entities as $item) {
      $item['icone'] = $this->viewValue($item['icone']);
      if (!empty($item['childrens']))
        foreach ($item['childrens'] as &$value) {
          $value['icone'] = $this->viewValue($value['icone']);
        }
      if (!empty($item['group'])) {
        $items[$item['group']]['items'][] = $item;
      }
      else {
        $items[$key_default]['items'][] = $item;
      }
    }
    return $items;
  }
  
  protected function CustomMenus() {
    $domain = $this->DomainNegotiator->getActiveDomain();
    $themeConf = $this->entityTypeManager->getStorage("config_theme_entity")->loadByProperties([
      'hostname' => $domain->id()
    ]);
    $link_edit_theme = [];
    if (!empty($themeConf)) {
      $themeConf = reset($themeConf);
      $link_edit_theme = [
        'label' => 'Modifier: couleurs, tailles de polices, ...',
        'active' => true,
        'icone' => $this->viewValue('<i class="fas fa-paint-brush"></i>'),
        'url' => Url::fromRoute('entity.config_theme_entity.edit_form', [
          'config_theme_entity' => $themeConf->id()
        ], [
          'query' => [
            'destination' => $this->Request->getPathInfo()
          ]
        ]),
        'childrens' => []
      ];
    }
    $custom_items = [
      [
        'label' => 'Dashbord',
        'active' => true,
        'icone' => $this->viewValue('<i class="fas fa-tachometer-alt"></i>'),
        'url' => Url::fromRoute('user.page'),
        'class' => 'active',
        'childrens' => []
      ],
      [
        'label' => 'Configuration',
        'active' => true,
        'icone' => $this->viewValue('<i class="fas fa-sliders-h"></i>'),
        'url' => '',
        'childrens' => [
          $link_edit_theme,
          [
            'label' => $this->t('Export current theme'),
            'active' => true,
            'icone' => $this->viewValue('<i class="fas fa-download"></i>'),
            'url' => Url::fromRoute('export_import_entities.generatesite', []),
            'childrens' => []
          ],
          [
            'label' => $this->t('Custom CSS and JS code (developer)'),
            'active' => true,
            'icone' => $this->viewValue('<i class="fas fa-fill-drip"></i>'),
            'url' => Url::fromRoute('generate_style_theme.managecustom.styles', [], [
              'query' => [
                'destination' => $this->Request->getPathInfo()
              ]
            ]),
            'childrens' => []
          ],
          [
            'label' => $this->t('Add CSS and JS code (developer)'),
            'active' => true,
            'icone' => $this->viewValue('<i class="fas fa-truck-loading"></i>'),
            'url' => Url::fromRoute('layoutgenentitystyles.generate', [], [
              'query' => [
                'destination' => $this->Request->getPathInfo()
              ]
            ]),
            'childrens' => []
          ]
        ]
      ],
      [
        'label' => 'Configuration des RDVs.',
        'active' => true,
        'icone' => $this->viewValue('<i class="fas fa-calendar-alt"></i>'),
        'url' => Url::fromRoute('prise_rendez_vous.default_settings_form'),
        'class' => '',
        'childrens' => []
      ],
      [
        'label' => 'Configuration des passerelles de paiements.',
        'active' => true,
        'icone' => $this->viewValue('<i class="fas fa-calendar-alt"></i>'),
        'url' => Url::fromRoute('lesroidelareno.payement_gateways', [
          'payment_plugin_id' => 'list-all'
        ], [
          'query' => [
            'destination' => $this->Request->getPathInfo()
          ]
        ]),
        'class' => '',
        'childrens' => []
      ],
      [
        'label' => 'Menus',
        'active' => true,
        'icone' => $this->viewValue('<i class="fas fa-ellipsis-v"></i>'),
        'url' => Url::fromRoute('lesroidelareno.manage_menu', [], [
          'query' => [
            'destination' => $this->Request->getPathInfo()
          ]
        ]),
        'class' => '',
        'childrens' => []
      ]
    ];
    return $custom_items;
  }
  
  protected function buildDefaultEntities() {
    $entities = $this->entityTypeManager->getDefinitions();
    $results = [];
    foreach ($entities as $entity) {
      /**
       *
       * @var \Drupal\Core\Entity\ContentEntityType $entity
       */
      if ($entity->getBaseTable()) {
        $id = $entity->id();
        $results[$id] = [
          'label' => $entity->getLabel(),
          'active' => true,
          'icone' => $this->viewValue('<i class="fas fa-angle-double-right"></i>'),
          'url' => '',
          'childrens' => []
        ];
        if ($bundleEntityType = $entity->getBundleEntityType()) {
          $EntityTypesBundle = $this->entityTypeManager->getStorage($bundleEntityType)->loadMultiple();
          $subResult = [];
          foreach ($EntityTypesBundle as $TypeBundle) {
            $subResult[$TypeBundle->id()] = [
              'label' => $TypeBundle->label(),
              'active' => true,
              'icone' => $this->viewValue('<i class="fas fa-angle-double-right"></i>'),
              'url' => '',
              'childrens' => []
            ];
          }
          $results[$id]['childrens'] = $subResult;
        }
      }
    }
    return $results;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $override_menus = $form_state->getValue('override_menus');
    $this->configuration['override_menus'] = $override_menus;
    if ($override_menus) {
      $this->configuration['entities'] = $form_state->getValue('entities');
    }
    else {
      $this->configuration['entities'] = [];
    }
  }
  
}
