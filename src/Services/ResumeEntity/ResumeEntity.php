<?php

namespace Drupal\hbktemplateuser\Services\ResumeEntity;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\hbktemplateuser\Services\Layouts\HbktemplateuserGenerateLayouts;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 *
 * @author stephane
 *        
 */
class ResumeEntity {
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
  
  function __construct(EntityTypeManagerInterface $entity_type_manager, HbktemplateuserGenerateLayouts $HbktemplateuserGenerateLayouts) {
    $this->entityTypeManager = $entity_type_manager;
    $this->HbktemplateuserGenerateLayouts = $HbktemplateuserGenerateLayouts;
  }
  
  /**
   *
   * @return array
   */
  function getResumeNodes() {
    $contentTypes = NodeType::loadMultiple();
    $entityQuery = $this->entityTypeManager->getStorage('node')->getQuery();
    $blocs = [];
    foreach ($contentTypes as $nodeType) {
      /**
       *
       * @var NodeType $nodeType
       */
      $query = $entityQuery->condition('type', $nodeType->id())->condition('status', true);
      $regions = [
        'title' => [
          '#markup' => $nodeType->label()
        ],
        'icone' => [
          '#markup' => '<i class="far fa-folder"></i>'
        ],
        'nombre' => [
          '#markup' => $query->count()->execute()
        ]
      ];
      $blocs[] = $this->HbktemplateuserGenerateLayouts->getLayout('hbktemplateuser_info_resume', $regions);
      ;
    }
    return $blocs;
  }
  
}