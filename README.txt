Flysystem
=========

## REQUIREMENTS ##

- PHP 5.4 or greater
- Composer (https://getcomposer.org)
- Composer manager (https://www.drupal.org/project/composer_manager)

## INSTALLATION ##

These are the steps you need to take in order to use this software. Order is
important.

 1. Download and install composer_manager.
 2. Download flysystem.
 3. cd into the core/ directory
 4. # composer drupal-rebuild && composer update --prefer-source
 5. Install Flysystem.
 6. Enjoy.

## CONFIGURATION ##

Stream wrappers are configured in settings.php. The keys (localexample) are the
names of the stream wrappers. The 'type' key, is the type of adapter. Available
adapters are:

 - local
 - ftp
 - dropbox (https://www.drupal.org/project/flysystem_dropbox)
 - rackspace (https://www.drupal.org/project/flysystem_rackspace)
 - s3v2 (https://www.drupal.org/project/flysystem_s3)
 - sftp (https://www.drupal.org/project/flysystem_sftp)
 - zip (https://www.drupal.org/project/flysystem_zip)

The 'config' key is the settings that will be passed into the Flysystem adapter.

Example configuration:

$schemes = [
  'localexample' => [
    'type' => 'local',
    'config' => [
      'root' => '/path/to/dir',  // If 'root' is inside the public directory,
    ],                           // then files will be served directly.
    'replicate' => 'ftpexample', // 'replicate' writes to both filesystems, but
  ],                             // reads from this one.
  'ftpexample' => [
    'type' => 'ftp',
    'config' => [
      'host' => 'ftp.example.com',
      'username' => 'username',
      'password' => 'password',

      // Optional config settings.
      'port' => 21,
      'root' => '/path/to/root',
      'passive' => true,
      'ssl' => true,
      'timeout' => 30,
    ],
    'cache' => TRUE, // Cache filesystem metadata. Not necessary, since this is
  ],                 // a replica.
];

$settings['flysystem'] = $schemes;
