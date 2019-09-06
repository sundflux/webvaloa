*NOTE! Master aka version 3 is under heavy development and totally broken for any other usage than developing. Use 1.0 branch for working version.*

webvaloa
========

[![webvaloa](https://github.com/sundflux/webvaloa/blob/master/.vendor.png)](https://github.com/sundflux/webvaloa/blob/master/.vendor.png)

Light and fast content management system with heavy emphasis on content construction and extendability.

Getting started
---------------

### Requirements

Example config for Nginx is included, `/config/nginx.conf`

Server stack minimum:

- PHP >= 7.2.19
- MySQL >= 5.7
- [Composer](http://getcomposer.org/)
- make

PHP Extensions:

- php-imagick
- php-intl
- php-json
- php-mbstring
- php-mysql
- php-xsl
- php-xml
- php-gettext

Install required PHP extensions on Ubuntu 18.04:

```bash
sudo apt install php7.2-cli php-imagick php7.2-intl php7.2-json \
php7.2-mbstring php7.2-mysql php7.2-xsl php7.2-xml php-gettext mysql-server
```

### Installation
The easiest way to install Webvaloa is to clone this repository to your server with:
```bash
git clone https://github.com/sundflux/webvaloa.git
```
Enter the application directory `cd webvaloa` and install Webvaloa with command:
```bash
composer install
```

After installing requiring packages, head to `http://yourhost/setup` with web browser to finish installation. 

### Updating Webvaloa

To update all Webvaloa components, use:

```bash
composer update --lock
```

### Adding extensions

*Do NOT include outside extensions or libraries in root `composer.json`*. 
This should be updated only from the Webvaloa repository.

Composer merge plugin is included, and will include following files if found:

    "ext/composer.json",
    "ext/*/composer.json"

Place any custom includes and composer packages under `ext/` directory.

### Debugging
Webvaloa displays debug information based on the current PHP error reporting level.

While developing you can enable debugging by either configuring your server to set the error 
reporting to `E_ALL` or adding the following code to the end of `config/config.php`:
```php
error_reporting(E_ALL);
```

### Development
For local development you need MySQL and PHP.

Install dependencies with:
```bash
make installdeps
```

Start local development server with:
```bash
make server
```

The development server is now running at `http://localhost:8000`

### Misc
[The MIT License (MIT)](LICENSE)

[Contributors](CONTRIBUTORS.md)
