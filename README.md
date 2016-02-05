Flysystem for Drupal
====================

[Flysystem](http://flysystem.thephpleague.com/) is a filesystem abstraction
which allows you to easily swap out a local filesystem for a remote one.
Reducing technical debt and chance of vendor lock-in.

## REQUIREMENTS ##

- Composer (https://getcomposer.org)
- (optional) Composer manager (https://www.drupal.org/project/composer_manager)
  If you have a different method of managing Composer dependencies, use that.

## INSTALLATION ##

These are the steps you need to take in order to use this software. Order is
important.

Note: If you're not using composer_manager, then use whatever you normally use
to manage dependencies, and just install this module.

 1. Download composer_manager.
 2. Download flysystem.
 3. Initialize composer_manager:
 4. Update composer dependencies.
 5. Install Flysystem.
 6. Enjoy.

Steps performed from the root of the Drupal install. Module locations may vary.

```bash
drush dl composer_manager flysystem
php modules/composer_manager/scripts/init.php
composer drupal-rebuild
composer update --lock
drush en flysystem -y
```

## CONFIGURATION ##

Stream wrappers are configured in settings.php. The keys (localexample) are the
names of the stream wrappers. For example: 'localexample://filename.txt'
The 'driver' key, is the type of adapter. Available adapters are:

 - local
 - ftp (Requires the ftp extension)
 - dropbox (https://www.drupal.org/project/flysystem_dropbox)
 - rackspace (https://www.drupal.org/project/flysystem_rackspace)
 - s3v2 (https://www.drupal.org/project/flysystem_s3)
 - sftp (https://www.drupal.org/project/flysystem_sftp)
 - zip (https://www.drupal.org/project/flysystem_zip)

The 'config' is the configuration passed into the Flysystem adapter.

Example configuration:

```php
$schemes = [
  'localexample' => [            // The name of the stream wrapper. localexample://

    'driver' => 'local',         // The plugin key.

    'config' => [
      'root' => '/path/to/dir/outsite/drupal', // This will be treated similarly
                                               // Drupal's private file system.
      // Or.

      'root' => 'sites/default/files/flysystem',
      'public' => TRUE,                          // In order for the public setting to work,
                                                 // the path must be relative to the root
                                                 // of the Drupal install.

    // Optional settings that apply to all adapters.

    'cache' => TRUE,             // Cache filesystem metadata. Not necessary for
                                 // the local driver.

    'replicate' => 'ftpexample', // 'replicate' writes to both filesystems, but
                                 // reads from this one. Functions as a backup.

    'serve_js' => TRUE,          // Serve Javascript or CSS via this stream wrapper.
    'serve_css' => TRUE,         // This is useful for adapters that function as
                                 // CDNs like the S3 adapter.
  ],

  'ftpexample' => [
    'driver' => 'ftp',
    'config' => [
      'host' => 'ftp.example.com',
      'username' => 'username',
      'password' => 'password',

      // Optional config settings.
      'port' => 21,
      'root' => '/path/to/root',
      'passive' => true,
      'ssl' => false,
      'timeout' => 90,
      'permPrivate' => 0700,
      'permPublic' => 0700,
      'transferMode' => FTP_BINARY,
    ],
  ],
];

// Don't forget this!
$settings['flysystem'] = $schemes;
```
