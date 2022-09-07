<?php

namespace Drupal\hbktemplateuser\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\domain\DomainNegotiator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for hbktemplateuser routes.
 */
class HbktemplateuserController extends ControllerBase {

  /**
   *
   * @var DomainNegotiator
   */
  protected $DomainNegotiator;

  /**
   * --
   */
  function __construct(DomainNegotiator $DomainNegotiator) {
    $this->DomainNegotiator = $DomainNegotiator;
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('domain.negotiator'));
  }

  /**
   * Builds the response.
   */
  public function build(Request $Request) {
    $links = [];
    $links[] = [
      '#type' => 'link',
      '#title' => ' Exporter le theme encours ',
      '#url' => Url::fromRoute('export_import_entities.generatesite'),
      '#attributes' => [
        'class' => [
          'toolbar-icon'
        ]
      ]
    ];

    if (\Drupal::moduleHandler()->moduleExists('domain') && $domain = $this->DomainNegotiator->getActiveDomain()) {

      $themeConf = $this->entityTypeManager()->getStorage("config_theme_entity")->loadByProperties([
        'hostname' => $domain->id()
      ]);
      if ($themeConf) {
        $themeConf = reset($themeConf);
        // entity.config_theme_entity.edit_form
        $links[] = [
          '#type' => 'link',
          '#title' => ' Configurer les couleurs, tailles de polices, logo ',
          '#url' => Url::fromRoute('entity.config_theme_entity.edit_form', [
            'config_theme_entity' => $themeConf->id()
          ], [
            'query' => [
              'destination' => $Request->getPathInfo()
            ]
          ]),
          '#attributes' => [
            'class' => [
              'toolbar-icon'
            ]
          ]
        ];
      }
    }

    $links[] = [
      '#type' => 'link',
      '#title' => ' Definir des styles personnalisés pour le theme ',
      '#url' => Url::fromRoute('generate_style_theme.managecustom.styles'),
      '#attributes' => [
        'class' => [
          'toolbar-icon'
        ]
      ]
    ];
    $links[] = [
      '#type' => 'link',
      '#title' => ' Voir la liste des fichiers de styles ',
      '#url' => Url::fromRoute('layoutgenentitystyles.generate'),
      '#attributes' => [
        'class' => [
          'toolbar-icon'
        ]
      ]
    ];
    /* */
    //
    // $this->ExportEntities->getEntites();
    $build['content'] = [
      '#type' => 'html_tag',

      '#tag' => 'h2',
      '#value' => $this->t("Liste d'actions")
    ];
    $build['links'] = [
      '#theme' => 'item_list',
      '#items' => $links
    ];
    return $build;
  }

}