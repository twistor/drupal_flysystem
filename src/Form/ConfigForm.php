<?php

namespace Drupal\flysystem\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\flysystem\FlysystemFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure file system settings for this site.
 */
class ConfigForm extends FormBase {

  /**
   * The Flysystem factory.
   *
   * @var \Drupal\flysystem\FlysystemFactory
   */
  protected $factory;

  /**
   * Constructs a ConfigForm object.
   *
   * @param \Drupal\flysystem\FlysystemFactory $factory
   *   The FlysystemF factory.
   */
  public function __construct(FlysystemFactory $factory) {
    $this->factory = $factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('flysystem_factory'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'flysystem_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $schemes = $this->factory->getSchemes();

    $form['sync_from'] = [
      '#type' => 'select',
      '#options' => array_combine($schemes, $schemes),
      '#title' => $this->t('Sync from'),
      '#required' => TRUE,
    ];

    $form['sync_to'] = [
      '#type' => 'select',
      '#options' => array_combine($schemes, $schemes),
      '#title' => $this->t('Sync to'),
      '#required' => TRUE,
    ];

    $form['force'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Force'),
      '#description' => $this->t('Normally, existing files will be ignored. Selecting this option will overwrite any existing files.'),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Sync'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('sync_from') === $form_state->getValue('sync_to')) {
      $form_state->setError($form['sync_from'], $this->t('"Sync from" and "Sync to" cannot be the same scheme.'));
      $form_state->setError($form['sync_to']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $scheme_from = $form_state->getValue('sync_from');
    $scheme_to = $form_state->getValue('sync_to');

    $from_files = $this->getFileList($scheme_from);

    $to_files = [];
    if (!$form_state->getValue('force')) {
      $to_files = $this->getFileList($scheme_to);
    }

    $batch = [
      'operations' => [],
      'finished' => get_class($this) . '::finishBatch',
      'title' => $this->t('Synchronizing file systems'),
      'init_message' => $this->t('Starting file system synchronization.'),
      'progress_message' => $this->t('Completed @current step of @total.'),
      'error_message' => $this->t('File system synchronization has encountered an error.'),
    ];

    // @todo We shouldn't do all files in one go, but rather add files and
    // directories and recurse in a batch callback.
    foreach (array_diff($from_files, $to_files) as $filepath) {
      $batch['operations'][] = [get_class($this) . '::copyFile', [$scheme_from, $scheme_to, $filepath]];
    }

    batch_set($batch);
  }

  /**
   * Copies a single file.
   *
   * @param string $scheme_from
   *   The scheme to sync from.
   * @param string $scheme_to
   *   The scheme to sync to.
   * @param string $filepath
   *   The file to sync.
   * @param array &$context
   *   The batch context.
   */
  public static function copyFile($scheme_from, $scheme_to, $filepath, array &$context) {
    $context['message'] = \Drupal::translation()->translate('Copying: %file', ['%file' => $filepath]);
    $context['finished'] = 1;

    $factory = \Drupal::service('flysystem_factory');

    // Copying files could take a very long time. Using streams will keep memory
    // usage down, but we could still timeout.
    drupal_set_time_limit(0);

    try {
      $read_handle = $factory->getFilesystem($scheme_from)->readStream($filepath);

      if (!is_resource($read_handle)) {
        $args = ['%scheme' => $scheme_from, '%file' => $filepath];
        $context['results']['errors'][] = ['The file %scheme://%file could not be opened.', $args];
        return;
      }

      $success = $factory->getFilesystem($scheme_to)->putStream($filepath, $read_handle);

      if (!$success) {
        $args = ['%scheme' => $scheme_to, '%file' => $filepath];
        $context['results']['errors'][] = ['The file %scheme://%file could not be saved.', $args];
      }
    }

    // Catch all exceptions so we don't break batching. The types of exceptions
    // that adapters can throw varies greatly.
    catch (\Exception $e) {
      $context['results']['errors'][] = ['An eror occured while copying %file.', ['%file' => $filepath]];
      $context['results']['errors'][] = $e->getMessage();

      watchdog_exception('flysystem', $e);
    }

    if (isset($read_handle) && is_resource($read_handle)) {
      fclose($read_handle);
    }
  }

  /**
   * Finish batch.
   */
  public static function finishBatch($success, array $results, array $operations) {
    if (!$success) {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $args = ['%file' => reset($operations)[2]];
      drupal_set_message(\Drupal::translation()->translate('An error occurred while syncing: %file', $args), 'error');
      return;
    }

    if (empty($results['errors'])) {
      drupal_set_message(\Drupal::translation()->translate('File synchronization finished successfully.'));
      return;
    }

    foreach ($results['errors'] as $error) {
      if (is_array($error)) {
        drupal_set_message(\Drupal::translation()->translate($error[0], $error[1]), 'error', TRUE);
        \Drupal::logger('flysystem')->error($error[0], $error[1]);
      }
      else {
        drupal_set_message(Html::escape($error), 'error', TRUE);
      }
    }

    drupal_set_message(\Drupal::translation()->translate('File synchronization experienced errors.'), 'warning');
  }

  /**
   * Returns the file list for a scheme.
   *
   * @param string $scheme
   *   The scheme.
   *
   * @return string[]
   *   A list of files.
   */
  protected function getFileList($scheme) {
    $filesystem = $this->factory->getFilesystem($scheme);

    $files = array_filter($filesystem->listContents('', TRUE), function ($meta) {
      return $meta['type'] === 'file';
    });

    return array_map(function (array $meta) {
      return $meta['path'];
    }, $files);
  }

}
