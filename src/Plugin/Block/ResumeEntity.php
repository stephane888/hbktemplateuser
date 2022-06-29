<?php

namespace Drupal\hbktemplateuser\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layoutgenentitystyles\Services\LayoutgenentitystylesServices;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\hbktemplateuser\Services\Layouts\HbktemplateuserGenerateLayouts;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "hbktemplateuser_resume_entity",
 *   admin_label = @Translation(" Resume Entity "),
 *   category = @Translation("hbktemplateuser")
 * )
 */
class ResumeEntity extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   *
   * @var LayoutgenentitystylesServices
   */
  protected $LayoutgenentitystylesServices;
  
  /**
   * The entity type manager.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  
  /**
   *
   * @var HbktemplateuserGenerateLayouts
   */
  protected $HbktemplateuserGenerateLayouts;
  
  /**
   * Constructs a new CartBlock.
   *
   * @param array $configuration
   *        A configuration array containing information about the plugin
   *        instance.
   * @param string $plugin_id
   *        The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *        The plugin implementation definition.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *        The cart provider.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *        The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LayoutgenentitystylesServices $LayoutgenentitystylesServices, EntityTypeManagerInterface $entity_type_manager, HbktemplateuserGenerateLayouts $HbktemplateuserGenerateLayouts) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->LayoutgenentitystylesServices = $LayoutgenentitystylesServices;
    $this->entityTypeManager = $entity_type_manager;
    $this->HbktemplateuserGenerateLayouts = $HbktemplateuserGenerateLayouts;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('layoutgenentitystyles.add.style.theme'), $container->get('entity_type.manager'), $container->get('hbktemplateuser.generate.layouts'));
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function build() {
    $regions = [];
    $nbre = 0;
    if (!empty($this->configuration['type_entity']) && !empty($this->configuration['content']['type'])) {
      $nodeType = $this->entityTypeManager->getStorage($this->configuration['type_entity'])->load($this->configuration['content']['type']);
      if ($this->configuration['type_entity'] == 'node_type') {
        $entityQuery = $this->entityTypeManager->getStorage('node')->getQuery();
        $query = $entityQuery->condition('type', $nodeType->id())->condition('status', true);
        $nbre = $query->count()->execute();
      }
      elseif ($this->configuration['type_entity'] == 'commerce_product_type') {
        $entityQuery = $this->entityTypeManager->getStorage('commerce_product')->getQuery();
        $query = $entityQuery->condition('status', true)->condition('type', $this->configuration['content']['type']);
        $nbre = $query->count()->execute();
      }
      
      $titre = [
        '#type' => 'link',
        '#title' => [
          '#type' => 'inline_template',
          '#template' => $nodeType->label()
        ],
        '#url' => \Drupal\Core\Url::fromUri('internal:/manage-content/' . $this->configuration['content']['type'], []),
        '#attributes' => []
      ];
      $regions = [
        'title' => [
          $titre
        ],
        'icone' => [
          '#markup' => '<i class="far fa-folder"></i>'
        ],
        'nombre' => [
          '#markup' => $nbre
        ]
      ];
    }
    
    $build = [
      '#theme' => 'hbktemplateuser_resume_entity',
      '#block' => $this->HbktemplateuserGenerateLayouts->getLayout('hbktemplateuser_info_resume', $regions)
    ];
    return $build;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    // dump($this->configuration);
    // on a rencontrer un probleme sur layout.
    $complete_form_state = $form_state instanceof SubformStateInterface ? $form_state->getCompleteFormState() : $form_state;
    $plugins = $this->entityTypeManager->getDefinitions();
    $options = [];
    foreach ($plugins as $k => $plugin) {
      $options[$k] = $plugin->getLabel();
    }
    //
    $form['type_entity'] = [
      '#type' => 'select',
      '#title' => t(" selectionner le type d'entité "),
      '#options' => $options,
      '#default_value' => $this->configuration['type_entity'],
      '#ajax' => [
        'callback' => [
          $this,
          '_blockFormCallback'
        ],
        'disable-refocus' => FALSE,
        'event' => 'change',
        'wrapper' => 'hbktemplateuser-resume-entity-type_entity',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t(' Verifying entry... ')
        ]
      ]
    ];
    $form['content'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'hbktemplateuser-resume-entity-type_entity'
      ]
    ];
    $options_type = [];
    if ($form_state instanceof SubformStateInterface) {
      $form_state = $form_state->getCompleteFormState();
    }
    
    if ($complete_form_state->hasValue([
      'settings',
      'type_entity'
    ])) {
      $type_entity = $complete_form_state->getValue([
        'settings',
        'type_entity'
      ]);
      switch ($type_entity) {
        case 'node_type':
        case 'commerce_product_type':
        case 'commerce_product':
          $list_entities_type = $this->entityTypeManager->getStorage($type_entity)->loadMultiple();
          foreach ($list_entities_type as $k => $value) {
            $options_type[$k] = $value->label();
          }
          ;
          break;
        
        default:
          $this->messenger()->addWarning(" Type de contenu non traiter ");
          break;
      }
    }
    elseif (!empty($this->configuration['type_entity'])) {
      $type_entity = $this->configuration['type_entity'];
      $list_entities_type = $this->entityTypeManager->getStorage($type_entity)->loadMultiple();
      foreach ($list_entities_type as $k => $value) {
        $options_type[$k] = $value->label();
      }
    }
    $form['content']['type'] = [
      '#type' => 'select',
      '#title' => $this->t(' Bundle '),
      '#options' => $options_type,
      '#default_value' => $this->configuration['content']['type']
    ];
    
    return $form;
  }
  
  public function _blockFormCallback($form, FormStateInterface $form_state) {
    return $form['settings']['content'];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      // on definit ici le style utilisé par le layout.
      'block_load_style_scss_js' => 'hbktemplateuser/hbktemplateuser-info-resume',
      'type_entity' => 'node_type',
      'content' => [
        'type' => ''
      ],
      'icone' => '',
      'title' => ''
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $library = $this->configuration['block_load_style_scss_js'];
    $this->LayoutgenentitystylesServices->addStyleFromModule($library, 'hbktemplateuser_resume_entity', 'layout', 'teasers/');
    // save
    $this->configuration['type_entity'] = $form_state->getValue('type_entity');
    $this->configuration['content'] = $form_state->getValue('content');
    $this->configuration['icone'] = $form_state->getValue('icone');
    $this->configuration['title'] = $form_state->getValue('title');
  }
  
}
