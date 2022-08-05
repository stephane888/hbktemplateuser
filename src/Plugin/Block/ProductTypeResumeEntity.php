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
 *   id = "hbktemplateuser_product_type_resume_entity",
 *   admin_label = @Translation(" Product type resume entity "),
 *   category = @Translation("hbktemplateuser")
 * )
 */
class ProductTypeResumeEntity extends BlockBase implements ContainerFactoryPluginInterface {
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
    $uid = \Drupal::currentUser()->id();
    $regions = [];
    $nbre = 0;
    $build = [];
    $sections = [];
    $typesProduct = $this->entityTypeManager->getStorage('commerce_product_type')->loadMultiple();
    foreach ($typesProduct as $value) {
      if ($value->id() == 'default')
        continue;
      $entityQuery = $this->entityTypeManager->getStorage('commerce_product')->getQuery();
      $query = $entityQuery->condition('status', true)->condition('type', $value->id())->condition('field_domain_access', $this->DomainNegotiator->getActiveId());
      // un produit peut appartenir à un ou plusiseurs domaines, mais en realité
      // il appartient à un utilisateur bien precis. (voir la tache/2183)
      $query->condition('uid', $uid);
      $nbre = $query->count()->execute();
      $link = 'internal:/manage-product/';
      $link = \Drupal\Core\Url::fromUri($link . $value->id(), []);
      
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
        'icone' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => !empty($this->configuration['content']['icone']['value']) ? $this->configuration['content']['icone']['value'] : '<i class="far fa-folder"></i>'
        ],
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
    //
    return $build;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    return $form;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      // on definit ici le style utilisé par le layout.
      'block_load_style_scss_js' => 'hbktemplateuser/hbktemplateuser-info-resume'
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
