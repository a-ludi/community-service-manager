Community Service Manager
=========================

Manage your community service schedules with multiple volunteers easily.
You simply create community service dates by using the [Event Organiser
plugin][1] and let the volunteers apply for the dates by themselves. You can
configure automatic notiftications for upcoming and unattended community
services.


Development Environment
-----------------------

The development environment for CSM requires:

- Apache (2.4.7)
- MySQL Server (5.5.44)
    - MySQL command line client (_recommended_)
    - phpMyAdmin (_recommended_)
- PHP (5.5.9) with non-standard extensions
    - `mysql` or `mysqli`
    - [`xdebug`][2] (2.2.3)
    - [`yaml`][3] (1.2.0)
- [Wordpress][4] (4.2.4)
    - [EventOrganiser][1] (2.13.6)
- [`db.php`][5] (includes class `wpdb_debug`)

This list may be incomplete but will be extended if required. The version
numbers are not strictly required but using these version should work.


### Debian-based OS

On a Debian-based OS you will require the following steps to install the
required software:

1. Execute in a shell
   ```bash
   sudo apt-get install apache2 mysql-server mysql-client phpmyadmin php5 php5-cli php5-xdebug php-pear
   sudo pecl install yaml
   ```
2. Download WordPress and extract it into your web server's document root
   (usually this is `/var/www/`).
3. Configure Apache, PHP, MySQL and WordPress (more Details follow)
4. Fetch a copy of `db.php` and drop it in your `$WP_ROOT/wp-content` folder by
   either:
    - downloading the file directly and copying it into your WP installation
    - cloning the Gist using and linking the file into your WP
      installation:
      ```bash
      git clone https://gist.github.com/354de07403192e64c456.git wpdb_debug
      ln -s wpdb_debug/db.php $WP_ROOT/wp-content/db.php
      ```
      Updates can be included with `git pull`.
5. To be continued…


[1]: https://wordpress.org/plugins/event-organiser/ "Event Organiser Plugin on WordPress.org"
[2]: http://xdebug.org/ "Xdebug homepage"
[3]: http://php.net/manual/en/book.yaml.php "php.net: YAML Data Serialization"
[4]: https://wordpress.org/download/ "WordPress.org: Download WordPress"
[5]: https://gist.github.com/a-ludi/354de07403192e64c456 "Gist: db.php"

