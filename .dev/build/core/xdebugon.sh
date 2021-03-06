#!/usr/bin/env bash

echo "xdebug.remote_connect_back=0" >> /usr/local/etc/php/conf.d/xdebug.ini
echo "xdebug.remote_autostart=1" >> /usr/local/etc/php/conf.d/xdebug.ini
echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini
echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/xdebug.ini

HOST_ADDR=`/sbin/ip route|awk '/default/ { print $3 }'`
echo "xdebug.remote_host=$HOST_ADDR" >> /usr/local/etc/php/conf.d/xdebug.ini
service apache2 restart
