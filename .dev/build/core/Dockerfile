FROM shopware/development:7.3

RUN apt-get update && apt-get install bash

COPY config.sh /opt/docker/bin/config.sh

RUN yes | pecl install xdebug && docker-php-ext-enable xdebug
COPY xdebugon.sh /usr/local/bin/xdebugon.sh
COPY xdebugoff.sh /usr/local/bin/xdebugoff.sh

RUN apt-get update && apt-get install -y iproute2
