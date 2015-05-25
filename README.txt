Flysystem
=========

## REQUIREMENTS ##

- PHP 5.4 or greater
- The mbstring extension. (http://php.net/manual/en/book.mbstring.php)
- Composer (https://getcomposer.org)
- Composer manager (https://www.drupal.org/project/composer_manager)

## INSTALLATION ##

These are the steps you need to take in order to use this software. Order is
important.

 1. Download and install composer_manager.
 2. Download flysystem.
 3. cd into the core/ directory
 4. # composer drupal-rebuild && composer update
 5. Install Flysystem.
 6. Enjoy.

## CONFIGURATION ##

Stream wrappers are configured in settings.php. The keys (localexample) are the
names of the stream wrappers. Can be used like 'localexample://filename.txt' The
'driver' key, is the type of adapter. Available adapters are:

 - local
 - ftp (Requires the ftp extension)
 - dropbox (https://www.drupal.org/project/flysystem_dropbox)
 - rackspace (https://www.drupal.org/project/flysystem_rackspace)
 - s3v2 (https://www.drupal.org/project/flysystem_s3)
 - sftp (https://www.drupal.org/project/flysystem_sftp)
 - zip (https://www.drupal.org/project/flysystem_zip)

The 'config' key is the settings that will be passed into the Flysystem adapter.

Example configuration:

$schemes = [
  'localexample' => [            // The name of the stream wrapper. localexample://

    'driver' => 'local',         // The plugin key.

    'config' => [
      'root' => '/path/to/dir',  // If 'root' is inside the public directory,
    ],                           // then files will be served directly. Can be
                                 // relative or absolute.

    // Optional settings that apply to all adapters.

    'cache' => TRUE,             // Cache filesystem metadata. Not necessary,
                                 // since this is a local filesystem.

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
