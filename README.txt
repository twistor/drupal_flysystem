Flysystem
=========

## REQUIREMENTS ##

- PHP 5.4 or greater
- Composer (https://getcomposer.org)
- Composer manager (https://www.drupal.org/project/composer_manager)

## INSTALLATION ##

These are the steps you need to take in order to use this software. Order is
important.

 1. Download comoser_manager
 2. Enable the flysystem module.
 3. cd into the core/ directory
 4. # composer drupal-rebuild && composer update --prefer-source

## CONFIGURATION ##

Stream wrappers are configured in settings.php. The keys (sftpexample) are the
names of the stream wrappers. The 'type' key, is the type of adapter. Available
adapters are:
 - dropbox
 - ftp
 - local
 - rackspace
 - s3v2
 - sftp
 - zip

The 'config' key is the settings that will be passed into the Flysystem adapter.

Example configuration:

$schemes = [
  'local' => [
    'type' => 'local',
    'config' => [
      'root' => 'sites/default/files',
    ],
    'replicate' => 'sftpexample', // Writes will propigate to this stream.
    'prefix' => 'http://localhost/sites/default/files/', // Used when generating links to files.
  ],

  'sftpexample' => [
    'type' => 'sftp',
    'config' => [
      'host' => '127.0.0.1',
      'port' => 22,
      'username' => 'root',
      'password' => 'secret password',
      'privateKey' => '/path/to/private/key',
      'root' => '/server/address/path',
      'timeout' => 10,
      'directoryPerm' => 0755,
    ],
  ],
  'zipexample' => [
    'type' => 'zip',
    'config' => [
      'location' => '/path/to/zip/file.zip',
    ],
  ],
  'dropbox' => [
    'type' => 'dropbox',
    'config' => [
      'token' => 'long-token-thing',
      'client_id' => 'Drupal Backup',
    ],
    'cache' => TRUE,
  ],
];

$settings['flysystem'] = $schemes;
