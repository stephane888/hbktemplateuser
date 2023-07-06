<?php

namespace Drupal\hbktemplateuser\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\layoutgenentitystyles\Services\LayoutgenentitystylesServices;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\hbktemplateuser\Services\Layouts\HbktemplateuserGenerateLayouts;
use Drupal\domain\DomainNegotiator;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Http\RequestStack;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "hbktemplateuser_resume_entity",
 *   admin_label = @Translation(" Permet de contruire different bloc de resumer en function du type d'entites. "),
 *   category = @Translation("hbktemplateuser")
 * )
 */
class ResumeEntity extends BaseResumeEntity implements ContainerFactoryPluginInterface {
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
  protected $Request;
  
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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LayoutgenentitystylesServices $LayoutgenentitystylesServices, EntityTypeManagerInterface $entity_type_manager, HbktemplateuserGenerateLayouts $HbktemplateuserGenerateLayouts, DomainNegotiator $DomainNegotiator, RequestStack $RequestStack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->LayoutgenentitystylesServices = $LayoutgenentitystylesServices;
    $this->entityTypeManager = $entity_type_manager;
    $this->HbktemplateuserGenerateLayouts = $HbktemplateuserGenerateLayouts;
    $this->DomainNegotiator = $DomainNegotiator;
    $this->Request = $RequestStack->getCurrentRequest();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('layoutgenentitystyles.add.style.theme'), $container->get('entity_type.manager'), $container->get('hbktemplateuser.generate.layouts'), $container->get('domain.negotiator'), $container->get('request_stack'));
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function build() {
    if (!$this->userIsAdministratorOfDomaine()) {
      return [];
    }
    $regions = [];
    $nbre = 0;
    $build = [];
    $sections = [];
    
    // dump($this->configuration);
    if (!empty($this->configuration['type_entity'])) {
      // gere les entitées avec bundle donc on souhaite afficher un bundle en
      // particulier.
      // Cela permet d'afficher le resume en function du nombre de node et pour
      // l'edition on passe par une vue.
      if (!empty($this->configuration['content']['type'])) {
        $nodeType = $this->entityTypeManager->getStorage($this->configuration['type_entity'])->load($this->configuration['content']['type']);
        if ($this->configuration['type_entity'] == 'node_type') {
          $entityQuery = $this->entityTypeManager->getStorage('node')->getQuery();
          $query = $entityQuery->condition('type', $nodeType->id())->condition('status', true);
          $nbre = $query->count()->execute();
          // $link = 'internal:/manage-content/';
          $link = 'internal:/manage-node/';
          $link = \Drupal\Core\Url::fromUri($link . $this->configuration['content']['type'], [
            'query' => [
              'destination' => $this->Request->getPathInfo()
            ]
          ]);
        }
        elseif ($this->configuration['type_entity'] == 'blocks_contents_type') {
          $entityQuery = $this->entityTypeManager->getStorage('blocks_contents')->getQuery();
          $query = $entityQuery->condition('status', true)->condition('type', $this->configuration['content']['type'])->condition('field_domain_access', $this->DomainNegotiator->getActiveId());
          $nbre = $query->count()->execute();
          // $link = 'internal:/manage-blocks-contents/';
          $link = 'internal:/manage-blocks_contents/';
          $link = \Drupal\Core\Url::fromUri($link . $this->configuration['content']['type'], [
            'query' => [
              'destination' => $this->Request->getPathInfo()
            ]
          ]);
        }
        elseif ($this->configuration['type_entity'] == 'commerce_product_type') {
          $entityQuery = $this->entityTypeManager->getStorage('commerce_product')->getQuery();
          $query = $entityQuery->condition('status', true)->condition('type', $this->configuration['content']['type'])->condition('field_domain_access', $this->DomainNegotiator->getActiveId());
          $nbre = $query->count()->execute();
          // $link = 'internal:/manage-product/';
          $link = 'internal:/manage-commerce_product/';
          $link = \Drupal\Core\Url::fromUri($link . $this->configuration['content']['type'], [
            'query' => [
              'destination' => $this->Request->getPathInfo()
            ]
          ]);
        }
        elseif ($this->configuration['type_entity'] == 'block_content_type') {
          $entityQuery = $this->entityTypeManager->getStorage('block_content')->getQuery();
          $query = $entityQuery->condition('status', true)->condition('type', $this->configuration['content']['type'])->condition('field_domain_access', $this->DomainNegotiator->getActiveId());
          $nbre = $query->count()->execute();
          // $link = 'internal:/block-content/';
          $link = 'internal:/manage-block_content/';
          $link = \Drupal\Core\Url::fromUri($link . $this->configuration['content']['type'], [
            'query' => [
              'destination' => $this->Request->getPathInfo()
            ]
          ]);
        }
        else {
          $this->messenger()->addWarning($this->viewValue("<p> <b>hbktemplateuser</b> </p> Le type d'entite <i><b>" . $this->configuration['type_entity'] . "<b></i> n'est pas encore configurer "));
        }
        if ($nbre == 0)
          return [];
        $titre = [
          '#type' => 'link',
          '#title' => $this->viewValue($nodeType->label(), 'Block'),
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
        
        $sections = [
          '#theme' => 'hbktemplateuser_resume_entity',
          '#block' => $this->HbktemplateuserGenerateLayouts->getLayout('hbktemplateuser_info_resume', $regions)
        ];
      }
      // pour gerer les entities avec bundle donc on souhaite tout afficher et
      // les entites sans bundle.
      // Cela permet de voir/editer/supprimer directement le node,
      else {
        /**
         * à ce niveau on a deux cas d figure :
         * 1/2 - on a fournit un entite de donnée (example node) dans ce cas on
         * va afficher les nodes directement editable.
         * 2/2 - on a fournit une entite de configuration (example node_type)
         * dans ce cas on va afficher les differents bundle et le nombre de
         * nodes par bundle.
         */
        // 1/2
        $bundle = $this->entityTypeManager->getStorage($this->configuration['type_entity'])->getEntityType()->getBundleEntityType();
        if ($bundle) {
          $entityQuery = $this->entityTypeManager->getStorage($this->configuration['type_entity'])->getQuery();
          $query = $entityQuery->condition('status', true)->condition('field_domain_access', $this->DomainNegotiator->getActiveId());
          $ids = $query->execute();
          // dump($this->DomainNegotiator->getActiveId());
          $contents = $this->entityTypeManager->getStorage($this->configuration['type_entity'])->loadMultiple($ids);
          $this->buildBlockEntityId($contents, $sections);
        }
        else {
          $entityTypeId = $this->entityTypeManager->getStorage($this->configuration['type_entity'])->getEntityType()->getBundleOf();
          $bundles = $this->entityTypeManager->getStorage($this->configuration['type_entity'])->loadMultiple();
          $StorageEntity = $this->entityTypeManager->getStorage($entityTypeId);
          foreach ($bundles as $bundle) {
            $entityQuery = $StorageEntity->getQuery();
            $entityQuery->condition('status', true)->condition('type', $bundle->id())->condition('field_domain_access', $this->DomainNegotiator->getActiveId());
            $nbre = $entityQuery->count()->execute();
            if ($nbre == 0)
              continue;
            //
            $link = 'internal:/manage-' . $entityTypeId . '/' . $bundle->id();
            $link = \Drupal\Core\Url::fromUri($link, [
              'query' => [
                'destination' => $this->Request->getPathInfo()
              ]
            ]);
            $titre = [
              '#type' => 'link',
              '#title' => $this->viewValue($bundle->label(), 'Block'),
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
        }
        //
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
      }
    }
    if (!empty($sections))
      return $build;
    else
      return [];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildConfigurationForm($form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
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
      // On determine si l'entite contient des bundle.
      $bundle = $this->entityTypeManager->getStorage($type_entity)->getEntityType()->getBundleEntityType();
      if ($bundle) {
        // permet d'afficher tous sans distintion.
        $options_type[] = "All (affiche tous les nodes)";
        $list_entities_type = $this->entityTypeManager->getStorage($bundle)->loadMultiple();
        foreach ($list_entities_type as $k => $value) {
          $options_type[$k] = $value->label();
        }
      }
      //
      // switch ($type_entity) {
      // case 'node_type':
      // case 'commerce_product_type':
      // case 'commerce_product':
      // case 'block_content_type':
      // case 'blocks_contents_type':
      // $list_entities_type =
      // $this->entityTypeManager->getStorage($type_entity)->loadMultiple();
      // foreach ($list_entities_type as $k => $value) {
      // $options_type[$k] = $value->label();
      // }
      // ;
      // break;
      // case 'site_internet_entity':
      // $options_type = [];
      // break;
      // default:
      // $this->messenger()->addWarning(" Type de contenu non traiter : " .
      // $type_entity);
      // break;
      // }
    }
    elseif (!empty($this->configuration['type_entity'])) {
      $type_entity = $this->configuration['type_entity'];
      // $list_entities_type =
      // $this->entityTypeManager->getStorage($type_entity)->loadMultiple();
      // foreach ($list_entities_type as $k => $value) {
      // $options_type[$k] = $value->label();
      // }
      $bundle = $this->entityTypeManager->getStorage($type_entity)->getEntityType()->getBundleEntityType();
      if ($bundle) {
        // permet d'afficher tous sans distintion.
        $options_type[] = "All (affiche tous les nodes)";
        $list_entities_type = $this->entityTypeManager->getStorage($bundle)->loadMultiple();
        foreach ($list_entities_type as $k => $value) {
          $options_type[$k] = $value->label();
        }
      }
    }
    if (!empty($options_type))
      $form['content']['type'] = [
        '#type' => 'select',
        '#title' => $this->t(' Bundle '),
        '#options' => $options_type,
        '#default_value' => !empty($options_type) ? $this->configuration['content']['type'] : '',
        '#description' => "Permet d'afficher les bundles avec les nombres de node ou d'afficher chaque node editable directement (otion all...)"
        // '#access' => !empty($options_type) ? true : false
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
      'type_entity' => 'node_type',
      'content' => [
        'type' => ''
      ]
    ] + parent::defaultConfiguration();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    // save
    $this->configuration['type_entity'] = $form_state->getValue('type_entity');
    $this->configuration['content'] = $form_state->getValue('content');
  }
  
}
