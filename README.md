Flysystem for Drupal
====================

[Flysystem](http://flysystem.thephpleague.com/) is a filesystem abstraction
which allows you to easily swap out a local filesystem for a remote one.
Reducing technical debt and chance of vendor lock-in.

## REQUIREMENTS ##

- Composer (https://getcomposer.org)

## INSTALLATION ##

These are the steps you need to take in order to use this software. Order is
important.

 1. Download and install flysystem's module and its dependencies.
 2. Install flysystem module.
 3. Enjoy.

```bash
cd /path/to/drupal/root
composer require drupal/flysystem
drush en flysystem
```

## TROUBLESHOOTING ##

If you are having trouble with this module, check the status page at
admin/reports/status. The status page runs all the Flysystem checks and provides
useful error reporting.

## CONFIGURATION ##

Stream wrappers are configured in settings.php.

The keys (local-example below) are the names of the stream wrappers.

For example: 'local-example://filename.txt'.

Stream wrapper names cannot contain underscores, they can only contain letters,
numbers, + (plus sign), . (period), - (hyphen).

The 'driver' key, is the type of adapter. The available adapters are:

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
  'local-example' => [           // The name of the stream wrapper.

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

    'name' => 'Custom stream wrapper name', // Defaults to Flysystem: scheme.
    'description' => 'Custom description',  // Defaults to Flysystem: scheme.

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
