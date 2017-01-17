webvaloa
========

[![webvaloa](https://github.com/sundflux/webvaloa/blob/master/.vendor.png)](https://github.com/sundflux/webvaloa/blob/master/.vendor.png)

Light and fast content management system with heavy emphasis on content construction and extendability.

Getting started
---------------

### Requirements
Tested to work with: nginx, Apache or Lighttpd.
- PHP >= 5.6.4 
- php-imagick
- php-intl
- php-json
- php-mbstring
- php-mysql (PDO)
- php-mcrypt
- php-xsl

### Webvaloa
The easiest way to install Webvaloa is to clone this repository to your server with:
```bash
git clone https://github.com/sundflux/webvaloa.git
```

### Libvaloa
Then you'll need to install Libvaloa using using [Composer](http://getcomposer.org/):
```bash
cd webvaloa
composer install
```

### The setup
After this you can continue the installation with a browser by going to `/setup` and following the instructions.
This setup wizard will create a `config/config.php` file based on your selections and setup the database with your selected profile. Webvaloa includes a basic website profile that is usefull when building basic websites.

### Custom code
The Webvaloa installation should be left as-is except for the `public/media` directory that contains all the user uploaded contents.
The directory for your custom components and overriding code can be defined at the end of the `config/config.php` file:
```php
DEFINE('LIBVALOA_EXTENSIONSPATH', '<your custom path>');
```
This could be for example a `custom` directory outside the Webvaloa directory that contains the same diretory structure.
For example a custom controller would go into `custom/vendor/ValoaApplication/Controllers/Somecontroller`.
It is also possible to override parts of Webvaloa by using the same naming for the files.

### Debugging
Webvaloa displays debug information based on the current PHP error reporting level.
While developing you can enable debugging by either configuring your server to set the error reporting to `E_ALL` or adding the following code to the end of `config/config.php`:
```php
error_reporting(E_ALL);
```

### Misc
License: [The MIT License (MIT)](LICENSE)

[Contributors](CONTRIBUTORS.md)
