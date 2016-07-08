<?php

namespace Drupal\flysystem;

use Drupal\Component\Utility\Html;
use Drupal\editor\Plugin\Filter\EditorFileReference as DrupalEditorFileReference;
use Drupal\filter\FilterProcessResult;

/**
 * Overrides EditorFileReference to fix https://www.drupal.org/node/2666382.
 *
 * @codeCoverageIgnore
 */
class EditorFileReference extends DrupalEditorFileReference {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (stristr($text, 'data-entity-type="file"') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);
      $processed_uuids = array();
      foreach ($xpath->query('//*[@data-entity-type="file" and @data-entity-uuid]') as $node) {
        $uuid = $node->getAttribute('data-entity-uuid');

        // If there is a 'src' attribute, set it to the file entity's current
        // URL. This ensures the URL works even after the file location changes.
        if ($node->hasAttribute('src')) {
          $file = $this->entityManager->loadEntityByUuid('file', $uuid);
          if ($file) {
            $node->setAttribute('src', file_url_transform_relative(file_create_url($file->getFileUri())));
          }
        }

        // Only process the first occurrence of each file UUID.
        if (!isset($processed_uuids[$uuid])) {
          $processed_uuids[$uuid] = TRUE;

          $file = $this->entityManager->loadEntityByUuid('file', $uuid);
          if ($file) {
            $result->addCacheTags($file->getCacheTags());
          }
        }
      }
      $result->setProcessedText(Html::serialize($dom));
    }

    return $result;
  }

}
