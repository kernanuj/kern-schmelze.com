#!/usr/bin/env bash

echo "xdebug.remote_connect_back=0" >> /usr/local/etc/php/conf.d/xdebug.ini
echo "xdebug.remote_autostart=0" >> /usr/local/etc/php/conf.d/xdebug.ini
echo "xdebug.remote_enable=0" >> /usr/local/etc/php/conf.d/xdebug.ini
echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/xdebug.ini

service apache2 restart
