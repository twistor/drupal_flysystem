<?php

namespace Drupal\Tests\flysystem\Unit\Form {

use Drupal\Core\Form\FormState;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\FlysystemFactory;
use Drupal\flysystem\Form\ConfigForm;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Memory\MemoryAdapter;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass \Drupal\flysystem\Form\ConfigForm
 * @group flysystem
 */
class ConfigFormTest extends UnitTestCase {

  /**
   * The Flysystem factory prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $factory;

  /**
   * The form object.
   *
   * @var \Drupal\flysystem\Form\ConfigForm
   */
  protected $form;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->factory = $this->prophesize(FlysystemFactory::class);
    $this->factory->getFilesystem('from_empty')->willReturn(new Filesystem(new MemoryAdapter()));
    $this->factory->getFilesystem('to_empty')->willReturn(new Filesystem(new MemoryAdapter()));
    $this->factory->getSchemes()->willReturn(['from_empty', 'to_empty']);

    $this->form = new ConfigForm($this->factory->reveal());
    $this->form->setStringTranslation($this->getStringTranslationStub());

    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('flysystem_factory', $this->factory->reveal());

    $logger = $this->prophesize(LoggerChannelFactoryInterface::class);
    $logger->get('flysystem')->willReturn($this->prophesize(LoggerInterface::class)->reveal());
    $container->set('logger.factory', $logger->reveal());

    \Drupal::setContainer($container);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  public function testCreate() {
    $container = new ContainerBuilder();
    $container->set('flysystem_factory', $this->factory->reveal());

    $this->assertInstanceOf(ConfigForm::class, ConfigForm::create($container));
  }

  /**
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $this->assertSame('flysystem_config_form', $this->form->getFormId());
  }

  /**
   * @covers ::buildForm
   */
  public function testBuildForm() {
    $form = $this->form->buildForm([], new FormState());
    $this->assertSame(4, count($form));

    $this->assertTrue($form['sync_from']['#required']);
    $this->assertTrue($form['sync_to']['#required']);
  }

  /**
   * @covers ::validateForm
   */
  public function testValidateForm() {
    $form_state = new FormState();
    $form = $this->form->buildForm([], $form_state);
    $form['sync_from']['#parents'] = ['sync_from'];
    $form['sync_to']['#parents'] = ['sync_to'];

    $form_state->setValue('sync_from', 'from');
    $form_state->setValue('sync_to', 'to');

    $this->form->validateForm($form, $form_state);
    $this->assertSame(0, count($form_state->getErrors()));

    $form_state->setValue('sync_to', 'from');

    $this->form->validateForm($form, $form_state);
    $this->assertSame(2, count($form_state->getErrors()));
  }

  /**
   * @covers ::submitForm
   * @covers ::getFileList
   */
  public function testSubmitForm() {
    $form_state = new FormState();
    $form = [];
    $form_state->setValue('sync_from', 'from_empty');
    $form_state->setValue('sync_to', 'to_empty');

    $this->form->submitForm($form, $form_state);
    $batch = \Drupal\flysystem\Form\batch_set();

    $this->assertSame(ConfigForm::class . '::finishBatch', $batch['finished']);
    $this->assertSame(0, count($batch['operations']));

    // Test with existing source files.
    $from = new Filesystem(new MemoryAdapter());
    $from->write('dir/test.txt', 'abcdefg');
    $from->write('test.txt', 'abcdefg');
    $this->factory->getFilesystem('from_files')->willReturn($from);

    $form_state->setValue('sync_from', 'from_files');

    $this->form->submitForm($form, $form_state);

    $batch_files = array_map(function (array $operation) {
      return $operation[1][2];
    }, \Drupal\flysystem\Form\batch_set()['operations']);

    $this->assertSame(['dir/test.txt', 'test.txt'], $batch_files);

    // Test with existing destination files, and force true.
    $form_state->setValue('force', TRUE);
    $form_state->setValue('sync_to', 'from_files');

    $this->form->submitForm($form, $form_state);

    $batch_files = array_map(function (array $operation) {
      return $operation[1][2];
    }, \Drupal\flysystem\Form\batch_set()['operations']);

    $this->assertSame(['dir/test.txt', 'test.txt'], $batch_files);
  }

  /**
   * @covers ::copyFile
   */
  public function testCopyFile() {
    $context = [];

    $from = new Filesystem(new MemoryAdapter());
    $from->write('dir/test.txt', 'abcdefg');
    $this->factory->getFilesystem('from_files')->willReturn($from);

    ConfigForm::copyFile('from_files', 'to_empty', 'dir/test.txt', $context);

    $this->assertSame('abcdefg', $this->factory->reveal()->getFilesystem('to_empty')->read('dir/test.txt'));
    $this->assertTrue(empty($context['results']));
    $this->assertSame(1, $context['finished']);
  }

  /**
   * @covers ::copyFile
   */
  public function testCopyFileFailedRead() {
    // Tests failed read.
    $context = [];
    $failed_read = $this->prophesize(FilesystemInterface::class);
    $failed_read->readStream('does_not_exist')->willReturn(FALSE);
    $this->factory->getFilesystem('failed_read')->willReturn($failed_read->reveal());

    ConfigForm::copyFile('failed_read', 'to_empty', 'does_not_exist', $context);

    $to_files = $this->factory->reveal()->getFilesystem('to_empty')->listContents('', TRUE);
    $this->assertSame(0, count($to_files));
    $this->assertSame(1, count($context['results']['errors']));
  }

  /**
   * @covers ::copyFile
   */
  public function testCopyFileFailedWrite() {
    $context = [];

    $from = new Filesystem(new MemoryAdapter());
    $from->write('test.txt', 'abcdefg');
    $this->factory->getFilesystem('from_files')->willReturn($from);

    $failed_write = $this->prophesize(FilesystemInterface::class);
    $failed_write->putStream(Argument::cetera())->willReturn(FALSE);
    $this->factory->getFilesystem('to_fail')->willReturn($failed_write);

    ConfigForm::copyFile('from_files', 'to_fail', 'test.txt', $context);

    $this->assertSame(1, count($context['results']['errors']));
    $this->assertTrue(strpos($context['results']['errors'][0][0], 'could not be saved') !== FALSE);
  }

  /**
   * @covers ::copyFile
   */
  public function testCopyFileException() {
    $context = [];
    ConfigForm::copyFile('from_empty', 'to_empty', 'does_not_exist.txt', $context);
    $this->assertSame(2, count($context['results']['errors']));
    $this->assertTrue(strpos($context['results']['errors'][0][0], 'An eror occured while copying') !== FALSE);
    $this->assertTrue(strpos($context['results']['errors'][1], 'File not found at path') !== FALSE);
  }

  /**
   * @covers ::finishBatch
   */
  public function testFinishBatch() {
    ConfigForm::finishBatch(TRUE, [], []);
    ConfigForm::finishBatch(FALSE, [], ['from', 'to', 'file.txt']);
    ConfigForm::finishBatch(TRUE, ['errors' => ['first error', ['second error', ['']]]], []);
  }

  /**
   * Converts a file list fron Flysystem into a list of files.
   *
   * @param array $list
   *   The file list from Flysystem::listContents().
   *
   * @return string[]
   *   A list of file paths.
   */
  protected function getFileList(array $list) {
    $list = array_filter($list, function (array $file) {
      return $file['type'] === 'file';
    });

    return array_map(function (array $file) {
      return $file['path'];
    }, $list);
  }

}
}

namespace Drupal\flysystem\Form {
  function drupal_set_message() {}

  function batch_set($batch = NULL) {
    static $last_batch;

    if (isset($batch)) {
      $last_batch = $batch;
    }
    return $last_batch;
  }

  function drupal_set_time_limit($limit) {
    if ($limit !== 0) {
      throw new \Exception();
    }
  }

  function watchdog_exception() {}
}
