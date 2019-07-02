webvaloa 3
========

[![webvaloa](https://github.com/sundflux/webvaloa/blob/master/.vendor.png)](https://github.com/sundflux/webvaloa/blob/master/.vendor.png)

Light and fast content management system with heavy emphasis on content construction and extendability.

Getting started
---------------

### Requirements

Example config for Nginx is included, `/config/nginx.conf`

Server stack minimum:

- PHP >= 7.2.0
- MySQL >= 5.7
- [Composer](http://getcomposer.org/)
- make

PHP Extensions:

- php-imagick
- php-intl
- php-json
- php-mbstring
- php-mysql
- php-mcrypt
- php-xsl
- php-gettext

### Installation
The easiest way to install Webvaloa is to clone this repository to your server with:
```bash
git clone https://github.com/sundflux/webvaloa.git
```
Enter the application directory (`cd webvaloa`) and install Webvaloa with command:
```bash
make install
```

### Debugging
Webvaloa displays debug information based on the current PHP error reporting level.

While developing you can enable debugging by either configuring your server to set the error reporting to `E_ALL` or adding the following code to the end of `config/config.php`:
```php
error_reporting(E_ALL);
```

### Development
For local development you only need MySQL and PHP.

Start local development server with:
```bash
make server
```

The development server should now be running at `http://localhost:8000`

(if you don't have make installed, you can also start the development server with `php -S localhost:8000`)

### Misc
License: [The MIT License (MIT)](LICENSE)

[Contributors](CONTRIBUTORS.md)
