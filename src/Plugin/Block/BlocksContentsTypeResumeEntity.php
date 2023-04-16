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
use Drupal\domain\DomainNegotiator;
use Drupal\block_content\Entity\BlockContent;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "hbktemplateuser_blocks_contents_resume",
 *   admin_label = @Translation(" Permet de contruire uniquement les resumes de type 'Blocks contents'. "),
 *   category = @Translation("hbktemplateuser")
 * )
 */
class BlocksContentsTypeResumeEntity extends BaseResumeEntity implements ContainerFactoryPluginInterface {
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
   *
   * @var DomainNegotiator
   */
  protected $DomainNegotiator;
  
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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LayoutgenentitystylesServices $LayoutgenentitystylesServices, EntityTypeManagerInterface $entity_type_manager, HbktemplateuserGenerateLayouts $HbktemplateuserGenerateLayouts, DomainNegotiator $DomainNegotiator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->LayoutgenentitystylesServices = $LayoutgenentitystylesServices;
    $this->entityTypeManager = $entity_type_manager;
    $this->HbktemplateuserGenerateLayouts = $HbktemplateuserGenerateLayouts;
    $this->DomainNegotiator = $DomainNegotiator;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('layoutgenentitystyles.add.style.theme'), $container->get('entity_type.manager'), $container->get('hbktemplateuser.generate.layouts'), $container->get('domain.negotiator'));
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function build() {
    $regions = [];
    $nbre = 0;
    $build = [];
    $sections = [];
    $typesProduct = $this->entityTypeManager->getStorage('blocks_contents_type')->loadMultiple();
    foreach ($typesProduct as $value) {
      $entityQuery = $this->entityTypeManager->getStorage('blocks_contents')->getQuery();
      $query = $entityQuery->condition('status', true)->condition('type', $value->id())->condition('field_domain_access', $this->DomainNegotiator->getActiveId());
      $nbre = $query->count()->execute();
      $link = 'internal:/manage-blocks_contents/';
      $link = \Drupal\Core\Url::fromUri($link . $value->id(), []);
      //
      
      if ($nbre == 0)
        continue;
      
      $titre = [
        '#type' => 'link',
        '#title' => [
          '#type' => 'inline_template',
          '#template' => $value->label()
        ],
        '#url' => $link,
        '#attributes' => []
      ];
      
      $regions = [
        'title' => [
          $titre
        ],
        'icone' => $this->viewValue(!empty($this->configuration['icone']['value']) ? $this->configuration['icone']['value'] : '<i class="far fa-folder"></i>'),
        'nombre' => [
          '#markup' => $nbre
        ]
      ];
      $sections[] = [
        '#theme' => 'hbktemplateuser_resume_entity',
        '#block' => $this->HbktemplateuserGenerateLayouts->getLayout('hbktemplateuser_info_resume', $regions)
      ];
    }
    $build['content'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'row'
        ]
      ],
      $sections
    ];
    if (!empty($sections))
      return $build;
    else
      return [];
    return $build;
  }
  
}
